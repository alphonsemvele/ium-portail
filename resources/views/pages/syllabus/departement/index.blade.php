<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Cour;
use App\Models\Departement;
use App\Models\Syllabus;
use App\Support\AnneeAcademique;
use App\Support\AvisSyllabus;

name('syllabus.departement');
middleware(['auth', 'verified', 'role']);

/**
 * « Syllabus du département » : ce que le chef de departement attend, ce
 * qu'il a recu, et ce qu'il en decide.
 *
 * Les matieres sans syllabus y figurent aussi : c'est la moitie du travail
 * de savoir qui n'a pas rendu.
 */
new class extends Component {
    public int $semestre = 1;

    public string $filtre = 'tous';

    /** Une page de liste : 474 matieres d'un coup rendent l'ecran illisible. */
    public const PAR_PAGE = 40;

    public int $page = 1;

    /** Le syllabus ouvert en lecture. */
    public ?int $ouvert = null;

    public bool $showRevision = false;
    public string $motif = '';

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';

    public function mount(): void
    {
        $ouvrir = (int) request('ouvrir');

        if ($ouvrir > 0) {
            $syllabus = Syllabus::find($ouvrir);

            if ($syllabus && $this->maCharge()->contains($syllabus->departement_id)) {
                $this->semestre = (int) $syllabus->semestre;
                $this->ouvert = $syllabus->id;
            }
        }
    }

    /** Les departements dont l'utilisateur repond. */
    private function maCharge(): \Illuminate\Support\Collection
    {
        return collect(auth()->user()->departementsDirigees());
    }

    public function updated(string $champ): void
    {
        // Changer de filtre, c'est repartir du debut de la liste.
        if ($champ !== 'page') {
            $this->page = 1;
        }
    }

    public function with(): array
    {
        $annee = AnneeAcademique::courante();
        $departements = $this->maCharge();

        // Toutes les matieres du departement, par l'UE ou par la specialite :
        // les deux rattachements existent dans le catalogue.
        $matieres = Cour::with(['ue.specialite.filiere', 'specialite.filiere', 'responsable'])
            ->where('status', '!=', 'failed')
            ->where(function ($requete) use ($departements) {
                $requete->whereHas('ue.specialite.filiere', fn ($q) => $q->whereIn('departement_id', $departements))
                    ->orWhereHas('specialite.filiere', fn ($q) => $q->whereIn('departement_id', $departements));
            })
            ->orderBy('name')
            ->get();

        $syllabus = Syllabus::with('enseignant')
            ->whereIn('cour_id', $matieres->pluck('id'))
            ->where('annee_academique', $annee)
            ->where('semestre', $this->semestre)
            ->get()
            ->keyBy('cour_id');

        $lignes = $matieres
            ->map(fn (Cour $matiere) => [
                'matiere' => $matiere,
                'fiche' => $syllabus->get($matiere->id),
                'statut' => $syllabus->get($matiere->id)?->statut ?? 'manquant',
            ])
            ->filter(fn (array $ligne) => $this->filtre === 'tous' || $ligne['statut'] === $this->filtre)
            ->values();

        $pages = max(1, (int) ceil($lignes->count() / self::PAR_PAGE));
        $this->page = min(max(1, $this->page), $pages);

        return [
            'annee' => $annee,
            'lignes' => $lignes->slice(($this->page - 1) * self::PAR_PAGE, self::PAR_PAGE),
            'nombre' => $lignes->count(),
            'pages' => $pages,
            'nomsDepartements' => Departement::whereIn('id', $departements)->pluck('nom')->implode(', '),
            'compte' => [
                'total' => $matieres->count(),
                'soumis' => $syllabus->whereIn('statut', ['soumis', 'valide', 'a_revoir'])->count(),
                'valides' => $syllabus->where('statut', 'valide')->count(),
                'attente' => $syllabus->where('statut', 'soumis')->count(),
            ],
            'detail' => $this->ouvert ? $syllabus->firstWhere('id', $this->ouvert) : null,
        ];
    }

    public function updatedSemestre(): void
    {
        $this->ouvert = null;
    }

    public function lire(int $syllabusId): void
    {
        $this->ouvert = $this->mien($syllabusId)?->id;
    }

    public function fermer(): void
    {
        $this->ouvert = null;
        $this->showRevision = false;
        $this->motif = '';
    }

    /** Le syllabus demande, s'il relève bien d'un departement que je dirige. */
    private function mien(int $syllabusId): ?Syllabus
    {
        $syllabus = Syllabus::find($syllabusId);

        return $syllabus && $this->maCharge()->contains($syllabus->departement_id) ? $syllabus : null;
    }

    public function valider(int $syllabusId): void
    {
        $syllabus = $this->mien($syllabusId);

        if (! $syllabus || $syllabus->statut !== 'soumis') {
            return;
        }

        $syllabus->update([
            'statut' => 'valide',
            'motif' => null,
            'tranche_par' => auth()->id(),
            'tranche_le' => now(),
        ]);

        $this->notifier(AvisSyllabus::decision($syllabus->fresh('enseignant')));
        $this->fermer();
    }

    public function ouvrirRevision(int $syllabusId): void
    {
        $this->ouvert = $this->mien($syllabusId)?->id;
        $this->motif = '';
        $this->showRevision = $this->ouvert !== null;
    }

    public function demanderRevision(): void
    {
        $syllabus = $this->ouvert ? $this->mien($this->ouvert) : null;

        if (! $syllabus || $syllabus->statut !== 'soumis') {
            return;
        }

        $this->validate(
            ['motif' => ['required', 'string', 'min:5', 'max:1000']],
            ['motif.required' => "Dites ce qui doit être repris : sans motif, l'enseignant redépose la même chose."],
        );

        $syllabus->update([
            'statut' => 'a_revoir',
            'motif' => trim($this->motif),
            'tranche_par' => auth()->id(),
            'tranche_le' => now(),
        ]);

        $this->notifier(AvisSyllabus::decision($syllabus->fresh('enseignant')));
        $this->fermer();
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
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Syllabus du département</h1>
            <p class="text-gray-500">{{ $nomsDepartements ?: 'Aucun département' }} · année {{ $annee }}</p>
        </div>
        <div class="flex gap-3">
            <div>
                <label for="semestre" class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Semestre</label>
                <select id="semestre" wire:model.live="semestre"
                        class="min-w-[9.5rem] rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="1">Semestre 1</option>
                    <option value="2">Semestre 2</option>
                </select>
            </div>
            <div>
                <label for="filtre" class="block text-xs uppercase tracking-wide text-gray-500 mb-1">État</label>
                <select id="filtre" wire:model.live="filtre"
                        class="min-w-[9.5rem] rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="tous">Tous</option>
                    <option value="manquant">Non déposés</option>
                    <option value="soumis">À examiner</option>
                    <option value="valide">Validés</option>
                    <option value="a_revoir">À revoir</option>
                    <option value="brouillon">Brouillons</option>
                </select>
            </div>
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

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Matières', $compte['total'], 'text-gray-800'],
            ['Syllabus reçus', $compte['soumis'], 'text-sky-700'],
            ['À examiner', $compte['attente'], 'text-amber-700'],
            ['Validés', $compte['valides'], 'text-green-700'],
        ] as [$intitule, $valeur, $couleur])
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs uppercase text-gray-500">{{ $intitule }}</p>
                <p class="text-3xl font-bold {{ $couleur }}">{{ $valeur }}</p>
            </div>
        @endforeach
    </div>

    @if ($detail)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Semestre {{ $detail->semestre }} · {{ $detail->annee_academique }}
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $detail->intitule() }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ trim($detail->enseignant?->name.' '.$detail->enseignant?->lastname) ?: 'Enseignant inconnu' }}
                        @if ($detail->soumis_le) · soumis le {{ $detail->soumis_le->format('d/m/Y à H:i') }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="/syllabus/{{ $detail->id }}/pdf" target="_blank" rel="noopener"
                       class="text-sm text-gray-600 hover:underline">PDF</a>
                    <button wire:click="fermer" class="text-sm text-gray-600 hover:underline">Fermer</button>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><span class="block text-xs uppercase text-gray-500">Niveau</span>{{ $detail->niveau ?: '—' }}</div>
                    <div><span class="block text-xs uppercase text-gray-500">Crédits</span>{{ $detail->credits ?: '—' }}</div>
                    <div><span class="block text-xs uppercase text-gray-500">Volume</span>{{ $detail->volume_total ? $detail->volume_total.' h' : '—' }}</div>
                    <div><span class="block text-xs uppercase text-gray-500">Langue</span>{{ $detail->langue ?: '—' }}</div>
                </div>

                @if ($detail->fichier_nom)
                    <p class="text-sm">
                        Fichier joint :
                        <a href="/syllabus/{{ $detail->id }}/fichier" class="text-indigo-600 hover:underline break-all">{{ $detail->fichier_nom }}</a>
                    </p>
                @endif

                @foreach (\App\Models\Syllabus::SECTIONS as $champ => $intitule)
                    @if (filled($detail->{$champ}))
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ $intitule }}</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $detail->{$champ} }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($detail->statut === 'soumis')
                <div class="px-6 py-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                    <button wire:click="valider({{ $detail->id }})"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                        Valider
                    </button>
                    <button wire:click="ouvrirRevision({{ $detail->id }})"
                            class="px-5 py-2.5 text-sm font-medium rounded-lg border border-amber-300 text-amber-800 hover:bg-amber-50">
                        Demander une révision
                    </button>
                    <span class="text-sm text-gray-500">L'enseignant est prévenu par courriel.</span>
                </div>
            @endif
        </div>
    @endif

    @if ($showRevision)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Demander une révision</h2>
                <p class="text-sm text-gray-500 mb-4">Ce motif part à l'enseignant : dites ce qui doit être repris.</p>
                <textarea wire:model="motif" rows="4"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500"
                          placeholder="Le chronogramme ne couvre pas les 30 heures…"></textarea>
                @error('motif')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <div class="mt-5 flex justify-end gap-3">
                    <button wire:click="$set('showRevision', false)"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button wire:click="demanderRevision"
                            class="px-4 py-2 text-sm rounded-lg bg-amber-600 text-white hover:bg-amber-700">Envoyer</button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($lignes->isEmpty())
            <p class="px-6 py-10 text-center text-gray-600">Aucune matière ne correspond à ce filtre.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 font-medium">Matière</th>
                            <th class="px-5 py-3 font-medium">Enseignant</th>
                            <th class="px-5 py-3 font-medium">État</th>
                            <th class="px-5 py-3 font-medium text-right">—</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($lignes as $ligne)
                            @php($fiche = $ligne['fiche'])
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">
                                        {{ $ligne['matiere']->code ? $ligne['matiere']->code.' - ' : '' }}{{ $ligne['matiere']->name }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $ligne['matiere']->ue?->name ?: '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-700">
                                    {{ trim(($fiche?->enseignant?->name ?? $ligne['matiere']->responsable?->name ?? '')
                                        .' '.($fiche?->enseignant?->lastname ?? $ligne['matiere']->responsable?->lastname ?? '')) ?: '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span @class([
                        'px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap',

                                            'bg-amber-100 text-amber-800' => $ligne['statut'] === 'manquant',
                                            'bg-gray-200 text-gray-700' => $ligne['statut'] === 'brouillon',
                                            'bg-sky-100 text-sky-800' => $ligne['statut'] === 'soumis',
                                            'bg-green-100 text-green-800' => $ligne['statut'] === 'valide',
                                            'bg-red-100 text-red-800' => $ligne['statut'] === 'a_revoir',
                                        ])>
                                        {{ $ligne['statut'] === 'manquant' ? 'Non déposé' : $fiche->libelleStatut() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if ($fiche && $fiche->estSoumis())
                                        <button wire:click="lire({{ $fiche->id }})"
                                                class="text-emerald-700 hover:underline">Lire</button>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pages > 1)
                <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ $nombre }} matières · page {{ $page }} sur {{ $pages }}</span>
                    <div class="flex gap-2">
                        <button wire:click="$set('page', {{ max(1, $page - 1) }})" @disabled($page <= 1)
                                class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40">Précédent</button>
                        <button wire:click="$set('page', {{ min($pages, $page + 1) }})" @disabled($page >= $pages)
                                class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40">Suivant</button>
                    </div>
                </div>
            @endif
        @endif
    </div>

</div>
@endvolt
</x-layouts.app>
