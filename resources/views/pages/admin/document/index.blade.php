<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\DemandeDocument;
use App\Models\DepotDocument;
use App\Support\AnneeAcademique;
use App\Support\CascadeAcademique;

name('admin.documents');
middleware(['auth', 'verified', 'role']);

/**
 * Documents demandes au personnel.
 *
 * On ouvre une demande et on la dirige en descendant la cascade de
 * l'institut — cycle, departement, filiere, specialite. On s'arrete ou l'on
 * veut : sans rien choisir, la demande s'adresse a tout le personnel.
 *
 * Chaque agent concerne la voit dans son espace. Le suivi (qui a rendu, qui
 * n'a pas rendu) est a cote : page /admin/document/suivi.
 */
new class extends Component {
    use CascadeAcademique;

    public string $annee = '';
    public string $filtreStatut = '';

    // Formulaire
    public ?int $demande_id = null;
    public string $titre = '';
    public string $description = '';
    public string $date_limite = '';
    public bool $obligatoire = true;
    public bool $plusieurs_fichiers = false;
    public string $formats = '';

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;
    public ?DemandeDocument $demandeElement = null;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public array $formErrors = [];

    public function mount(): void
    {
        $this->annee = AnneeAcademique::courante();
        $this->initialiserCascade();
    }

    public function with(): array
    {
        $demandes = DemandeDocument::where('annee_academique', $this->annee)
            ->when($this->filtreStatut !== '', fn ($q) => $q->where('statut', $this->filtreStatut))
            ->orderByDesc('created_at')
            ->get();

        // Compte des depots par demande, pour la colonne « rendus ».
        $rendus = DepotDocument::selectRaw('demande_document_id, count(*) as total')
            ->whereIn('demande_document_id', $demandes->pluck('id'))
            ->whereNotNull('depose_le')
            ->groupBy('demande_document_id')
            ->pluck('total', 'demande_document_id');

        return [
            'demandes' => $demandes,
            'rendus' => $rendus,
            'attendus' => $demandes->mapWithKeys(fn (DemandeDocument $d) => [$d->id => $d->concernes()->count()]),
            'annees' => AnneeAcademique::options(),
        ];
    }

    public function functionShowCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function functionShowEditModal(int $id): void
    {
        $demande = DemandeDocument::findOrFail($id);

        $this->demande_id = $demande->id;
        $this->titre = $demande->titre;
        $this->description = (string) $demande->description;
        $this->date_limite = $demande->date_limite?->toDateString() ?? '';
        $this->rechargerCascade(...array_values($demande->chaineCascade()));
        $this->obligatoire = $demande->obligatoire;
        $this->plusieurs_fichiers = $demande->plusieurs_fichiers;
        $this->formats = (string) $demande->formats;
        $this->formErrors = [];
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->formErrors = [];

        $valide = $this->validate([
            'titre' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'cycle_id' => 'nullable|exists:cycles,id',
            'departement_id' => 'nullable|exists:departements,id',
            'filiere_id' => 'nullable|exists:filieres,id',
            'specialite_id' => 'nullable|exists:specialites,id',
            'date_limite' => 'nullable|date',
            'formats' => 'nullable|string|max:120',
        ], [], ['titre' => 'intitulé']);

        // Rien de choisi : la demande s'adresse a tout le personnel.
        $valeurs = [
            'titre' => $valide['titre'],
            'description' => $valide['description'] ?: null,
            'cible_type' => $this->niveauCascade() ?? 'tous',
            'cible_id' => $this->cibleCascade(),
            'annee_academique' => $this->annee,
            'date_limite' => $valide['date_limite'] ?: null,
            'obligatoire' => $this->obligatoire,
            'plusieurs_fichiers' => $this->plusieurs_fichiers,
            'formats' => $valide['formats'] ?: null,
        ];

        if ($this->demande_id) {
            DemandeDocument::whereKey($this->demande_id)->update($valeurs);
            $this->notifier('Demande modifiée.');
        } else {
            DemandeDocument::create($valeurs + ['statut' => 'ouverte', 'cree_par' => auth()->id()]);
            $this->notifier('Demande ouverte : les agents concernés la voient dans leur espace.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function basculerStatut(int $id): void
    {
        $demande = DemandeDocument::findOrFail($id);
        $demande->update(['statut' => $demande->estOuverte() ? 'fermee' : 'ouverte']);

        $this->notifier($demande->estOuverte()
            ? 'Demande rouverte : les dépôts sont de nouveau acceptés.'
            : 'Demande fermée : plus aucun dépôt possible.');
    }

    public function functionShowDeleteModal(int $id): void
    {
        $this->demandeElement = DemandeDocument::withCount('depots')->findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function supprimer(): void
    {
        $this->demandeElement?->delete();
        $this->demandeElement = null;
        $this->showDeleteModal = false;
        $this->notifier('Demande supprimée.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->demandeElement = null;
        $this->formErrors = [];
    }

    private function resetForm(): void
    {
        $this->demande_id = null;
        $this->titre = '';
        $this->description = '';
        $this->date_limite = '';
        $this->viderCascade();
        $this->obligatoire = true;
        $this->plusieurs_fichiers = false;
        $this->formats = '';
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
            <h1 class="text-3xl font-bold text-gray-800">Documents demandés</h1>
            <p class="text-gray-500">
                Ce que l'administration attend du personnel. Chaque agent concerné le voit dans son espace et y dépose ses pièces.
            </p>
        </div>
        <button wire:click="functionShowCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
            + Nouvelle demande
        </button>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow p-5 mb-6 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700">Année académique</label>
            <select wire:model.live="annee" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($annees as $an)<option value="{{ $an }}" @selected($an === $annee)>{{ $an }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">État</label>
            <select wire:model.live="filtreStatut" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Toutes</option>
                <option value="ouverte">Ouvertes</option>
                <option value="fermee">Fermées</option>
            </select>
        </div>
    </div>

    <!-- Modal : demande -->
    @if ($showFormModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    {{ $demande_id ? 'Modifier la demande' : 'Nouvelle demande' }}
                </h2>

                @if (!empty($formErrors['general']))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ $formErrors['general'] }}</div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Intitulé *</label>
                        <input wire:model="titre" type="text" required maxlength="150" placeholder="Syllabus du semestre 1"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('titre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ce qui est attendu</label>
                        <textarea wire:model="description" rows="3" maxlength="2000"
                                  placeholder="Préciser le contenu, le modèle à suivre, le nombre de pages…"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">À qui on le demande</p>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cycle</label>
                                <select wire:model.live="cycle_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Tout le personnel</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Département</label>
                                <select wire:model.live="departement_id" @disabled(! $cycle_id)
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                    <option value="">{{ $cycle_id ? '— Choisir —' : '—' }}</option>
                                    @foreach ($departements as $departement)
                                        <option value="{{ $departement->id }}">{{ $departement->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filière</label>
                                <select wire:model.live="filiere_id" @disabled(! $departement_id)
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                    <option value="">{{ $departement_id ? 'Tout le département' : '—' }}</option>
                                    @foreach ($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Spécialité</label>
                                <select wire:model.live="specialite_id" @disabled(! $filiere_id)
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                    <option value="">{{ $filiere_id ? 'Toute la filière' : '—' }}</option>
                                    @foreach ($specialites as $specialite)
                                        <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500">
                            @if ($niveauRetenu = $this->niveauCascade())
                                Adressée à <span class="font-medium text-indigo-700">{{ ['departement' => 'tout le département', 'filiere' => 'toute la filière', 'specialite' => 'la spécialité'][$niveauRetenu] }}</span>
                                — y compris ceux qui n'enseignent que dans l'une de ses spécialités.
                            @else
                                Sans rien choisir, la demande s'adresse à <span class="font-medium">tout le personnel</span>.
                            @endif
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">À rendre avant le</label>
                            <input wire:model="date_limite" type="date"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Formats acceptés</label>
                            <input wire:model="formats" type="text" maxlength="120" placeholder="pdf, docx"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Vide : {{ implode(', ', config('documents.formats')) }}.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-3 text-sm text-gray-700">
                            <input wire:model="obligatoire" type="checkbox" class="w-4 h-4 rounded border border-gray-400 text-indigo-600 focus:ring-indigo-500">
                            Document obligatoire
                        </label>
                        <label class="flex items-center gap-3 text-sm text-gray-700">
                            <input wire:model="plusieurs_fichiers" type="checkbox" class="w-4 h-4 rounded border border-gray-400 text-indigo-600 focus:ring-indigo-500">
                            Autoriser plusieurs fichiers par agent
                        </label>
                    </div>

                    <div class="flex justify-end gap-4 pt-2">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            {{ $demande_id ? 'Enregistrer' : 'Ouvrir la demande' }}
                        </button>
                        <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal : suppression -->
    @if ($showDeleteModal && $demandeElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-red-600 mb-4">Supprimer la demande</h3>
                <p class="mb-6">
                    « {{ $demandeElement->titre }} »
                    @if ($demandeElement->depots_count > 0)
                        — <strong>{{ $demandeElement->depots_count }} dépôt(s)</strong> et leurs fichiers partiront avec elle.
                    @endif
                    C'est définitif.
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
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Demandes — {{ $annee }}</h2>

        @if ($demandes->isEmpty())
            <p class="text-center text-gray-500 py-8">Aucune demande pour cette sélection.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-4 text-sm font-medium text-gray-600">Document</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Destinataires</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Échéance</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Rendus</th>
                            <th class="p-4 text-sm font-medium text-gray-600">État</th>
                            <th class="p-4 text-sm font-medium text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($demandes as $demande)
                            @php($attendu = $attendus[$demande->id] ?? 0)
                            @php($rendu = $rendus[$demande->id] ?? 0)
                            <tr class="bg-gray-50 hover:bg-gray-100 transition duration-200">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">{{ $demande->titre }}</div>
                                    @if (! $demande->obligatoire)
                                        <span class="text-xs text-gray-500">Facultatif</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-gray-600">{{ $demande->cible() }}</td>
                                <td class="p-4 text-sm">
                                    @if ($demande->date_limite)
                                        <span class="{{ $demande->enRetard() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                            {{ $demande->date_limite->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm">
                                    <span class="font-medium {{ $attendu > 0 && $rendu >= $attendu ? 'text-emerald-600' : 'text-gray-700' }}">
                                        {{ $rendu }} / {{ $attendu }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if ($demande->estOuverte())
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ouverte</span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-700">Fermée</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="/admin/document/suivi?demande={{ $demande->id }}"
                                           class="bouton-icone text-sky-600 hover:bg-sky-50" title="Suivi" aria-label="Suivi">
                                            <x-icone-action nom="voir" />
                                        </a>
                                        <button wire:click="functionShowEditModal({{ $demande->id }})"
                                                class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier">
                                            <x-icone-action nom="modifier" />
                                        </button>
                                        @if ($demande->estOuverte())
                                            <button wire:click="basculerStatut({{ $demande->id }})"
                                                    class="bouton-icone text-amber-600 hover:bg-amber-50" title="Fermer" aria-label="Fermer">
                                                <x-icone-action nom="desactiver" />
                                            </button>
                                        @else
                                            <button wire:click="basculerStatut({{ $demande->id }})"
                                                    class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Rouvrir" aria-label="Rouvrir">
                                                <x-icone-action nom="activer" />
                                            </button>
                                        @endif
                                        <button wire:click="functionShowDeleteModal({{ $demande->id }})"
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
