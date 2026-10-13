<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Un document demande au personnel : « syllabus du semestre 1 », « fiche de
 * progression »… adresse a un departement, une filiere, une specialite, ou a
 * tout le monde.
 */
class DemandeDocument extends Model
{
    protected $table = 'demandes_document';

    protected $fillable = [
        'titre', 'description', 'cible_type', 'cible_id', 'annee_academique',
        'date_limite', 'obligatoire', 'plusieurs_fichiers', 'formats', 'statut', 'cree_par',
    ];

    protected $casts = [
        'date_limite' => 'date',
        'obligatoire' => 'boolean',
        'plusieurs_fichiers' => 'boolean',
    ];

    public function depots(): HasMany
    {
        return $this->hasMany(DepotDocument::class);
    }

    public function scopeOuverte(Builder $query): Builder
    {
        return $query->where('statut', 'ouverte');
    }

    public function estOuverte(): bool
    {
        return $this->statut === 'ouverte';
    }

    /** La demande a-t-elle depasse son echeance ? */
    public function enRetard(): bool
    {
        return $this->date_limite !== null && $this->date_limite->isPast();
    }

    /** A qui elle s'adresse, en une ligne. */
    public function cible(): string
    {
        return match ($this->cible_type) {
            'departement' => 'Département : '.(Departement::find($this->cible_id)?->nom ?? 'supprimé'),
            'filiere' => 'Filière : '.(Filiere::find($this->cible_id)?->name ?? 'supprimée'),
            'specialite' => 'Spécialité : '.(Specialite::find($this->cible_id)?->name ?? 'supprimée'),
            default => 'Tout le personnel',
        };
    }

    /**
     * La cible, remontee en cascade complete (cycle, departement, filiere,
     * specialite) : une fiche doit rouvrir avec tous ses deroulants garnis,
     * pas seulement le dernier.
     *
     * @return array{cycle: ?int, departement: ?int, filiere: ?int, specialite: ?int}
     */
    public function chaineCascade(): array
    {
        $vide = ['cycle' => null, 'departement' => null, 'filiere' => null, 'specialite' => null];

        return match ($this->cible_type) {
            'specialite' => (function () {
                $specialite = Specialite::with('filiere.departement')->find($this->cible_id);

                return [
                    'cycle' => $specialite?->filiere?->departement?->cycle_id,
                    'departement' => $specialite?->filiere?->departement_id,
                    'filiere' => $specialite?->filiere_id,
                    'specialite' => $this->cible_id,
                ];
            })(),
            'filiere' => (function () {
                $filiere = Filiere::with('departement')->find($this->cible_id);

                return [
                    'cycle' => $filiere?->departement?->cycle_id,
                    'departement' => $filiere?->departement_id,
                    'filiere' => $this->cible_id,
                    'specialite' => null,
                ];
            })(),
            'departement' => [
                'cycle' => Departement::find($this->cible_id)?->cycle_id,
                'departement' => $this->cible_id,
                'filiere' => null,
                'specialite' => null,
            ],
            default => $vide,
        };
    }

    /**
     * Les extensions acceptees.
     *
     * @return array<int, string>
     */
    public function formatsAcceptes(): array
    {
        if (blank($this->formats)) {
            return config('documents.formats');
        }

        return collect(explode(',', $this->formats))
            ->map(fn ($f) => strtolower(trim($f, " \t.")))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Le personnel concerne par la demande.
     *
     * Les affectations portent leurs parents : affecter quelqu'un a une
     * specialite l'inscrit aussi a sa filiere et a son departement. Une
     * demande adressee a une filiere touche donc tous ceux qui enseignent
     * dans ses specialites, sans qu'on ait a les enumerer.
     *
     * @return Collection<int, User>
     */
    public function concernes(): Collection
    {
        $personnel = User::personnelDuPortail();

        if ($this->cible_type !== 'tous') {
            $colonne = $this->cible_type.'_id';

            $personnel->whereIn('id', Affectation::query()
                ->where('annee_academique', $this->annee_academique)
                ->where($colonne, $this->cible_id)
                ->pluck('user_id'));
        }

        return $personnel->orderBy('name')->orderBy('lastname')->get();
    }

    /** La demande concerne-t-elle cet agent ? */
    public function concerne(User $user): bool
    {
        if ($this->cible_type === 'tous') {
            return User::personnelDuPortail()->whereKey($user->id)->exists();
        }

        return Affectation::where('annee_academique', $this->annee_academique)
            ->where('user_id', $user->id)
            ->where($this->cible_type.'_id', $this->cible_id)
            ->exists();
    }
}
