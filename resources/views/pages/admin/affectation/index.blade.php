<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Affectation;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\User;
use App\Services\EffectifPortail;
use App\Support\AnneeAcademique;

name('admin.affectations');
middleware(['auth', 'verified', 'role']);

/**
 * Affectation du personnel.
 *
 * L'administrateur dit ici de quoi chaque agent a la charge : un departement,
 * une filiere, une specialite. C'est cette liste qui decide ensuite de
 * l'emploi du temps qu'il voit et des documents qu'on peut lui demander.
 *
 * Les comptes viennent du portail (App\Services\EffectifPortail) : on ne cree
 * personne ici, on repartit.
 */
new class extends Component {
    public string $annee = '';
    public string $recherche = '';
    public string $filtreRole = '';

    // Formulaire d'affectation
    public ?int $agent_id = null;
    public string $niveau = 'specialite';
    public ?int $cible_id = null;
    public string $fonction = '';

    public bool $showAffecterModal = false;
    public bool $showRetirerModal = false;
    public ?Affectation $affectationElement = null;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';
    public array $formErrors = [];

    public function mount(): void
    {
        $this->annee = AnneeAcademique::courante();
    }

    /** Les agents du personnel, avec leurs charges de l'annee. */
    public function with(): array
    {
        $agents = User::query()
            ->whereNotIn('role', ['student', 'etudiant'])
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'failed'))
            ->when($this->recherche !== '', function ($q) {
                $terme = '%'.$this->recherche.'%';
                $q->where(fn ($q) => $q->where('name', 'like', $terme)
                    ->orWhere('lastname', 'like', $terme)
                    ->orWhere('matricule', 'like', $terme)
                    ->orWhere('email', 'like', $terme));
            })
            ->when($this->filtreRole !== '', fn ($q) => $q->whereJsonContains('roles', $this->filtreRole))
            ->with(['affectations' => fn ($q) => $q->where('annee_academique', $this->annee)
                ->with(['departement', 'filiere', 'specialite'])])
            ->orderBy('name')->orderBy('lastname')
            ->get();

        return [
            'agents' => $agents,
            // La liste du formulaire ne suit pas les filtres du tableau :
            // on affecte souvent quelqu'un qu'on ne cherchait pas.
            'tousLesAgents' => User::whereNotIn('role', ['student', 'etudiant'])
                ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'failed'))
                ->orderBy('name')->orderBy('lastname')
                ->get(['id', 'name', 'lastname', 'matricule']),
            'annees' => AnneeAcademique::options(),
            'roles' => collect(config('roles', []))
                ->map(fn ($role, $code) => ['code' => $code, 'libelle' => $role['libelle'] ?? $code])
                ->values()->all(),
            'cibles' => $this->cibles(),
            'effectifRecuLe' => EffectifPortail::recuLe(),
            'sansAffectation' => $agents->filter(fn (User $a) => $a->affectations->isEmpty())->count(),
        ];
    }

    /** Ce que l'on peut confier, au niveau choisi. */
    public function cibles(): array
    {
        return match ($this->niveau) {
            'departement' => Departement::where('status', '!=', 'failed')
                ->orderBy('nom')->get()
                ->map(fn ($d) => ['id' => $d->id, 'libelle' => $d->nom])->all(),
            'filiere' => Filiere::with('departement')->where('status', '!=', 'failed')
                ->orderBy('name')->get()
                ->map(fn ($f) => ['id' => $f->id, 'libelle' => $f->name
                    .($f->departement ? ' — '.$f->departement->nom : '')])->all(),
            default => Specialite::with('filiere')->where('status', '!=', 'failed')
                ->orderBy('name')->get()
                ->map(fn ($s) => ['id' => $s->id, 'libelle' => $s->name
                    .($s->filiere ? ' — '.$s->filiere->name : '')])->all(),
        };
    }

    /** Changer de niveau vide la cible : elle ne veut plus rien dire. */
    public function updatedNiveau(): void
    {
        $this->cible_id = null;
    }

    public function functionShowAffecterModal(?int $agentId = null): void
    {
        $this->agent_id = $agentId;
        $this->niveau = 'specialite';
        $this->cible_id = null;
        $this->fonction = '';
        $this->formErrors = [];
        $this->showAffecterModal = true;
    }

    public function affecter(): void
    {
        $this->formErrors = [];

        $valide = $this->validate([
            'agent_id' => 'required|exists:users,id',
            'niveau' => 'required|in:departement,filiere,specialite',
            'cible_id' => 'required|integer',
            'fonction' => 'nullable|string|max:120',
            'annee' => 'required|string|max:20',
        ], [], [
            'agent_id' => 'agent',
            'cible_id' => 'charge',
        ]);

        $chaine = Affectation::chaine($valide['niveau'], (int) $valide['cible_id']);

        if (blank($chaine[$valide['niveau'].'_id'])) {
            $this->formErrors['general'] = "Cette charge n'existe plus. Rechargez la page.";

            return;
        }

        $existe = Affectation::where('user_id', $valide['agent_id'])
            ->where('annee_academique', $this->annee)
            ->where('niveau', $chaine['niveau'])
            ->where('departement_id', $chaine['departement_id'])
            ->where('filiere_id', $chaine['filiere_id'])
            ->where('specialite_id', $chaine['specialite_id'])
            ->exists();

        if ($existe) {
            $this->formErrors['general'] = 'Cet agent a déjà cette charge pour '.$this->annee.'.';

            return;
        }

        Affectation::create($chaine + [
            'user_id' => $valide['agent_id'],
            'annee_academique' => $this->annee,
            'fonction' => $this->fonction ?: null,
        ]);

        $this->showAffecterModal = false;
        $this->notifier('Affectation enregistrée.');
    }

    public function functionShowRetirerModal(int $id): void
    {
        $this->affectationElement = Affectation::with(['user', 'departement', 'filiere', 'specialite'])->findOrFail($id);
        $this->showRetirerModal = true;
    }

    public function retirer(): void
    {
        $this->affectationElement?->delete();
        $this->affectationElement = null;
        $this->showRetirerModal = false;
        $this->notifier('Affectation retirée.');
    }

    /**
     * Reprend les affectations de l'annee precedente. La rentree ne
     * rebat pas toutes les cartes : on repart de l'existant, on corrige.
     */
    public function reconduire(): void
    {
        $debut = (int) strtok($this->annee, '/');
        $precedente = ($debut - 1).'/'.$debut;

        $anciennes = Affectation::where('annee_academique', $precedente)->get();
        $reprises = 0;

        foreach ($anciennes as $ancienne) {
            $existe = Affectation::where('user_id', $ancienne->user_id)
                ->where('annee_academique', $this->annee)
                ->where('niveau', $ancienne->niveau)
                ->where('departement_id', $ancienne->departement_id)
                ->where('filiere_id', $ancienne->filiere_id)
                ->where('specialite_id', $ancienne->specialite_id)
                ->exists();

            if ($existe) {
                continue;
            }

            Affectation::create($ancienne->only([
                'user_id', 'niveau', 'departement_id', 'filiere_id', 'specialite_id', 'fonction',
            ]) + ['annee_academique' => $this->annee]);
            $reprises++;
        }

        $this->notifier($reprises === 0
            ? "Rien à reprendre de $precedente."
            : "$reprises affectation(s) reprises de $precedente.");
    }

    public function closeModal(): void
    {
        $this->showAffecterModal = false;
        $this->showRetirerModal = false;
        $this->affectationElement = null;
        $this->formErrors = [];
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Affectations du personnel</h1>
            <p class="text-gray-500">
                De quoi chaque agent a la charge : département, filière ou spécialité.
                C'est ce qui décide de son emploi du temps et des documents qu'on lui demande.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="reconduire"
                    wire:confirm="Reprendre les affectations de l'année précédente pour {{ $annee }} ?"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Reprendre l'année précédente
            </button>
            <button wire:click="functionShowAffecterModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                + Nouvelle affectation
            </button>
        </div>
    </div>

    {{-- Les comptes viennent du portail : on le rappelle, pour qu'on ne les cherche pas ici. --}}
    <div class="mb-6 p-4 bg-sky-50 border-l-4 border-sky-400 text-sky-900 rounded-lg text-sm">
        Les comptes sont créés et retirés au <strong>portail La Majestueuse</strong>, jamais ici.
        @if ($effectifRecuLe)
            Dernière liste reçue le {{ \Carbon\Carbon::parse($effectifRecuLe)->translatedFormat('d/m/Y à H:i') }}.
        @else
            Aucune liste reçue pour l'instant — lancez <code class="px-1 bg-white rounded">php artisan portail:effectif</code>.
        @endif
        @if ($sansAffectation > 0)
            <span class="block mt-1 text-sky-800">{{ $sansAffectation }} agent(s) sans aucune charge pour {{ $annee }}.</span>
        @endif
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow p-5 mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Année académique</label>
            <select wire:model.live="annee" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($annees as $an)
                    <option value="{{ $an }}">{{ $an }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Rôle</label>
            <select wire:model.live="filtreRole" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les rôles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role['code'] }}">{{ $role['libelle'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Rechercher</label>
            <input wire:model.live.debounce.400ms="recherche" type="search" placeholder="Nom, matricule, adresse…"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    @if (!empty($formErrors['general']) && !$showAffecterModal)
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">{{ $formErrors['general'] }}</div>
    @endif

    <!-- Modal : nouvelle affectation -->
    @if ($showAffecterModal)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <h2 class="text-2xl font-semibold text-gray-800 mb-1">Affecter un agent</h2>
                <p class="text-sm text-gray-500 mb-6">Année académique {{ $annee }}.</p>

                @if (!empty($formErrors))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($formErrors as $field => $messages)
                                @foreach ((array) $messages as $msg)<li>{{ $msg }}</li>@endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="affecter" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Agent *</label>
                        <select wire:model="agent_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Choisir —</option>
                            @foreach ($tousLesAgents as $agent)
                                <option value="{{ $agent->id }}">
                                    {{ trim($agent->name.' '.$agent->lastname) }}{{ $agent->matricule ? ' ('.$agent->matricule.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('agent_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Niveau de la charge *</label>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            @foreach (['departement' => 'Département', 'filiere' => 'Filière', 'specialite' => 'Spécialité'] as $valeur => $libelle)
                                <label class="flex items-center justify-center gap-2 px-3 py-2 border rounded-lg cursor-pointer text-sm
                                        {{ $niveau === $valeur ? 'border-indigo-600 bg-indigo-50 text-indigo-700 font-medium' : 'border-gray-300 text-gray-600' }}">
                                    <input type="radio" wire:model.live="niveau" value="{{ $valeur }}" class="sr-only">
                                    {{ $libelle }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Une charge de filière emporte ses spécialités ; une charge de département emporte tout ce qu'il contient.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ ['departement' => 'Département', 'filiere' => 'Filière', 'specialite' => 'Spécialité'][$niveau] }} *
                        </label>
                        <select wire:model="cible_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Choisir —</option>
                            @foreach ($cibles as $cible)
                                <option value="{{ $cible['id'] }}">{{ $cible['libelle'] }}</option>
                            @endforeach
                        </select>
                        @if (empty($cibles))
                            <p class="mt-1 text-sm text-amber-700">Rien à ce niveau pour le moment.</p>
                        @endif
                        @error('cible_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fonction <span class="text-gray-400">(facultatif)</span></label>
                        <input wire:model="fonction" type="text" maxlength="120" placeholder="Enseignant, Chef de département…"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex justify-end gap-4 pt-2">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Affecter</button>
                        <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal : retrait -->
    @if ($showRetirerModal && $affectationElement)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-red-600 mb-4">Retirer l'affectation</h3>
                <p class="mb-6">
                    Retirer à <strong>{{ trim($affectationElement->user?->name.' '.$affectationElement->user?->lastname) }}</strong>
                    la charge de <strong>{{ $affectationElement->intitule() }}</strong> pour {{ $affectationElement->annee_academique }} ?
                </p>
                <div class="flex justify-end gap-4">
                    <button wire:click="retirer" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Retirer</button>
                    <button wire:click="closeModal" class="px-5 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Liste -->
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Personnel — {{ $annee }}</h2>

        @if ($agents->isEmpty())
            <p class="text-center text-gray-500 py-8">Aucun agent ne correspond à cette recherche.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-4 text-sm font-medium text-gray-600">Agent</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Rôles</th>
                            <th class="p-4 text-sm font-medium text-gray-600">Charges de l'année</th>
                            <th class="p-4 text-sm font-medium text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                            <tr class="bg-gray-50 hover:bg-gray-100 transition duration-200 align-top">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">{{ trim($agent->name.' '.$agent->lastname) }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $agent->matricule ?: '—' }}
                                        @unless ($agent->peut_se_connecter)
                                            <span class="ml-1 inline-block px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">connexion fermée</span>
                                        @endunless
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-gray-600">{{ $agent->libellesRoles() }}</td>
                                <td class="p-4">
                                    @if ($agent->affectations->isEmpty())
                                        <span class="text-sm text-gray-400">Aucune</span>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($agent->affectations as $affectation)
                                                <span class="inline-flex items-center gap-2 pl-3 pr-1 py-1 rounded-full text-xs font-medium
                                                    @class([
                                                        'bg-violet-100 text-violet-800' => $affectation->niveau === 'departement',
                                                        'bg-blue-100 text-blue-800' => $affectation->niveau === 'filiere',
                                                        'bg-emerald-100 text-emerald-800' => $affectation->niveau === 'specialite',
                                                    ])">
                                                    <span title="{{ $affectation->chemin() }}">
                                                        {{ $affectation->intitule() }}@if ($affectation->fonction) · {{ $affectation->fonction }}@endif
                                                    </span>
                                                    <button wire:click="functionShowRetirerModal({{ $affectation->id }})"
                                                            class="w-5 h-5 rounded-full hover:bg-white/70 leading-none"
                                                            title="Retirer" aria-label="Retirer cette charge">×</button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <button wire:click="functionShowAffecterModal({{ $agent->id }})"
                                            class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Affecter" aria-label="Affecter">
                                        <x-icone-action nom="affecter" />
                                    </button>
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
