<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use App\Mail\notifMail;
use App\Models\DemandeDocument;
use App\Models\DepotDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

name('admin.documents.suivi');
middleware(['auth', 'verified', 'role']);

/**
 * Suivi d'une demande : qui a rendu, qui n'a pas rendu, et ce qu'on en fait.
 *
 * Les agents concernes sont calcules a partir des affectations de l'annee —
 * ils n'ont rien a faire pour apparaitre ici, et un enseignant affecte apres
 * l'ouverture de la demande s'y ajoute tout seul.
 */
new class extends Component {
    #[Url]
    public ?int $demande = null;

    public string $filtre = '';

    public bool $showDecisionModal = false;
    public ?int $depot_id = null;
    public string $decision = 'accepte';
    public string $motif = '';

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';

    public function with(): array
    {
        $demande = $this->demandeCourante();

        if (! $demande) {
            return ['demandeDocument' => null, 'lignes' => collect(), 'compte' => []];
        }

        $depots = DepotDocument::with(['fichiers', 'arbitre'])
            ->where('demande_document_id', $demande->id)
            ->get()
            ->keyBy('user_id');

        $lignes = $demande->concernes()->map(fn ($agent) => [
            'agent' => $agent,
            'depot' => $depots->get($agent->id),
        ]);

        $compte = [
            'attendus' => $lignes->count(),
            'deposes' => $lignes->filter(fn ($l) => $l['depot']?->depose_le)->count(),
            'acceptes' => $lignes->filter(fn ($l) => $l['depot']?->estAccepte())->count(),
            'refuses' => $lignes->filter(fn ($l) => $l['depot']?->estRefuse())->count(),
        ];
        $compte['manquants'] = $compte['attendus'] - $compte['deposes'];

        if ($this->filtre !== '') {
            $lignes = $lignes->filter(function ($ligne) {
                $depot = $ligne['depot'];

                return match ($this->filtre) {
                    'manquants' => ! $depot?->depose_le,
                    'en_attente' => $depot?->depose_le && $depot->statut === 'en_attente',
                    'acceptes' => (bool) $depot?->estAccepte(),
                    'refuses' => (bool) $depot?->estRefuse(),
                    default => true,
                };
            });
        }

        return [
            'demandeDocument' => $demande,
            'lignes' => $lignes->values(),
            'compte' => $compte,
        ];
    }

    private function demandeCourante(): ?DemandeDocument
    {
        return $this->demande ? DemandeDocument::find($this->demande) : null;
    }

    public function ouvrirDecision(int $depotId, string $decision): void
    {
        $this->depot_id = $depotId;
        $this->decision = $decision;
        $this->motif = '';
        $this->showDecisionModal = true;
    }

    public function trancher(): void
    {
        $depot = DepotDocument::find($this->depot_id);

        if (! $depot) {
            return;
        }

        // Un refus sans motif oblige l'enseignant a deviner : on l'exige.
        if ($this->decision === 'refuse' && trim($this->motif) === '') {
            $this->notifier("Indiquez pourquoi le document est refusé.", 'erreur');

            return;
        }

        $depot->update([
            'statut' => $this->decision,
            'motif' => $this->decision === 'refuse' ? trim($this->motif) : null,
            'tranche_par' => auth()->id(),
            'tranche_le' => now(),
        ]);

        $this->showDecisionModal = false;
        $this->notifier($this->decision === 'accepte' ? 'Document accepté.' : 'Document refusé, le motif lui est transmis.');
    }

    /** Relance les agents qui n'ont rien rendu. */
    public function relancer(): void
    {
        $demande = $this->demandeCourante();

        if (! $demande) {
            return;
        }

        $depots = DepotDocument::where('demande_document_id', $demande->id)->get()->keyBy('user_id');
        $partis = 0;
        $sansAdresse = 0;

        foreach ($demande->concernes() as $agent) {
            $depot = $depots->get($agent->id);

            if ($depot?->depose_le) {
                continue;
            }

            // Les comptes sans adresse professionnelle portent une adresse
            // derivee : rien n'y arriverait.
            if (blank($agent->email) || str_ends_with((string) $agent->email, '@portail.local')) {
                $sansAdresse++;

                continue;
            }

            $echeance = $demande->date_limite
                ? ' avant le '.$demande->date_limite->format('d/m/Y')
                : '';

            try {
                Mail::to($agent->email)->send(new notifMail(
                    'Document attendu : '.$demande->titre,
                    'Bonjour '.trim($agent->name.' '.$agent->lastname).", le document « ".$demande->titre
                    ."» est attendu".$echeance.". Déposez-le depuis votre espace, rubrique « Mes documents »."
                ));
                $partis++;
            } catch (\Throwable $e) {
                Log::warning('Relance document : envoi impossible.', [
                    'agent' => $agent->id, 'raison' => $e->getMessage(),
                ]);
            }

            DepotDocument::updateOrCreate(
                ['demande_document_id' => $demande->id, 'user_id' => $agent->id],
                ['relance_le' => now()],
            );
        }

        $this->notifier($partis === 0
            ? ($sansAdresse > 0 ? "Personne n'a d'adresse pour être relancé ($sansAdresse sans adresse)." : 'Personne à relancer.')
            : "$partis relance(s) envoyée(s)".($sansAdresse > 0 ? ", $sansAdresse agent(s) sans adresse." : '.'));
    }

    public function closeModal(): void
    {
        $this->showDecisionModal = false;
        $this->depot_id = null;
        $this->motif = '';
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

    @if (! $demandeDocument)
        <div class="max-w-xl mx-auto bg-white rounded-2xl shadow p-8 text-center">
            <p class="text-gray-600">Aucune demande sélectionnée.</p>
            <a href="/admin/document" wire:navigate class="mt-4 inline-block px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Revenir aux demandes
            </a>
        </div>
    @else
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
            <div>
                <a href="/admin/document" wire:navigate class="text-sm text-indigo-600 hover:underline">← Toutes les demandes</a>
                <h1 class="text-3xl font-bold text-gray-800 mt-1">{{ $demandeDocument->titre }}</h1>
                <p class="text-gray-500">
                    {{ $demandeDocument->cible() }} · {{ $demandeDocument->annee_academique }}
                    @if ($demandeDocument->date_limite)
                        · à rendre avant le
                        <span class="{{ $demandeDocument->enRetard() ? 'text-red-600 font-medium' : '' }}">
                            {{ $demandeDocument->date_limite->format('d/m/Y') }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="relancer"
                        wire:confirm="Envoyer un rappel à tous ceux qui n'ont rien déposé ?"
                        class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600">
                    Relancer les retardataires
                </button>
                <a href="/admin/document/{{ $demandeDocument->id }}/export"
                   class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                    Exporter (CSV)
                </a>
            </div>
        </div>

        @if ($demandeDocument->description)
            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700">
                {{ $demandeDocument->description }}
            </div>
        @endif

        <!-- Compteurs, cliquables pour filtrer -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
            @php($cartes = [
                '' => ['Attendus', $compte['attendus'], 'text-gray-800'],
                'manquants' => ['Non déposés', $compte['manquants'], 'text-red-600'],
                'en_attente' => ['À valider', $compte['deposes'] - $compte['acceptes'] - $compte['refuses'], 'text-amber-600'],
                'acceptes' => ['Acceptés', $compte['acceptes'], 'text-emerald-600'],
                'refuses' => ['Refusés', $compte['refuses'], 'text-gray-600'],
            ])
            @foreach ($cartes as $cle => [$libelle, $valeur, $couleur])
                <button wire:click="$set('filtre', '{{ $cle }}')"
                        class="bg-white rounded-2xl shadow-sm border p-5 text-left transition
                               {{ $filtre === $cle ? 'border-indigo-500 ring-1 ring-indigo-200' : 'border-gray-100 hover:border-gray-300' }}">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $libelle }}</p>
                    <p class="mt-1 text-3xl font-bold {{ $couleur }}">{{ $valeur }}</p>
                </button>
            @endforeach
        </div>

        <!-- Modal : décision -->
        @if ($showDecisionModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                        {{ $decision === 'accepte' ? 'Accepter le document' : 'Refuser le document' }}
                    </h2>

                    <form wire:submit="trancher" class="space-y-6">
                        @if ($decision === 'refuse')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Motif du refus *</label>
                                <textarea wire:model="motif" rows="3" required
                                          placeholder="Ce qui manque, ou ce qui doit être corrigé."
                                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <p class="mt-1 text-xs text-gray-500">L'enseignant le lira dans son espace et pourra redéposer.</p>
                            </div>
                        @else
                            <p class="text-gray-600">Le document est conforme ; l'enseignant n'a plus rien à faire.</p>
                        @endif

                        <div class="flex justify-end gap-4">
                            <button type="submit"
                                    class="px-6 py-2 text-white rounded-lg {{ $decision === 'accepte' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700' }}">
                                Confirmer
                            </button>
                            <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Tableau de suivi -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                Personnel concerné
                @if ($filtre !== '')
                    <button wire:click="$set('filtre', '')" class="ml-2 text-sm font-normal text-indigo-600 hover:underline">voir tout</button>
                @endif
            </h2>

            @if ($lignes->isEmpty())
                <p class="text-center text-gray-500 py-8">
                    Personne ne correspond. Les destinataires viennent des affectations de {{ $demandeDocument->annee_academique }}.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-4 text-sm font-medium text-gray-600">Agent</th>
                                <th class="p-4 text-sm font-medium text-gray-600">État</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Déposé le</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Pièces</th>
                                <th class="p-4 text-sm font-medium text-gray-600 text-right">Décision</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lignes as $ligne)
                                @php($agent = $ligne['agent'])
                                @php($depot = $ligne['depot'])
                                <tr class="bg-gray-50 hover:bg-gray-100 transition duration-200 align-top">
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800">{{ trim($agent->name.' '.$agent->lastname) }}</div>
                                        <div class="text-xs text-gray-500">{{ $agent->matricule ?: $agent->poste ?: '—' }}</div>
                                    </td>
                                    <td class="p-4">
                                        @if (! $depot?->depose_le)
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Non déposé</span>
                                            @if ($depot?->relance_le)
                                                <div class="text-xs text-gray-400 mt-1">Relancé le {{ $depot->relance_le->format('d/m/Y') }}</div>
                                            @endif
                                        @elseif ($depot->estAccepte())
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Accepté</span>
                                        @elseif ($depot->estRefuse())
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-700">Refusé</span>
                                            <div class="text-xs text-gray-500 mt-1">{{ $depot->motif }}</div>
                                        @else
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">À valider</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-sm text-gray-600">{{ $depot?->depose_le?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="p-4">
                                        @if ($depot && $depot->fichiers->isNotEmpty())
                                            <ul class="space-y-1">
                                                @foreach ($depot->fichiers as $fichier)
                                                    <li>
                                                        <a href="/documents/fichiers/{{ $fichier->id }}"
                                                           class="text-sm text-indigo-600 hover:underline break-all">{{ $fichier->nom_original }}</a>
                                                        <span class="text-xs text-gray-400">({{ $fichier->poids() }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($depot?->depose_le)
                                            <div class="flex justify-end gap-2">
                                                @unless ($depot->estAccepte())
                                                    <button wire:click="ouvrirDecision({{ $depot->id }}, 'accepte')"
                                                            class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Accepter" aria-label="Accepter">
                                                        <x-icone-action nom="valider" />
                                                    </button>
                                                @endunless
                                                @unless ($depot->estRefuse())
                                                    <button wire:click="ouvrirDecision({{ $depot->id }}, 'refuse')"
                                                            class="bouton-icone text-red-600 hover:bg-red-50" title="Refuser" aria-label="Refuser">
                                                        <x-icone-action nom="annuler" />
                                                    </button>
                                                @endunless
                                            </div>
                                        @else
                                            <span class="block text-right text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    @if ($showNotification)
        <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => { show = false; $wire.set('showNotification', false) }, 4000)">
            <div class="p-4 rounded-xl shadow-lg border-l-4
                        {{ $notificationType === 'erreur' ? 'bg-red-50 border-red-500 text-red-800' : 'bg-green-50 border-green-500 text-green-800' }}">
                {{ $notificationMessage }}
            </div>
        </div>
    @endif
</div>
@endvolt
</x-layouts.app>
