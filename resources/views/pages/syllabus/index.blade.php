<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Cour;
use App\Models\Syllabus;
use App\Support\AnneeAcademique;
use App\Support\AvisSyllabus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

name('syllabus.mes');
middleware(['auth', 'verified', 'role']);

/**
 * « Mes syllabus » : la description officielle de chaque matiere confiee a
 * l'enseignant connecte.
 *
 * Il redige dans le formulaire — ou joint son propre fichier — puis soumet.
 * Le chef du departement de la matiere est alors prevenu par courriel.
 */
new class extends Component {
    use WithFileUploads;

    public int $semestre = 1;

    /** La matiere dont on redige le syllabus ; aucune = on est sur la liste. */
    public ?int $matiereId = null;

    /** Choix d'une matiere a decrire, en cascade. */
    public bool $showChoix = false;
    public ?int $choixDepartement = null;
    public ?int $choixFiliere = null;
    public ?int $choixSpecialite = null;
    public ?int $choixUe = null;
    public ?int $choixMatiere = null;

    public array $champs = [];

    /** Trois lignes, comme sur le document officiel. */
    public array $equipe = [];

    public $piece = null;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';

    public function mount(): void
    {
        // Le lien du courriel ouvre directement le bon syllabus.
        $ouvrir = (int) request('ouvrir');

        if ($ouvrir > 0) {
            $syllabus = Syllabus::find($ouvrir);

            if ($syllabus && $syllabus->user_id === auth()->id()) {
                $this->semestre = (int) $syllabus->semestre;
                $this->ouvrir($syllabus->cour_id);
            }
        }
    }

    public function with(): array
    {
        $user = auth()->user();
        $annee = AnneeAcademique::courante();

        /*
         * Les matieres affectees, et celles dont l'agent a deja commence le
         * syllabus : tant que les affectations ne sont pas saisies, c'est
         * lui qui designe la matiere qu'il decrit.
         */
        $miennes = Syllabus::where('user_id', $user->id)
            ->where('annee_academique', $annee)
            ->where('semestre', $this->semestre)
            ->pluck('cour_id');

        $matieres = $user->matieresEnseignees($annee);

        if ($miennes->isNotEmpty()) {
            $matieres = $matieres
                ->merge(Cour::with(['ue.specialite.filiere', 'specialite'])->whereIn('id', $miennes)->get())
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        $syllabus = Syllabus::where('annee_academique', $annee)
            ->where('semestre', $this->semestre)
            ->where(fn ($requete) => $requete
                ->where('user_id', $user->id)
                ->orWhereIn('cour_id', $matieres->pluck('id')))
            ->get()
            ->keyBy('cour_id');

        return [
            'annee' => $annee,
            'matieres' => $matieres,
            'syllabus' => $syllabus,
            'aDeposer' => $matieres->reject(fn (Cour $m) => $syllabus->get($m->id)?->estSoumis())->count(),
            'enCours' => $this->matiereId ? $this->matiere($this->matiereId) : null,
            'courant' => $this->matiereId ? $syllabus->get($this->matiereId) : null,
            'cascade' => [
                'departements' => \App\Models\Departement::orderBy('nom')->get(['id', 'nom']),
                'filieres' => $this->choixDepartement
                    ? \App\Models\Filiere::where('departement_id', $this->choixDepartement)->orderBy('name')->get(['id', 'name'])
                    : collect(),
                'specialites' => $this->choixFiliere
                    ? \App\Models\Specialite::where('filiere_id', $this->choixFiliere)->orderBy('name')->get(['id', 'name'])
                    : collect(),
                'ues' => $this->choixSpecialite
                    ? \App\Models\Ue::where('specialite_id', $this->choixSpecialite)->orderBy('name')->get(['id', 'name'])
                    : collect(),
                'matieres' => $this->choixUe
                    ? Cour::where('ue_id', $this->choixUe)->where('status', '!=', 'failed')->orderBy('name')->get(['id', 'code', 'name'])
                    : collect(),
            ],
        ];
    }

    /** La matiere designee, si elle existe au catalogue. */
    private function matiere(?int $matiereId): ?Cour
    {
        return $matiereId
            ? Cour::with(['ue.specialite.filiere', 'specialite', 'cycle'])
                ->where('status', '!=', 'failed')
                ->find($matiereId)
            : null;
    }

    public function updatedChoixDepartement(): void
    {
        $this->choixFiliere = $this->choixSpecialite = $this->choixUe = $this->choixMatiere = null;
    }

    public function updatedChoixFiliere(): void
    {
        $this->choixSpecialite = $this->choixUe = $this->choixMatiere = null;
    }

    public function updatedChoixSpecialite(): void
    {
        $this->choixUe = $this->choixMatiere = null;
    }

    /** Une matiere choisie dans la cascade ouvre aussitot son formulaire. */
    public function updatedChoixMatiere(): void
    {
        if ($this->choixMatiere) {
            $this->ouvrir((int) $this->choixMatiere);
        }
    }

    public function updatedSemestre(): void
    {
        $this->fermer();
    }

    /** Ouvre le formulaire d'une matiere, garni de ce qu'on sait deja. */
    public function ouvrir(int $matiereId): void
    {
        $user = auth()->user();
        $matiere = $this->matiere($matiereId);

        if (! $matiere) {
            $this->notifier("Cette matière n'existe plus au catalogue.", 'erreur');

            return;
        }

        $this->showChoix = false;
        $this->choixMatiere = null;

        $syllabus = Syllabus::pourMatiere($matiere, $user, $this->semestre);

        $this->matiereId = $matiereId;
        $this->piece = null;
        $this->resetValidation();

        $this->champs = [
            'niveau' => $syllabus->niveau ?? '',
            'langue' => $syllabus->langue ?: 'Français',
            'credits' => $syllabus->credits,
            'volume_total' => $syllabus->volume_total,
            'volume_cm' => $syllabus->volume_cm,
            'volume_td' => $syllabus->volume_td,
            'volume_tp' => $syllabus->volume_tp,
            'volume_tpe' => $syllabus->volume_tpe,
            'objectifs' => $syllabus->objectifs ?? '',
            'competences' => $syllabus->competences ?? '',
            'outils' => $syllabus->outils ?? '',
            'prerequis' => $syllabus->prerequis ?? '',
            'resume' => $syllabus->resume ?? '',
            'contenu' => $syllabus->contenu ?? '',
            // Les deux modes sont ceux du document officiel : on les propose,
            // l'enseignant les ajuste.
            'mode_enseignement' => $syllabus->mode_enseignement ?: 'Présentiel / distanciel / bimodal',
            'mode_evaluation' => $syllabus->mode_evaluation
                ?: "Évaluations formatives [en ligne et/ou en présentiel]\nÉvaluations sommatives : contrôle continu, examen final [en présentiel]",
            'bibliographie' => $syllabus->bibliographie ?? '',
        ];

        $equipe = $syllabus->equipePedagogique();
        $this->equipe = collect(range(0, 2))
            ->map(fn (int $rang) => [
                'nom' => $equipe[$rang]['nom'] ?? '',
                'charge' => $equipe[$rang]['charge'] ?? ($rang === 0 ? 'CM' : ''),
            ])
            ->all();
    }

    public function fermer(): void
    {
        $this->matiereId = null;
        $this->piece = null;
        $this->champs = [];
        $this->equipe = [];
        $this->resetValidation();
    }

    public function enregistrer(bool $soumettre = false): void
    {
        $user = auth()->user();
        $matiere = $this->matiere($this->matiereId);

        if (! $matiere) {
            return;
        }

        $syllabus = Syllabus::pourMatiere($matiere, $user, $this->semestre);

        if (! $syllabus->estModifiable()) {
            $this->notifier('Ce syllabus est validé : il ne se modifie plus.', 'erreur');

            return;
        }

        $this->validate([
            'champs.niveau' => ['nullable', 'string', 'max:80'],
            'champs.langue' => ['required', 'string', 'max:40'],
            'champs.credits' => ['nullable', 'integer', 'min:0', 'max:60'],
            'champs.volume_total' => ['nullable', 'integer', 'min:0', 'max:999'],
            'champs.volume_cm' => ['nullable', 'integer', 'min:0', 'max:999'],
            'champs.volume_td' => ['nullable', 'integer', 'min:0', 'max:999'],
            'champs.volume_tp' => ['nullable', 'integer', 'min:0', 'max:999'],
            'champs.volume_tpe' => ['nullable', 'integer', 'min:0', 'max:999'],
            'piece' => [
                'nullable', 'file',
                'mimes:'.implode(',', config('documents.formats')),
                'max:'.config('documents.taille_max'),
            ],
        ], [
            'champs.langue.required' => "Indiquez la langue d'enseignement.",
            'piece.mimes' => 'Formats acceptés : '.implode(', ', config('documents.formats')).'.',
            'piece.max' => 'Le fichier dépasse '.round(config('documents.taille_max') / 1024).' Mo.',
        ]);

        foreach ($this->champs as $champ => $valeur) {
            $syllabus->{$champ} = is_string($valeur) ? (trim($valeur) ?: null) : $valeur;
        }

        $syllabus->equipe = collect($this->equipe)
            ->map(fn ($ligne) => ['nom' => trim((string) $ligne['nom']), 'charge' => trim((string) $ligne['charge'])])
            ->filter(fn ($ligne) => $ligne['nom'] !== '')
            ->values()
            ->all();

        // La chaine academique peut avoir bouge depuis la creation : on la
        // reprend a chaque enregistrement, c'est elle qui designe le chef.
        $chaine = \App\Models\Affectation::chaine('cours', $matiere->id);
        $syllabus->fill([
            'user_id' => $syllabus->user_id ?: $user->id,
            'ue_id' => $chaine['ue_id'],
            'specialite_id' => $chaine['specialite_id'],
            'filiere_id' => $chaine['filiere_id'],
            'departement_id' => $chaine['departement_id'],
        ]);

        if ($this->piece) {
            // Le nouveau fichier remplace l'ancien : un seul syllabus fait foi.
            if ($syllabus->fichier) {
                Storage::disk('local')->delete($syllabus->fichier);
            }

            $nom = $this->piece->getClientOriginalName();
            $syllabus->fichier = $this->piece->storeAs(
                'syllabus/'.$syllabus->annee_academique.'/'.$matiere->id,
                Str::uuid().'.'.$this->piece->getClientOriginalExtension(),
                'local',
            );
            $syllabus->fichier_nom = Str::limit($nom, 180, '');
            $syllabus->fichier_taille = $this->piece->getSize();
        }

        if (! $soumettre) {
            $syllabus->statut = $syllabus->statut === 'a_revoir' ? 'a_revoir' : 'brouillon';
            $syllabus->save();
            $this->piece = null;
            $this->notifier('Brouillon enregistré. Il n’est pas encore parti au chef de département.');

            return;
        }

        // Soumettre, c'est engager : soit le document est rempli, soit le
        // fichier de l'enseignant en tient lieu.
        $manquantes = collect(Syllabus::SECTIONS_REQUISES)
            ->filter(fn (string $champ) => blank($syllabus->{$champ}))
            ->map(fn (string $champ) => Syllabus::SECTIONS[$champ])
            ->values();

        if ($manquantes->isNotEmpty() && blank($syllabus->fichier)) {
            $syllabus->save();
            $this->piece = null;
            $this->notifier(
                'Brouillon enregistré. Avant de soumettre, remplissez : '.$manquantes->implode(', ')
                .' — ou joignez votre fichier.',
                'erreur',
            );

            return;
        }

        $syllabus->fill([
            'statut' => 'soumis',
            'motif' => null,
            'soumis_le' => now(),
            'tranche_par' => null,
            'tranche_le' => null,
        ])->save();

        $this->piece = null;
        $this->notifier(AvisSyllabus::depot($syllabus));
        $this->fermer();
    }

    public function retirerPiece(): void
    {
        $syllabus = Syllabus::where('cour_id', $this->matiereId)
            ->where('annee_academique', AnneeAcademique::courante())
            ->where('semestre', $this->semestre)
            ->first();

        if (! $syllabus || $syllabus->user_id !== auth()->id() || ! $syllabus->estModifiable()) {
            return;
        }

        if ($syllabus->fichier) {
            Storage::disk('local')->delete($syllabus->fichier);
        }

        $syllabus->update(['fichier' => null, 'fichier_nom' => null, 'fichier_taille' => null]);
        $this->notifier('Fichier retiré.');
    }

    private function notifier(string $message, string $type = 'success'): void
    {
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Mes syllabus</h1>
            <p class="text-gray-500">
                La description de chacune de vos matières, année {{ $annee }}.
                @if ($aDeposer > 0)
                    <span class="font-medium text-amber-700">{{ $aDeposer }} reste(nt) à soumettre.</span>
                @endif
            </p>
        </div>
        <div>
            <label for="semestre" class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Semestre</label>
            <select id="semestre" wire:model.live="semestre"
                    class="min-w-[9.5rem] rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="1">Semestre 1</option>
                <option value="2">Semestre 2</option>
            </select>
        </div>
    </div>

    @if ($showNotification)
        <div class="mb-6 rounded-xl px-4 py-3 text-sm {{ $notificationType === 'erreur' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
            <div class="flex items-start justify-between gap-3">
                <span>{{ $notificationMessage }}</span>
                <button wire:click="$set('showNotification', false)" class="text-xs underline shrink-0">Fermer</button>
            </div>
        </div>
    @endif


    @if ($showChoix)
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 px-6 py-5 mb-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-semibold text-gray-800">Choisir une matière</h2>
                    <p class="text-sm text-gray-500">Descendez jusqu'à la matière dont vous rédigez le syllabus.</p>
                </div>
                <button wire:click="$set('showChoix', false)" class="text-sm text-gray-600 hover:underline shrink-0">Annuler</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Département</label>
                    <select wire:model.live="choixDepartement"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">—</option>
                        @foreach ($cascade['departements'] as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Filière</label>
                    <select wire:model.live="choixFiliere" @disabled($cascade['filieres']->isEmpty())
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50">
                        <option value="">—</option>
                        @foreach ($cascade['filieres'] as $filiere)
                            <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Spécialité</label>
                    <select wire:model.live="choixSpecialite" @disabled($cascade['specialites']->isEmpty())
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50">
                        <option value="">—</option>
                        @foreach ($cascade['specialites'] as $specialite)
                            <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Unité d'enseignement</label>
                    <select wire:model.live="choixUe" @disabled($cascade['ues']->isEmpty())
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50">
                        <option value="">—</option>
                        @foreach ($cascade['ues'] as $ue)
                            <option value="{{ $ue->id }}">{{ $ue->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Matière</label>
                    <select wire:model.live="choixMatiere" @disabled($cascade['matieres']->isEmpty())
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-gray-50">
                        <option value="">—</option>
                        @foreach ($cascade['matieres'] as $matiere)
                            <option value="{{ $matiere->id }}">{{ $matiere->code ? $matiere->code.' - ' : '' }}{{ $matiere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    @if ($matieres->isEmpty())
        <div class="bg-white rounded-2xl shadow-lg p-10 text-center">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Aucune matière enregistrée à votre nom</h2>
            <p class="text-gray-600 mb-6">
                Vos matières apparaîtront ici dès que la scolarité vous les aura affectées pour l'année {{ $annee }}.
                En attendant, choisissez vous-même la matière dont vous rédigez le syllabus.
            </p>
            <button wire:click="$set('showChoix', true)"
                    class="px-5 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                Choisir une matière
            </button>
        </div>

    @elseif (! $enCours)
        <div class="flex justify-end mb-3">
            <button wire:click="$set('showChoix', true)" class="text-sm text-emerald-700 hover:underline">
                + Décrire une autre matière
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            @foreach ($matieres as $matiere)
                @php($fiche = $syllabus->get($matiere->id))
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">
                            {{ $matiere->code ? $matiere->code.' - ' : '' }}{{ $matiere->name }}
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ $matiere->ue?->name ?: '—' }}
                            @if ($matiere->specialite) · {{ $matiere->specialite->name }} @endif
                            @if ($fiche?->soumis_le) · soumis le {{ $fiche->soumis_le->format('d/m/Y') }} @endif
                        </p>
                        @if ($fiche?->statut === 'a_revoir' && $fiche->motif)
                            <p class="mt-1 text-sm text-red-700">À revoir : {{ $fiche->motif }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @php($statut = $fiche?->statut ?? 'manquant')
                        <span @class([
                        'px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap',
                        'bg-amber-100 text-amber-800' => $statut === 'manquant',
                        'bg-gray-200 text-gray-700' => $statut === 'brouillon',
                        'bg-sky-100 text-sky-800' => $statut === 'soumis',
                        'bg-green-100 text-green-800' => $statut === 'valide',
                        'bg-red-100 text-red-800' => $statut === 'a_revoir',
                            ])>
                            {{ $statut === 'manquant' ? 'À rédiger' : $fiche->libelleStatut() }}
                        </span>
                        @if ($fiche?->exists)
                            <a href="/syllabus/{{ $fiche->id }}/pdf" target="_blank" rel="noopener"
                               class="text-sm text-gray-600 hover:underline">PDF</a>
                        @endif
                        <button wire:click="ouvrir({{ $matiere->id }})"
                                class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                            {{ $statut === 'valide' ? 'Consulter' : ($statut === 'manquant' ? 'Rédiger' : 'Modifier') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- Le formulaire, section par section, dans l'ordre du document officiel. --}}
        @php($fiche = $courant)
        <div class="space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Syllabus — semestre {{ $semestre }} · {{ $annee }}</p>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $enCours->code ? $enCours->code.' - ' : '' }}{{ $enCours->name }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $enCours->ue?->name ?: '—' }}
                            @if ($enCours->specialite) · {{ $enCours->specialite->name }} @endif
                        </p>
                    </div>
                    <button wire:click="fermer" class="text-sm text-gray-600 hover:underline shrink-0">← Revenir à mes matières</button>
                </div>

                @if ($fiche?->statut === 'a_revoir' && $fiche->motif)
                    <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-400 rounded text-sm text-red-800">
                        <strong>À revoir :</strong> {{ $fiche->motif }}
                    </div>
                @endif

                @if ($fiche?->statut === 'valide')
                    <div class="mt-4 p-4 bg-green-50 border-l-4 border-green-400 rounded text-sm text-green-800">
                        Ce syllabus a été validé{{ $fiche->tranche_le ? ' le '.$fiche->tranche_le->format('d/m/Y') : '' }}
                        : il ne se modifie plus.
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <h3 class="font-semibold text-gray-800 mb-4">Identification</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Niveau</label>
                        <input type="text" wire:model="champs.niveau" placeholder="2"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('champs.niveau')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Langue d'enseignement</label>
                        <input type="text" wire:model="champs.langue"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('champs.langue')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Nombre de crédits</label>
                        <input type="number" min="0" wire:model="champs.credits"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('champs.credits')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-4">
                    @foreach ([
                        'volume_total' => 'Volume total',
                        'volume_cm' => 'CM',
                        'volume_td' => 'TD',
                        'volume_tp' => 'TP',
                        'volume_tpe' => 'TPE',
                    ] as $champ => $intitule)
                        <div>
                            <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">{{ $intitule }} (h)</label>
                            <input type="number" min="0" wire:model="champs.{{ $champ }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @error('champs.'.$champ)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <h3 class="font-semibold text-gray-800 mb-1">Équipe pédagogique</h3>
                <p class="text-sm text-gray-500 mb-4">Qui assure le cours, les TD, les TP.</p>
                @foreach ($equipe as $rang => $ligne)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div class="sm:col-span-2">
                            <input type="text" wire:model="equipe.{{ $rang }}.nom" placeholder="Enseignant {{ $rang + 1 }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <input type="text" wire:model="equipe.{{ $rang }}.charge" placeholder="CM / TD / TP"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                @endforeach
            </div>

            @foreach (\App\Models\Syllabus::SECTIONS as $champ => $intitule)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                    <label class="block font-semibold text-gray-800 mb-1" for="champ-{{ $champ }}">
                        {{ $intitule }}
                        @if (in_array($champ, \App\Models\Syllabus::SECTIONS_REQUISES, true))
                            <span class="text-xs font-normal text-amber-700">— attendu</span>
                        @endif
                    </label>
                    <textarea id="champ-{{ $champ }}" wire:model="champs.{{ $champ }}" rows="{{ $champ === 'contenu' ? 10 : 4 }}"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                              placeholder="Une idée par ligne."></textarea>
                </div>
            @endforeach

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <h3 class="font-semibold text-gray-800 mb-1">Votre fichier (facultatif)</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Si vous avez déjà rédigé votre syllabus, joignez-le : il tient lieu des sections ci-dessus.
                </p>

                @if ($fiche?->fichier_nom)
                    <div class="flex items-center justify-between gap-3 mb-4 px-4 py-3 border border-gray-100 rounded-xl">
                        <a href="/syllabus/{{ $fiche->id }}/fichier" class="text-sm text-indigo-600 hover:underline break-all">
                            {{ $fiche->fichier_nom }}
                        </a>
                        @if ($fiche->estModifiable())
                            <button wire:click="retirerPiece" wire:confirm="Retirer ce fichier ?"
                                    class="text-sm text-red-600 hover:underline shrink-0">Retirer</button>
                        @endif
                    </div>
                @endif

                <input type="file" wire:model="piece"
                       class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                @error('piece')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p wire:loading wire:target="piece" class="mt-2 text-xs text-gray-500">Envoi du fichier…</p>
            </div>

            @if (! $fiche || $fiche->estModifiable())
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button wire:click="enregistrer(false)" wire:loading.attr="disabled"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Enregistrer le brouillon
                    </button>
                    <button wire:click="enregistrer(true)" wire:loading.attr="disabled"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                        Soumettre au chef de département
                    </button>
                    <p class="text-sm text-gray-500">La soumission prévient le chef de département par courriel.</p>
                </div>
            @endif
        </div>
    @endif

</div>
@endvolt
</x-layouts.app>
