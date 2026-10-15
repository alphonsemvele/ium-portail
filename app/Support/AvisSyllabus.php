<?php

namespace App\Support;

use App\Mail\CourrielSyllabus;
use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Les courriels du syllabus : au chef de departement quand un enseignant
 * depose, a l'enseignant quand le chef tranche.
 *
 * Chaque methode rend une phrase a afficher : un envoi qui echoue ne doit ni
 * faire perdre le depot, ni laisser croire qu'il est parti.
 */
class AvisSyllabus
{
    /**
     * Un serveur de courriel qui ecrit dans un fichier n'avertit personne :
     * autant le dire, plutot que d'annoncer un envoi qui n'a pas lieu.
     */
    private static function posteOuverte(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array', null], true);
    }

    /** Une adresse derivee par le portail n'arrive nulle part. */
    private static function adresseUtilisable(?User $personne): bool
    {
        return $personne !== null
            && filled($personne->email)
            && ! str_ends_with((string) $personne->email, '@portail.local');
    }

    private static function nom(?User $personne): string
    {
        return $personne ? trim($personne->name.' '.$personne->lastname) : '';
    }

    /** Les faits que tout avis rappelle : de quelle matiere parle-t-on. */
    private static function faits(Syllabus $syllabus): array
    {
        return array_filter([
            'Matière' => $syllabus->intitule(),
            'Enseignant' => self::nom($syllabus->enseignant),
            'Rattachement' => $syllabus->chemin(),
            'Semestre' => 'Semestre '.$syllabus->semestre.' — '.$syllabus->annee_academique,
        ]);
    }

    /**
     * Previent le chef du departement de la matiere qu'un syllabus est
     * depose, et consigne a qui l'avis est parti.
     */
    public static function depot(Syllabus $syllabus): string
    {
        $chef = $syllabus->chefDeDepartement();

        if ($chef === null) {
            return "Syllabus soumis. Aucun chef de département n'est enregistré pour cette matière : "
                .'personne n’a pu être prévenu.';
        }

        if (! self::adresseUtilisable($chef)) {
            return 'Syllabus soumis. '.self::nom($chef)
                ." n'a pas d'adresse électronique : prévenez-le autrement.";
        }

        $courriel = new CourrielSyllabus(
            sujet: 'Syllabus déposé : '.$syllabus->intitule(),
            destinataire: self::nom($chef),
            introduction: self::nom($syllabus->enseignant).' vient de déposer le syllabus de sa matière. '
                .'Vous pouvez le lire et le valider depuis votre espace.',
            faits: self::faits($syllabus),
            bouton: [
                'libelle' => 'Ouvrir le syllabus',
                'url' => url('/syllabus/departement?ouvrir='.$syllabus->id),
            ],
        );

        try {
            Mail::to($chef->email)->send($courriel);
        } catch (Throwable $e) {
            Log::warning('Syllabus : avis au chef de département impossible.', [
                'syllabus' => $syllabus->id, 'raison' => $e->getMessage(),
            ]);

            return 'Syllabus soumis, mais le courriel au chef de département n’a pas pu partir.';
        }

        $syllabus->forceFill(['notifie_a' => $chef->email, 'notifie_le' => now()])->save();

        if (! self::posteOuverte()) {
            return 'Syllabus soumis. Attention : l’envoi de courriels n’est pas configuré sur ce serveur, '
                .self::nom($chef).' n’a rien reçu.';
        }

        return 'Syllabus soumis. '.self::nom($chef).' a été prévenu par courriel.';
    }

    /** Rend la decision du chef de departement a l'enseignant. */
    public static function decision(Syllabus $syllabus): string
    {
        $enseignant = $syllabus->enseignant;
        $valide = $syllabus->statut === 'valide';
        $verdict = $valide ? 'validé' : 'renvoyé pour révision';

        if (! self::adresseUtilisable($enseignant)) {
            return 'Syllabus '.$verdict.". L'enseignant n'a pas d'adresse électronique : prévenez-le autrement.";
        }

        $courriel = new CourrielSyllabus(
            sujet: ($valide ? 'Syllabus validé : ' : 'Syllabus à revoir : ').$syllabus->intitule(),
            destinataire: self::nom($enseignant),
            introduction: $valide
                ? 'Votre syllabus a été validé par le chef de département. Rien d’autre ne vous est demandé.'
                : 'Le chef de département vous demande de reprendre votre syllabus avant de le soumettre à nouveau.',
            faits: self::faits($syllabus),
            bouton: [
                'libelle' => $valide ? 'Voir mon syllabus' : 'Reprendre mon syllabus',
                'url' => url('/syllabus?ouvrir='.$syllabus->id),
            ],
            remarque: $valide ? null : ($syllabus->motif ?: null),
        );

        try {
            Mail::to($enseignant->email)->send($courriel);
        } catch (Throwable $e) {
            Log::warning('Syllabus : avis à l’enseignant impossible.', [
                'syllabus' => $syllabus->id, 'raison' => $e->getMessage(),
            ]);

            return 'Syllabus '.$verdict.', mais le courriel à l’enseignant n’a pas pu partir.';
        }

        if (! self::posteOuverte()) {
            return 'Syllabus '.$verdict.'. Attention : l’envoi de courriels n’est pas configuré sur ce serveur, '
                .self::nom($enseignant).' n’a rien reçu.';
        }

        return 'Syllabus '.$verdict.'. '.self::nom($enseignant).' a été prévenu par courriel.';
    }
}
