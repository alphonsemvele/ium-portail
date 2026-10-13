<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use App\Models\Affectation;
use App\Models\Cour;
use App\Models\EmploiDuTemps;
use App\Models\Salle;
use App\Models\Seance;
use App\Models\User;

name('admin.emplois-du-temps.grille');
middleware(['auth', 'verified', 'role']);

/**
 * La grille : jours en colonnes, creneaux en lignes, comme le document
 * officiel. On clique une case, on y pose une matiere, un enseignant, une
 * salle et le numero de la seance.
 */
new class extends Component {
    #[Url]
    public ?int $emploi = null;

    // Case en cours d'edition
    public bool $showCaseModal = false;
    public ?int $jour = null;
    public ?int $creneau = null;
    public ?int $seance_id = null;

    public string $type = 'cours';
    public ?int $cour_id = null;
    public string $intitule = '';
    public ?int $enseignant_id = null;
    public ?int $salle_id = null;
    public string $salle_libelle = '';
    public ?int $seance_numero = null;
    public ?int $seance_total = null;

    public bool $showNotification = false;
    public string $notificationMessage = '';
    public array $formErrors = [];

    public function with(): array
    {
        $emploi = $this->emploiCourant();

        return [
            'emploiDuTemps' => $emploi,
            'jours' => config('emploi_du_temps.jours'),
            'types' => config('emploi_du_temps.types'),
            'creneaux' => $emploi?->grilleCreneaux() ?? [],
            'cases' => $emploi?->cases() ?? [],
            'cours' => $emploi ? $this->coursDisponibles($emploi) : collect(),
            'enseignants' => $emploi ? $this->enseignants($emploi) : ['affectes' => collect(), 'autres' => collect()],
            'salles' => Salle::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function emploiCourant(): ?EmploiDuTemps
    {
        return $this->emploi
            ? EmploiDuTemps::with(['specialite.filiere', 'seances.cour', 'seances.enseignant', 'seances.salle'])->find($this->emploi)
            : null;
    }

    /** Les matieres de la specialite ; a defaut, celles de sa filiere. */
    private function coursDisponibles(EmploiDuTemps $emploi)
    {
        return Cour::where('status', '!=', 'failed')
            ->where(fn ($q) => $q->where('specialite_id', $emploi->specialite_id)
                ->orWhere('filiere_id', $emploi->specialite?->filiere_id))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'hour_number']);
    }

    /**
     * Les enseignants : d'abord ceux qui ont la charge de cette specialite,
     * ensuite le reste du personnel. On ne ferme pas la liste — un vacataire
     * intervient parfois avant d'avoir ete affecte.
     *
     * @return array{affectes: \Illuminate\Support\Collection, autres: \Illuminate\Support\Collection}
     */
    private function enseignants(EmploiDuTemps $emploi): array
    {
        $ids = Affectation::where('annee_academique', $emploi->annee_academique)
            ->where('specialite_id', $emploi->specialite_id)
            ->pluck('user_id')->unique();

        $personnel = User::whereNotIn('role', ['student', 'etudiant'])
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'failed'))
            ->orderBy('name')->orderBy('lastname')
            ->get(['id', 'name', 'lastname', 'matricule']);

        return [
            'affectes' => $personnel->whereIn('id', $ids)->values(),
            'autres' => $personnel->whereNotIn('id', $ids)->values(),
        ];
    }

    public function ouvrirCase(int $jour, int $creneau): void
    {
        $emploi = $this->emploiCourant();

        if (! $emploi) {
            return;
        }

        $this->jour = $jour;
        $this->creneau = $creneau;
        $this->formErrors = [];

        $seance = $emploi->seances->first(fn (Seance $s) => $s->jour === $jour && $s->creneau === $creneau);

        $this->seance_id = $seance?->id;
        $this->type = $seance->type ?? 'cours';
        $this->cour_id = $seance->cour_id ?? null;
        $this->intitule = $seance->intitule ?? '';
        $this->enseignant_id = $seance->enseignant_id ?? null;
        $this->salle_id = $seance->salle_id ?? null;
        $this->salle_libelle = $seance->salle_libelle ?? '';
        $this->seance_numero = $seance->seance_numero ?? null;
        $this->seance_total = $seance->seance_total ?? null;

        $this->showCaseModal = true;
    }

    public function enregistrerCase(): void
    {
        $this->formErrors = [];
        $emploi = $this->emploiCourant();

        if (! $emploi || $this->jour === null || $this->creneau === null) {
            return;
        }

        $this->validate([
            'type' => 'required|in:cours,tpe,activite,autre',
            'cour_id' => 'nullable|exists:cours,id',
            'intitule' => 'nullable|string|max:150',
            'enseignant_id' => 'nullable|exists:users,id',
            'salle_id' => 'nullable|exists:salles,id',
            'salle_libelle' => 'nullable|string|max:80',
            'seance_numero' => 'nullable|integer|min:1|max:999',
            'seance_total' => 'nullable|integer|min:1|max:999',
        ]);

        // Un cours sans matiere ni intitule ne dit rien a personne.
        if ($this->type === 'cours' && blank($this->cour_id) && blank($this->intitule)) {
            $this->formErrors['general'] = 'Choisissez une matière, ou donnez un intitulé.';

            return;
        }

        if ($this->seance_numero && $this->seance_total && $this->seance_numero > $this->seance_total) {
            $this->formErrors['general'] = 'Le numéro de séance dépasse le total prévu.';

            return;
        }

        $creneaux = $emploi->grilleCreneaux();
        $horaire = $creneaux[$this->creneau] ?? null;

        if (! $horaire || $horaire['pause']) {
            $this->formErrors['general'] = "Ce créneau ne reçoit pas de séance.";

            return;
        }

        $valeurs = [
            'jour' => $this->jour,
            'creneau' => $this->creneau,
            'heure_debut' => $horaire['debut'],
            'heure_fin' => $horaire['fin'],
            'type' => $this->type,
            'cour_id' => $this->type === 'cours' ? $this->cour_id : null,
            'intitule' => $this->intitule ?: null,
            'enseignant_id' => $this->enseignant_id ?: null,
            'salle_id' => $this->salle_id ?: null,
            'salle_libelle' => $this->salle_libelle ?: null,
            'seance_numero' => $this->seance_numero ?: null,
            'seance_total' => $this->seance_total ?: null,
        ];

        if ($this->seance_id) {
            Seance::whereKey($this->seance_id)->update($valeurs);
        } else {
            $emploi->seances()->create($valeurs);
        }

        $this->showCaseModal = false;
        $this->notifier('Séance enregistrée.');
    }

    public function viderCase(): void
    {
        if ($this->seance_id) {
            Seance::whereKey($this->seance_id)->delete();
            $this->notifier('Séance retirée.');
        }

        $this->showCaseModal = false;
    }

    public function basculerPublication(): void
    {
        $emploi = $this->emploiCourant();

        if (! $emploi) {
            return;
        }

        if ($emploi->estPublie()) {
            $emploi->update(['statut' => 'brouillon', 'publie_le' => null]);
            $this->notifier('Retiré des espaces enseignants.');

            return;
        }

        $emploi->update(['statut' => 'publie', 'publie_le' => now()]);
        $this->notifier('Publié : les enseignants concernés le voient.');
    }

    public function closeModal(): void
    {
        $this->showCaseModal = false;
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
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (! $emploiDuTemps)
        <div class="max-w-xl mx-auto bg-white rounded-2xl shadow p-8 text-center">
            <p class="text-gray-600">Aucun emploi du temps sélectionné.</p>
            <a href="/admin/emploi-du-temps" wire:navigate class="mt-4 inline-block px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Revenir à la liste
            </a>
        </div>
    @else
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
            <div>
                <a href="/admin/emploi-du-temps" wire:navigate class="text-sm text-indigo-600 hover:underline">← Tous les emplois du temps</a>
                <h1 class="text-3xl font-bold text-gray-800 mt-1">{{ $emploiDuTemps->specialite?->name ?? 'Spécialité supprimée' }}</h1>
                <p class="text-gray-500">
                    {{ $emploiDuTemps->niveau }} · Semestre {{ $emploiDuTemps->semestre }} ·
                    Année {{ $emploiDuTemps->annee_academique }} · {{ $emploiDuTemps->semaine() }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($emploiDuTemps->estPublie())
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Publié</span>
                    <button wire:click="basculerPublication" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Dépublier</button>
                @else
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Brouillon</span>
                    <button wire:click="basculerPublication" class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Publier</button>
                @endif
                <a href="/admin/emploi-du-temps/{{ $emploiDuTemps->id }}/pdf" target="_blank"
                   class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">PDF</a>
            </div>
        </div>

        <p class="mb-4 text-sm text-gray-500">Cliquez une case pour y poser une séance.</p>

        <!-- Grille -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse">
                <thead>
                    <tr>
                        <th class="w-28 border border-gray-300 bg-gray-100 p-2 text-xs font-semibold text-gray-600">Heures</th>
                        @foreach ($jours as $numero => $jour)
                            <th class="border border-gray-300 bg-gray-100 p-2 text-sm font-semibold text-gray-700">
                                {{ $jour['long'] }}
                                <span class="block text-xs font-normal italic text-gray-400">{{ $jour['anglais'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($creneaux as $index => $creneauLigne)
                        <tr>
                            <td class="border border-gray-300 bg-gray-50 p-2 text-center text-xs font-semibold text-gray-700 whitespace-nowrap">
                                {{ $creneauLigne['debut'] }} – {{ $creneauLigne['fin'] }}
                            </td>

                            @if ($creneauLigne['pause'])
                                <td colspan="{{ count($jours) }}" class="border border-gray-300 bg-gray-200 p-2 text-center text-sm italic font-medium text-gray-600">
                                    PAUSE
                                </td>
                            @else
                                @foreach ($jours as $numero => $jour)
                                    @php($seance = $cases[$numero][$index] ?? null)
                                    <td class="border border-gray-300 p-0 align-top">
                                        <button type="button" wire:click="ouvrirCase({{ $numero }}, {{ $index }})"
                                                class="w-full h-full min-h-[92px] p-2 text-left text-xs transition
                                                       {{ $seance ? 'bg-indigo-50/60 hover:bg-indigo-100' : 'hover:bg-gray-50' }}">
                                            @if ($seance)
                                                <span class="block font-semibold text-gray-800 leading-tight">{{ $seance->matiere() }}</span>
                                                @if ($seance->nomEnseignant())
                                                    <span class="block text-gray-600 mt-0.5">{{ $seance->nomEnseignant() }}</span>
                                                @endif
                                                @if ($seance->salleAffichee())
                                                    <span class="block text-gray-500">{{ $seance->salleAffichee() }}</span>
                                                @endif
                                                @if ($seance->progression())
                                                    <span class="block text-gray-500 font-medium">{{ $seance->progression() }}</span>
                                                @endif
                                            @else
                                                <span class="block text-gray-300 text-center pt-6 text-lg leading-none">+</span>
                                            @endif
                                        </button>
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal : contenu d'une case -->
        @if ($showCaseModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-1">
                        {{ $jours[$jour]['long'] ?? '' }} · {{ $creneaux[$creneau]['debut'] ?? '' }} – {{ $creneaux[$creneau]['fin'] ?? '' }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">{{ $emploiDuTemps->titre() }} — {{ $emploiDuTemps->semaine() }}</p>

                    @if (!empty($formErrors['general']))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">{{ $formErrors['general'] }}</div>
                    @endif

                    <form wire:submit="enregistrerCase" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nature de la séance *</label>
                            <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach ($types as $valeur => $libelle)
                                    <label class="flex items-center justify-center px-3 py-2 border rounded-lg cursor-pointer text-sm
                                            {{ $type === $valeur ? 'border-indigo-600 bg-indigo-50 text-indigo-700 font-medium' : 'border-gray-300 text-gray-600' }}">
                                        <input type="radio" wire:model.live="type" value="{{ $valeur }}" class="sr-only">
                                        {{ $libelle }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        @if ($type === 'cours')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Matière</label>
                                <select wire:model="cour_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Aucune (intitulé libre ci-dessous) —</option>
                                    @foreach ($cours as $matiere)
                                        <option value="{{ $matiere->id }}">{{ $matiere->name }}{{ $matiere->code ? ' ('.$matiere->code.')' : '' }}</option>
                                    @endforeach
                                </select>
                                @if ($cours->isEmpty())
                                    <p class="mt-1 text-sm text-amber-700">Aucune matière au catalogue pour cette spécialité : utilisez l'intitulé libre.</p>
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Intitulé libre {{ $type === 'cours' ? '(si la matière n\'est pas au catalogue)' : '(facultatif)' }}
                            </label>
                            <input wire:model="intitule" type="text" maxlength="150"
                                   placeholder="{{ $type === 'cours' ? 'Analyse Mathématique I' : ($types[$type] ?? '') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Enseignant</label>
                            <select wire:model="enseignant_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Aucun —</option>
                                @if ($enseignants['affectes']->isNotEmpty())
                                    <optgroup label="Affectés à cette spécialité">
                                        @foreach ($enseignants['affectes'] as $agent)
                                            <option value="{{ $agent->id }}">{{ trim($agent->name.' '.$agent->lastname) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                <optgroup label="Autre personnel">
                                    @foreach ($enseignants['autres'] as $agent)
                                        <option value="{{ $agent->id }}">{{ trim($agent->name.' '.$agent->lastname) }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                La séance apparaîtra dans l'espace de cet enseignant dès la publication.
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Salle au catalogue</label>
                                <select wire:model="salle_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Aucune —</option>
                                    @foreach ($salles as $salle)
                                        <option value="{{ $salle->id }}">{{ $salle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ou salle écrite à la main</label>
                                <input wire:model="salle_libelle" type="text" maxlength="80" placeholder="Salle 23 (TC)"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">N° de séance</label>
                                <input wire:model="seance_numero" type="number" min="1" max="999" placeholder="1"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Séances prévues</label>
                                <input wire:model="seance_total" type="number" min="1" max="999" placeholder="5"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <p class="-mt-4 text-xs text-gray-500">S'affiche « 1/5 » sur la grille, comme sur le document officiel.</p>

                        <div class="flex flex-wrap justify-between gap-4 pt-2">
                            <div>
                                @if ($seance_id)
                                    <button wire:click="viderCase" type="button"
                                            class="px-5 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100">
                                        Vider la case
                                    </button>
                                @endif
                            </div>
                            <div class="flex gap-4">
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Enregistrer</button>
                                <button wire:click="closeModal" type="button" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">Annuler</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

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
