<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\DemandeDocument;
use App\Models\DepotDocument;
use App\Models\FichierDocument;
use App\Support\AnneeAcademique;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

name('personnel.documents');
middleware(['auth', 'verified', 'role']);

/**
 * « Mes documents » : ce que l'administration attend de l'agent connecte.
 *
 * Il ne voit que les demandes qui le concernent — celles adressees a un
 * departement, une filiere ou une specialite dont il a la charge, et celles
 * adressees a tout le monde.
 */
new class extends Component {
    use WithFileUploads;

    /** Pieces en cours de televersement, par demande. */
    public array $fichiers = [];

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';
    public array $erreurs = [];

    public function with(): array
    {
        $user = auth()->user();
        $annee = AnneeAcademique::courante();

        $demandes = DemandeDocument::where('annee_academique', $annee)
            ->orderByRaw("CASE WHEN statut = 'ouverte' THEN 0 ELSE 1 END")
            ->orderByRaw('date_limite IS NULL, date_limite')
            ->get()
            ->filter(fn (DemandeDocument $d) => $d->concerne($user))
            ->values();

        $depots = DepotDocument::with('fichiers')
            ->where('user_id', $user->id)
            ->whereIn('demande_document_id', $demandes->pluck('id'))
            ->get()
            ->keyBy('demande_document_id');

        return [
            'demandes' => $demandes,
            'depots' => $depots,
            'annee' => $annee,
            'aRendre' => $demandes->filter(fn ($d) => $d->estOuverte()
                && ! ($depots->get($d->id)?->depose_le))->count(),
        ];
    }

    public function deposer(int $demandeId): void
    {
        $this->erreurs = [];
        $user = auth()->user();
        $demande = DemandeDocument::find($demandeId);

        if (! $demande || ! $demande->concerne($user)) {
            return;
        }

        if (! $demande->estOuverte()) {
            $this->notifier('Cette demande est fermée : les dépôts ne sont plus acceptés.', 'erreur');

            return;
        }

        $piece = $this->fichiers[$demandeId] ?? null;

        if (! $piece) {
            $this->erreurs[$demandeId] = 'Choisissez un fichier.';

            return;
        }

        $formats = $demande->formatsAcceptes();

        $this->validate([
            "fichiers.$demandeId" => [
                'required', 'file',
                'mimes:'.implode(',', $formats),
                'max:'.config('documents.taille_max'),
            ],
        ], [
            "fichiers.$demandeId.mimes" => 'Formats acceptés : '.implode(', ', $formats).'.',
            "fichiers.$demandeId.max" => 'Le fichier dépasse '.round(config('documents.taille_max') / 1024).' Mo.',
            "fichiers.$demandeId.required" => 'Choisissez un fichier.',
        ]);

        $depot = DepotDocument::firstOrCreate(
            ['demande_document_id' => $demande->id, 'user_id' => $user->id],
        );

        // Un seul fichier attendu : le nouveau remplace l'ancien, sinon on
        // ne saurait pas lequel fait foi.
        if (! $demande->plusieurs_fichiers) {
            foreach ($depot->fichiers as $ancien) {
                Storage::disk('local')->delete($ancien->chemin);
                $ancien->delete();
            }
        }

        $nomOriginal = $piece->getClientOriginalName();
        $nomRange = Str::uuid().'.'.$piece->getClientOriginalExtension();
        $chemin = $piece->storeAs(
            config('documents.dossier').'/'.$demande->id.'/'.$user->id,
            $nomRange,
            'local'
        );

        FichierDocument::create([
            'depot_document_id' => $depot->id,
            'chemin' => $chemin,
            'nom_original' => Str::limit($nomOriginal, 180, ''),
            'taille' => $piece->getSize(),
            'mime' => $piece->getMimeType(),
        ]);

        /*
         * Un redepot efface la decision precedente : le document n'est plus
         * celui qui avait ete refuse, il doit etre regarde de nouveau.
         */
        $depot->update([
            'depose_le' => now(),
            'statut' => 'en_attente',
            'motif' => null,
            'tranche_par' => null,
            'tranche_le' => null,
        ]);

        unset($this->fichiers[$demandeId]);
        $this->notifier('Document déposé. L\'administration le validera.');
    }

    public function retirer(int $fichierId): void
    {
        $fichier = FichierDocument::with('depot')->find($fichierId);

        if (! $fichier || $fichier->depot?->user_id !== auth()->id()) {
            return;
        }

        // Un document accepte ne se retire plus : il fait foi.
        if ($fichier->depot->estAccepte()) {
            $this->notifier('Ce document a été accepté : il ne peut plus être retiré.', 'erreur');

            return;
        }

        Storage::disk('local')->delete($fichier->chemin);
        $depot = $fichier->depot;
        $fichier->delete();

        if ($depot->fichiers()->count() === 0) {
            $depot->update(['depose_le' => null, 'statut' => 'en_attente', 'motif' => null]);
        }

        $this->notifier('Pièce retirée.');
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

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Mes documents</h1>
        <p class="text-gray-500">
            Ce que l'administration attend de vous pour l'année {{ $annee }}.
            @if ($aRendre > 0)
                <span class="font-medium text-amber-700">{{ $aRendre }} document(s) restent à déposer.</span>
            @endif
        </p>
    </div>

    @if ($demandes->isEmpty())
        <div class="bg-white rounded-2xl shadow-lg p-10 text-center">
            <p class="text-gray-600">Aucun document ne vous est demandé pour le moment.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($demandes as $demande)
                @php($depot = $depots->get($demande->id))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">{{ $demande->titre }}</h2>
                            <p class="text-sm text-gray-500">
                                {{ $demande->cible() }}
                                @if (! $demande->obligatoire) · facultatif @endif
                                @if ($demande->date_limite)
                                    · à rendre avant le
                                    <span class="{{ $demande->enRetard() && ! $depot?->depose_le ? 'text-red-600 font-medium' : '' }}">
                                        {{ $demande->date_limite->format('d/m/Y') }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0">
                            @if (! $depot?->depose_le)
                                @if ($demande->estOuverte())
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">À déposer</span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-700">Fermée — non déposé</span>
                                @endif
                            @elseif ($depot->estAccepte())
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Accepté</span>
                            @elseif ($depot->estRefuse())
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Refusé</span>
                            @else
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-800">En attente de validation</span>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        @if ($demande->description)
                            <p class="text-sm text-gray-600">{{ $demande->description }}</p>
                        @endif

                        @if ($depot?->estRefuse() && $depot->motif)
                            <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded text-sm text-red-800">
                                <strong>Motif du refus :</strong> {{ $depot->motif }}
                                <div class="mt-1 text-red-700">Corrigez le document et déposez-le de nouveau.</div>
                            </div>
                        @endif

                        {{-- Pièces déjà déposées --}}
                        @if ($depot && $depot->fichiers->isNotEmpty())
                            <ul class="divide-y divide-gray-100 border border-gray-100 rounded-xl">
                                @foreach ($depot->fichiers as $fichier)
                                    <li class="px-4 py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="/documents/fichiers/{{ $fichier->id }}"
                                               class="text-sm text-indigo-600 hover:underline break-all">{{ $fichier->nom_original }}</a>
                                            <span class="block text-xs text-gray-400">
                                                {{ $fichier->poids() }} · déposé le {{ $fichier->created_at->format('d/m/Y à H:i') }}
                                            </span>
                                        </div>
                                        @unless ($depot->estAccepte())
                                            <button wire:click="retirer({{ $fichier->id }})"
                                                    wire:confirm="Retirer cette pièce ?"
                                                    class="text-sm text-red-600 hover:underline shrink-0">Retirer</button>
                                        @endunless
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Dépôt --}}
                        @if ($demande->estOuverte() && ! $depot?->estAccepte())
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    @if ($depot?->depose_le && ! $demande->plusieurs_fichiers)
                                        Remplacer le document
                                    @elseif ($demande->plusieurs_fichiers)
                                        Ajouter une pièce
                                    @else
                                        Déposer le document
                                    @endif
                                </label>
                                <div class="mt-2 flex flex-col sm:flex-row gap-3">
                                    <input type="file" wire:model="fichiers.{{ $demande->id }}"
                                           class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg
                                                  file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                                  hover:file:bg-indigo-100">
                                    <button wire:click="deposer({{ $demande->id }})"
                                            wire:loading.attr="disabled"
                                            class="shrink-0 px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="deposer({{ $demande->id }})">Déposer</span>
                                        <span wire:loading wire:target="deposer({{ $demande->id }})">Envoi…</span>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Formats : {{ implode(', ', $demande->formatsAcceptes()) }} ·
                                    {{ round(config('documents.taille_max') / 1024) }} Mo maximum
                                    @unless ($demande->plusieurs_fichiers) · un seul fichier @endunless
                                </p>
                                @if (!empty($erreurs[$demande->id]))
                                    <p class="mt-1 text-sm text-red-600">{{ $erreurs[$demande->id] }}</p>
                                @endif
                                @error('fichiers.'.$demande->id) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
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
