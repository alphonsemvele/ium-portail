<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\EmploiDuTemps;
use App\Support\AnneeAcademique;
use App\Support\CascadeAcademique;
use Carbon\Carbon;

name('admin.emplois-du-temps');
middleware(['auth', 'verified', 'role']);

/**
 * Emplois du temps : une semaine, une specialite.
 *
 * On cree la semaine ici, on la remplit dans la grille, on la publie. Tant
 * qu'elle est en brouillon, les enseignants ne la voient pas : l'emploi du
 * temps se construit souvent a plusieurs mains et change jusqu'au dernier
 * moment.
 *
 * La cascade cycle > departement > filiere > specialite sert deux fois : elle
 * filtre la liste, et elle designe la specialite de la semaine qu'on cree.
 * On filtre donc jusqu'a la classe voulue, puis on ouvre « Nouvelle semaine »
 * sans avoir a la rechoisir.
 */
new class extends Component {
    use CascadeAcademique;

    public string $annee = '';
    public string $filtreSemestre = '';

    // Formulaire
    public string $niveau = '';
    public int $semestre = 1;
    public string $date_debut = '';

    public bool $showCreateModal = false;
    public bool $showDupliquerModal = false;
    public bool $showDeleteModal = false;
    public ?EmploiDuTemps $emploiElement = null;
    public string $dupliquer_vers = '';

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public array $formErrors = [];

    public function mount(): void
    {
        $this->annee = AnneeAcademique::courante();
        $this->date_debut = Carbon::now()->startOfWeek()->toDateString();
        $this->initialiserCascade();
    }

    public function with(): array
    {
        return [
            'emplois' => EmploiDuTemps::with(['specialite.filiere'])
                ->withCount('seances')
                ->where('annee_academique', $this->annee)
                ->when($this->specialite_id, fn ($q) => $q->where('specialite_id', $this->specialite_id))
                // Sans specialite choisie, on suit l'etage au-dessus.
                ->when(! $this->specialite_id && $this->filiere_id,
                    fn ($q) => $q->whereIn('specialite_id', $this->specialites->pluck('id')))
                ->when($this->filtreSemestre !== '', fn ($q) => $q->where('semestre', $this->filtreSemestre))
                ->orderByDesc('date_debut')->orderBy('niveau')
                ->get(),
            'annees' => AnneeAcademique::options(),
        ];
    }

    public function functionShowCreateModal(): void
    {
        $this->niveau = '';
        $this->semestre = 1;
        $this->date_debut = Carbon::now()->startOfWeek()->toDateString();
        $this->formErrors = [];
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        $this->formErrors = [];

        $valide = $this->validate([
            'specialite_id' => 'required|exists:specialites,id',
            'niveau' => 'required|string|max:80',
            'semestre' => 'required|integer|min:1|max:4',
            'date_debut' => 'required|date',
            'annee' => 'required|string|max:20',
        ], [], [
            'specialite_id' => 'spécialité',
            'date_debut' => 'semaine',
        ]);

        // La semaine va du lundi au samedi, comme sur le document officiel.
        $lundi = Carbon::parse($valide['date_debut'])->startOfWeek();

        $existe = EmploiDuTemps::where('specialite_id', $valide['specialite_id'])
            ->where('niveau', $valide['niveau'])
            ->where('semestre', $valide['semestre'])
            ->whereDate('date_debut', $lundi)
            ->first();

        if ($existe) {
            $this->formErrors['general'] = 'Cette semaine existe déjà pour ce niveau. Ouvrez-la plutôt que d\'en créer une seconde.';

            return;
        }

        $emploi = EmploiDuTemps::create([
            'specialite_id' => $valide['specialite_id'],
            'niveau' => $valide['niveau'],
            'semestre' => $valide['semestre'],
            'annee_academique' => $this->annee,
            'date_debut' => $lundi,
            'date_fin' => $lundi->copy()->addDays(5),
            'creneaux' => config('emploi_du_temps.creneaux'),
            'statut' => 'brouillon',
            'cree_par' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->redirect('/admin/emploi-du-temps/grille?emploi='.$emploi->id);
    }

    public function basculerPublication(int $id): void
    {
        $emploi = EmploiDuTemps::findOrFail($id);

        if ($emploi->estPublie()) {
            $emploi->update(['statut' => 'brouillon', 'publie_le' => null]);
            $this->notifier('Emploi du temps retiré des espaces enseignants.');

            return;
        }

        $emploi->update(['statut' => 'publie', 'publie_le' => now()]);
        $this->notifier('Emploi du temps publié : les enseignants concernés le voient.');
    }

    public function functionShowDupliquerModal(int $id): void
    {
        $this->emploiElement = EmploiDuTemps::findOrFail($id);
        $this->dupliquer_vers = $this->emploiElement->date_debut->copy()->addWeek()->toDateString();
        $this->formErrors = [];
        $this->showDupliquerModal = true;
    }

    /**
     * Recopie la semaine sur une autre : d'une semaine a l'autre, seuls les
     * numeros de seance changent — on les avance d'autant.
     */
    public function dupliquer(): void
    {
        $this->formErrors = [];
        $source = $this->emploiElement;

        if (! $source) {
            return;
        }

        $lundi = Carbon::parse($this->dupliquer_vers)->startOfWeek();

        $existe = EmploiDuTemps::where('specialite_id', $source->specialite_id)
            ->where('niveau', $source->niveau)
            ->where('semestre', $source->semestre)
            ->whereDate('date_debut', $lundi)
            ->exists();

        if ($existe) {
            $this->formErrors['general'] = 'Cette semaine existe déjà.';

            return;
        }

        $copie = EmploiDuTemps::create([
            'specialite_id' => $source->specialite_id,
            'niveau' => $source->niveau,
            'semestre' => $source->semestre,
            'annee_academique' => $source->annee_academique,
            'date_debut' => $lundi,
            'date_fin' => $lundi->copy()->addDays(5),
            'creneaux' => $source->creneaux,
            'statut' => 'brouillon',
            'cree_par' => auth()->id(),
        ]);

        foreach ($source->seances as $seance) {
            $copie->seances()->create($seance->only([
                'jour', 'creneau', 'heure_debut', 'heure_fin', 'type', 'cour_id',
                'intitule', 'enseignant_id', 'salle_id', 'salle_libelle', 'seance_total',
            ]) + [
                // La seance suivante de la meme matiere : 1/5 devient 2/5.
                'seance_numero' => $seance->seance_numero ? $seance->seance_numero + 1 : null,
            ]);
        }

        $this->showDupliquerModal = false;
        $this->emploiElement = null;
        $this->redirect('/admin/emploi-du-temps/grille?emploi='.$copie->id);
    }

    public function functionShowDeleteModal(int $id): void
    {
        $this->emploiElement = EmploiDuTemps::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function supprimer(): void
    {
        $this->emploiElement?->delete();
        $this->emploiElement = null;
        $this->showDeleteModal = false;
        $this->notifier('Emploi du temps supprimé.');
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showDupliquerModal = false;
        $this->showDeleteModal = false;
        $this->emploiElement = null;
        $this->formErrors = [];
    }

    private function notifier(string $message): void
    {
        $this->notificationMessage = $message;
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Emplois du temps</h1>
            <p class="text-gray-500">
                Une semaine, une spécialité. Publié, l'emploi du temps apparaît dans l'espace de chaque enseignant concerné.
            </p>
        </div>
        <button wire:click="functionShowCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            + Nouvelle semaine
        </button>
    </div>

    <!-- Filtres : la cascade de l'institut, comme sur l'écran des notes -->
    <div class="bg-white rounded-2xl shadow p-5 mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Année</label>
            <select wire:model.live="annee" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($annees as $an)<option value="{{ $an }}" @selected($an === $annee)>{{ $an }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Cycle</label>
            <select wire:model.live="cycle_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous</option>
                @foreach ($cycles as $cycle)<option value="{{ $cycle->id }}">{{ $cycle->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Département</label>
            <select wire:model.live="departement_id" @disabled(! $cycle_id)
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ $cycle_id ? 'Tous' : '—' }}</option>
                @foreach ($departements as $departement)<option value="{{ $departement->id }}">{{ $departement->nom }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Filière</label>
            <select wire:model.live="filiere_id" @disabled(! $departement_id)
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ $departement_id ? 'Toutes' : '—' }}</option>
                @foreach ($filieres as $filiere)<option value="{{ $filiere->id }}">{{ $filiere->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Spécialité</label>
            <select wire:model.live="specialite_id" @disabled(! $filiere_id)
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                <option value="">{{ $filiere_id ? 'Toutes' : '—' }}</option>
                @foreach ($specialites as $specialite)<option value="{{ $specialite->id }}">{{ $specialite->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Semestre</label>
            <select wire:model.live="filtreSemestre" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous</option>
                <option value="1">Semestre 1</option>
                <option value="2">Semestre 2</option>
            </select>
        </div>
    </div>

    <!-- Modal : nouvelle semaine -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouvelle semaine</h2>

                @if (!empty($formErrors['general']))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ $formErrors['general'] }}</div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    <div class="p-4 rounded-xl border {{ $specialite_id ? 'bg-indigo-50 border-indigo-200' : 'bg-amber-50 border-amber-200' }}">
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $specialite_id ? 'text-indigo-700' : 'text-amber-700' }}">Spécialité</p>
                        @if ($specialite_id)
                            <p class="mt-1 font-medium text-gray-800">{{ $specialites->firstWhere('id', (int) $specialite_id)?->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $cycles->firstWhere('id', (int) $cycle_id)?->name }} ›
                                {{ $departements->firstWhere('id', (int) $departement_id)?->nom }} ›
                                {{ $filieres->firstWhere('id', (int) $filiere_id)?->name }}
                            </p>
                        @else
                            <p class="mt-1 text-sm text-amber-800">
                                Choisissez d'abord cycle, département, filière et spécialité dans les filtres ci-dessus :
                                un emploi du temps appartient à une spécialité.
                            </p>
                        @endif
                        @error('specialite_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Niveau / classe *</label>
                            <input wire:model="niveau" type="text" required maxlength="80" placeholder="BTS 1 – IRT"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('niveau') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Semestre *</label>
                            <select wire:model="semestre" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1">Semestre 1</option>
                                <option value="2">Semestre 2</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semaine du *</label>
                        <input wire:model="date_debut" type="date" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">La semaine est ramenée au lundi, et va jusqu'au samedi.</p>
                        @error('date_debut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-4 pt-2">
                        <button type="submit" @disabled(! $specialite_id)
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Créer et remplir
                        </button>
                        <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal : dupliquer -->
    @if ($showDupliquerModal && $emploiElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Dupliquer la semaine</h2>
                <p class="text-sm text-gray-500 mb-6">
                    {{ $emploiElement->titre() }} — {{ $emploiElement->semaine() }}.
                    Les numéros de séance avancent d'un cran (1/5 devient 2/5).
                </p>

                @if (!empty($formErrors['general']))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ $formErrors['general'] }}</div>
                @endif

                <form wire:submit="dupliquer" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vers la semaine du *</label>
                        <input wire:model="dupliquer_vers" type="date" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Dupliquer</button>
                        <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal : suppression -->
    @if ($showDeleteModal && $emploiElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-red-600 mb-4">Supprimer l'emploi du temps</h3>
                <p class="mb-6">
                    {{ $emploiElement->titre() }}, {{ $emploiElement->semaine() }} — avec toutes ses séances. C'est définitif.
                </p>
                <div class="flex justify-end gap-4">
                    <button wire:click="supprimer" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Liste -->
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Semaines — {{ $annee }}</h2>

        @if ($emplois->isEmpty())
            <p class="text-center text-gray-500 py-8">Aucun emploi du temps pour cette sélection.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-4 text-sm font-medium text-gray-600">Spécialité / niveau</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Semaine</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Semestre</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Séances</th>
                            <th class="p-4 text-sm font-medium text-gray-600">État</th>
                            <th class="p-4 text-sm font-medium text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emplois as $emploi)
                            <tr class="bg-gray-50 hover:bg-gray-100 transition duration-200">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">{{ $emploi->specialite?->name ?? 'Spécialité supprimée' }}</div>
                                    <div class="text-xs text-gray-500">{{ $emploi->niveau }}</div>
                                </td>
                                <td class="p-4 text-sm">{{ $emploi->semaine() }}</td>
                                <td class="p-4 text-sm">S{{ $emploi->semestre }}</td>
                                <td class="p-4 text-sm">{{ $emploi->seances_count }}</td>
                                <td class="p-4">
                                    @if ($emploi->estPublie())
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Publié</span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Brouillon</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="/admin/emploi-du-temps/grille?emploi={{ $emploi->id }}"
                                           class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Remplir la grille" aria-label="Remplir la grille">
                                            <x-icone-action nom="modifier" />
                                        </a>
                                        <a href="/admin/emploi-du-temps/{{ $emploi->id }}/pdf" target="_blank"
                                           class="bouton-icone text-sky-600 hover:bg-sky-50" title="PDF" aria-label="PDF">
                                            <x-icone-action nom="voir" />
                                        </a>
                                        <button wire:click="functionShowDupliquerModal({{ $emploi->id }})"
                                                class="bouton-icone text-gray-600 hover:bg-gray-100" title="Dupliquer" aria-label="Dupliquer">
                                            <x-icone-action nom="partager" />
                                        </button>
                                        @if ($emploi->estPublie())
                                            <button wire:click="basculerPublication({{ $emploi->id }})"
                                                    class="bouton-icone text-amber-600 hover:bg-amber-50" title="Dépublier" aria-label="Dépublier">
                                                <x-icone-action nom="desactiver" />
                                            </button>
                                        @else
                                            <button wire:click="basculerPublication({{ $emploi->id }})"
                                                    class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Publier" aria-label="Publier">
                                                <x-icone-action nom="activer" />
                                            </button>
                                        @endif
                                        <button wire:click="functionShowDeleteModal({{ $emploi->id }})"
                                                class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer">
                                            <x-icone-action nom="supprimer" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)">
            <div class="p-4 rounded-xl shadow-lg border-l-4 bg-green-50 border-green-500 text-green-800">
                {{ $notificationMessage }}
            </div>
        </div>
    @endif
</div>
@endvolt
</x-layouts.app>
