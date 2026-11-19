<?php

namespace App\Support;

use GdImage;

/**
 * Le logo posé en filigrane sur un document imprimé.
 *
 * Deux usages : une marque unique, et une planche — le logo répété, penché,
 * d'un bord à l'autre de la feuille. C'est la planche que portent les pièces
 * officielles : elle couvre la page entière, pour qu'aucune partie du
 * document ne puisse être découpée et recollée ailleurs.
 *
 * L'image est délavée ici, point par point, plutôt que confiée à une
 * transparence CSS : dompdf ne l'applique pas aux blocs positionnés avec la
 * même fidélité, et un calque posé sur du blanc effacerait ce qu'il recouvre
 * — une note, un total. Ce qui sort garde donc sa transparence.
 *
 * Au passage, le damier gris d'une transparence aplatie disparaît : seuls
 * les points colorés — ou franchement sombres — font la marque.
 */
class Filigrane
{
    /**
     * L'intensité, en pourcentage.
     *
     * Assez pour qu'on devine la marque sous le tableau, pas assez pour gêner
     * la lecture d'une note ni pour ressortir sur une photocopie.
     */
    public const FORCE = 7;

    /** Une marque unique s'imprime grande, mais n'a pas besoin de finesse. */
    private const LARGEUR = 640;

    /** La largeur d'une planche, en points : une page A4 à 100 points par pouce. */
    private const PAGE = 827;

    /** La largeur d'un motif sur la planche : il en tient environ quatre par rangée. */
    private const MOTIF = 210;

    /** L'inclinaison du motif, en degrés. */
    private const PENCHE = 28;

    /**
     * L'écart d'un motif au suivant, en largeurs de motif.
     *
     * Jamais moins de 1 : les motifs sont posés tels quels, sans se fondre
     * les uns dans les autres, et deux motifs qui se chevaucheraient
     * s'effaceraient mutuellement les coins.
     */
    private const ECART = 1.0;

    /**
     * Le même logo revient sur chaque relevé d'une fournée : on ne le délave
     * qu'une fois par requête.
     *
     * @var array<string, ?string>
     */
    private static array $faits = [];

    /**
     * Une planche de filigranes à la taille d'une page.
     *
     * @param  float  $ratio  La hauteur de la page pour une largeur de 1.
     */
    public static function planche(
        ?string $fichier,
        int $force = self::FORCE,
        float $ratio = 297 / 210,
    ): ?string {
        return self::retenu('planche|'.$fichier.'|'.$force.'|'.$ratio,
            fn () => self::couvrir($fichier, $force, $ratio));
    }

    /** Une marque unique, à poser où l'on veut dans le document. */
    public static function depuis(?string $fichier, int $force = self::FORCE): ?string
    {
        return self::retenu($fichier.'|'.$force, function () use ($fichier, $force) {
            $pale = self::pale($fichier, $force, self::LARGEUR);

            return $pale === null ? null : self::encoder($pale);
        });
    }

    /** @param callable(): ?string $fabrique */
    private static function retenu(string $cle, callable $fabrique): ?string
    {
        if (array_key_exists($cle, self::$faits)) {
            return self::$faits[$cle];
        }

        return self::$faits[$cle] = $fabrique();
    }

    private static function couvrir(?string $fichier, int $force, float $ratio): ?string
    {
        $motif = self::pale($fichier, $force, self::MOTIF);

        if ($motif === null) {
            return null;
        }

        $penche = imagerotate($motif, self::PENCHE,
            (int) imagecolorallocatealpha($motif, 255, 255, 255, 127));
        imagealphablending($penche, false);
        imagesavealpha($penche, true);

        $largeur = self::PAGE;
        $hauteur = (int) round(self::PAGE * $ratio);

        $planche = imagecreatetruecolor($largeur, $hauteur);
        imagealphablending($planche, false);
        imagesavealpha($planche, true);
        imagefilledrectangle($planche, 0, 0, $largeur - 1, $hauteur - 1,
            (int) imagecolorallocatealpha($planche, 255, 255, 255, 127));

        $pasX = max(1, (int) round(imagesx($penche) * self::ECART));
        $pasY = max(1, (int) round(imagesy($penche) * self::ECART));

        // Une rangée sur deux est décalée d'un demi-pas : le motif ne forme
        // pas de colonnes, et la feuille se couvre régulièrement.
        for ($rang = -1; $rang * $pasY < $hauteur; $rang++) {
            $decalage = $rang % 2 === 0 ? 0 : (int) round($pasX / 2);

            for ($x = -$pasX + $decalage; $x < $largeur; $x += $pasX) {
                imagecopy($planche, $penche, $x, $rang * $pasY, 0, 0,
                    imagesx($penche), imagesy($penche));
            }
        }

        return self::encoder($planche);
    }

    /**
     * Le logo réduit à la largeur voulue et délavé, prêt à poser.
     *
     * Rien n'est composé sur du blanc : l'image garde sa transparence, et ce
     * qu'elle recouvrira restera lisible au travers.
     */
    private static function pale(?string $fichier, int $force, int $largeurVoulue): ?GdImage
    {
        if (blank($fichier) || ! is_file($fichier) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($fichier));

        if ($source === false) {
            return null;
        }

        $largeur = min($largeurVoulue, imagesx($source));
        $hauteur = max(1, (int) round(imagesy($source) * $largeur / imagesx($source)));

        $pale = imagecreatetruecolor($largeur, $hauteur);
        imagealphablending($pale, false);
        imagesavealpha($pale, true);
        imagecopyresampled($pale, $source, 0, 0, 0, 0, $largeur, $hauteur,
            imagesx($source), imagesy($source));

        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++) {
                $point = imagecolorat($pale, $x, $y);

                imagesetpixel($pale, $x, $y,
                    (self::opacite($point, $force) << 24) | ($point & 0xFFFFFF));
            }
        }

        return $pale;
    }

    /**
     * L'opacité d'un point, telle que GD l'écrit : 0 opaque, 127 invisible.
     *
     * Un point clair et presque gris est du fond — le blanc d'un logo, le
     * damier laissé par une transparence aplatie — et ne filigrane pas. Un
     * point coloré, ou sombre, est de la matière : il passe, à quelques pour
     * cent de ce qu'il valait.
     */
    private static function opacite(int $point, int $force): int
    {
        $rouge = ($point >> 16) & 0xFF;
        $vert = ($point >> 8) & 0xFF;
        $bleu = $point & 0xFF;

        $clair = max($rouge, $vert, $bleu);
        $sombre = min($rouge, $vert, $bleu);

        $matiere = $clair - $sombre > 16
            ? 1.0
            : max(0.0, min(1.0, (238 - $sombre) / 80));

        // Ce que le fichier portait déjà comme transparence se cumule.
        $deja = 1 - ((($point >> 24) & 0x7F) / 127);

        return (int) round(127 - 127 * $deja * $matiere * $force / 100);
    }

    private static function encoder(GdImage $image): string
    {
        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();


        return 'data:image/png;base64,'.base64_encode($png);
    }
}
