<?php

namespace App\Support;

/**
 * Le logo posé en filigrane sur un document imprimé.
 *
 * L'image est délavée ici, point par point, plutôt que confiée à une
 * transparence CSS : dompdf peint les blocs positionnés par-dessus le reste,
 * et un calque posé sur du blanc effacerait ce qu'il recouvre — une note, un
 * total. Ce qui sort garde donc sa transparence.
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
    public const FORCE = 9;

    /** Un filigrane s'imprime grand mais n'a pas besoin de finesse. */
    private const LARGEUR = 640;

    /**
     * Le même logo revient sur chaque relevé d'une fournée : on ne le délave
     * qu'une fois par requête.
     *
     * @var array<string, ?string>
     */
    private static array $faits = [];

    /** Le filigrane d'un fichier image, prêt à poser dans un document. */
    public static function depuis(?string $fichier, int $force = self::FORCE): ?string
    {
        $cle = $fichier.'|'.$force;

        if (array_key_exists($cle, self::$faits)) {
            return self::$faits[$cle];
        }

        return self::$faits[$cle] = self::fabriquer($fichier, $force);
    }

    private static function fabriquer(?string $fichier, int $force): ?string
    {
        if (blank($fichier) || ! is_file($fichier) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($fichier));

        if ($source === false) {
            return null;
        }

        $largeur = min(self::LARGEUR, imagesx($source));
        $hauteur = max(1, (int) round(imagesy($source) * $largeur / imagesx($source)));

        $pale = imagecreatetruecolor($largeur, $hauteur);
        imagealphablending($pale, false);
        imagesavealpha($pale, true);
        imagecopyresampled($pale, $source, 0, 0, 0, 0, $largeur, $hauteur, imagesx($source), imagesy($source));

        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++) {
                $point = imagecolorat($pale, $x, $y);

                imagesetpixel($pale, $x, $y,
                    (self::opacite($point, $force) << 24) | ($point & 0xFFFFFF));
            }
        }

        ob_start();
        imagepng($pale);
        $png = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($pale);

        return 'data:image/png;base64,'.base64_encode($png);
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
}
