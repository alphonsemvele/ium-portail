<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Un avis de syllabus : depot a annoncer au chef de departement, decision a
 * rendre a l'enseignant. Un seul gabarit, les faits en lignes.
 */
class CourrielSyllabus extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $faits   Intitule => valeur, affiches en tableau.
     * @param  array{libelle: string, url: string}|null  $bouton
     */
    public function __construct(
        public string $sujet,
        public string $destinataire,
        public string $introduction,
        public array $faits = [],
        public ?array $bouton = null,
        public ?string $remarque = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.syllabus');
    }
}
