<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Cour;
use App\Models\Departement;
use App\Models\Syllabus;
use App\Support\AnneeAcademique;

name('admin.syllabus');
middleware(['auth', 'verified', 'role']);

/**
 * Les syllabus de l'institut, departement par departement : qui a rendu, qui
 * n'a pas rendu, et ou en est la validation.
 *
 * La decision appartient au chef de departement (« Syllabus du département ») ;
 * l'administration regarde et relance.
 */
new class extends Component {
    public int $semestre = 1;

    public string $departement = 'tous';

    public string $filtre = 'tous';

    /** Une page de liste : 474 matieres d'un coup rendent l'ecran illisible. */
    public const PAR_PAGE = 40;

    public int $page = 1;

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
        $departements = Departement::orderBy('nom')->get();

        $matieres = Cour::with(['ue.specialite.filiere', 'specialite.filiere', 'responsable'])
            ->where('status', '!=', 'failed')
            ->when($this->departement !== 'tous', function ($requete) {
                $requete->where(function ($q) {
                    $q->whereHas('ue.specialite.filiere', fn ($f) => $f->where('departement_id', $this->departement))
                        ->orWhereHas('specialite.filiere', fn ($f) => $f->where('departement_id', $this->departement));
                });
            })
            ->orderBy('name')
            ->get();

        $syllabus = Syllabus::with(['enseignant', 'departement'])
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
            'departements' => $departements,
            'lignes' => $lignes->slice(($this->page - 1) * self::PAR_PAGE, self::PAR_PAGE),
            'nombre' => $lignes->count(),
            'pages' => $pages,
            'compte' => [
                'total' => $matieres->count(),
                'recus' => $syllabus->whereIn('statut', ['soumis', 'valide', 'a_revoir'])->count(),
                'attente' => $syllabus->where('statut', 'soumis')->count(),
                'valides' => $syllabus->where('statut', 'valide')->count(),
            ],
        ];
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Syllabus</h1>
            <p class="text-gray-500">Suivi des descriptions de cours · année {{ $annee }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <div>
                <label for="departement" class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Département</label>
                <select id="departement" wire:model.live="departement"
                        class="min-w-[9.5rem] rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="tous">Tous</option>
                    @foreach ($departements as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                    @endforeach
                </select>
            </div>
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
                    <option value="soumis">En attente du chef</option>
                    <option value="valide">Validés</option>
                    <option value="a_revoir">À revoir</option>
                    <option value="brouillon">Brouillons</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['Matières', $compte['total'], 'text-gray-800'],
            ['Syllabus reçus', $compte['recus'], 'text-sky-700'],
            ['En attente du chef', $compte['attente'], 'text-amber-700'],
            ['Validés', $compte['valides'], 'text-green-700'],
        ] as [$intitule, $valeur, $couleur])
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs uppercase text-gray-500">{{ $intitule }}</p>
                <p class="text-3xl font-bold {{ $couleur }}">{{ $valeur }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($lignes->isEmpty())
            <p class="px-6 py-10 text-center text-gray-600">Aucune matière ne correspond à ces filtres.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 font-medium">Matière</th>
                            <th class="px-5 py-3 font-medium">Rattachement</th>
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
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ $fiche?->chemin() ?: ($ligne['matiere']->ue?->name ?: '—') }}
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
                                    @if ($fiche?->exists)
                                        <a href="/syllabus/{{ $fiche->id }}/pdf" target="_blank" rel="noopener"
                                           class="text-emerald-700 hover:underline">PDF</a>
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
