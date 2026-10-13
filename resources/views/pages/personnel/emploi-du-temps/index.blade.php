<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Seance;
use Carbon\Carbon;

name('personnel.emploi-du-temps');
middleware(['auth', 'verified', 'role']);

/**
 * « Mon emploi du temps » : les seances de l'enseignant connecte, toutes
 * specialites confondues, semaine par semaine.
 *
 * Seuls les emplois du temps publies apparaissent : un brouillon change
 * encore, et personne ne doit organiser sa semaine dessus.
 */
new class extends Component {
    /** Lundi de la semaine affichee. */
    public string $lundi = '';

    public function mount(): void
    {
        $this->lundi = Carbon::now()->startOfWeek()->toDateString();
    }

    public function semainePrecedente(): void
    {
        $this->lundi = Carbon::parse($this->lundi)->subWeek()->toDateString();
    }

    public function semaineSuivante(): void
    {
        $this->lundi = Carbon::parse($this->lundi)->addWeek()->toDateString();
    }

    public function semaineCourante(): void
    {
        $this->lundi = Carbon::now()->startOfWeek()->toDateString();
    }

    public function with(): array
    {
        $debut = Carbon::parse($this->lundi)->startOfWeek();
        $fin = $debut->copy()->addDays(6);

        $seances = Seance::with(['cour', 'salle', 'emploiDuTemps.specialite'])
            ->where('enseignant_id', auth()->id())
            ->whereHas('emploiDuTemps', fn ($q) => $q->where('statut', 'publie')
                ->whereDate('date_debut', '>=', $debut)
                ->whereDate('date_debut', '<=', $fin))
            ->orderBy('jour')->orderBy('heure_debut')
            ->get();

        return [
            'debut' => $debut,
            'fin' => $debut->copy()->addDays(5),
            'jours' => config('emploi_du_temps.jours'),
            'parJour' => $seances->groupBy('jour'),
            'total' => $seances->count(),
            'heures' => $seances->sum(fn (Seance $s) => Carbon::parse($s->heure_fin)->floatDiffInHours(Carbon::parse($s->heure_debut))),
            'estSemaineCourante' => $debut->isSameWeek(Carbon::now()),
            // Les emplois du temps complets de ses classes de la semaine.
            'classes' => $seances->pluck('emploiDuTemps')->unique('id')->values(),
            'affectations' => auth()->user()->affectationsDeLAnnee(),
        ];
    }
};
?>

<x-layouts.app header="true">
@volt
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Mon emploi du temps</h1>
            <p class="text-gray-500">Vos séances de la semaine, toutes spécialités confondues.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="semainePrecedente" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50" aria-label="Semaine précédente">←</button>
            <button wire:click="semaineCourante" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cette semaine</button>
            <button wire:click="semaineSuivante" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50" aria-label="Semaine suivante">→</button>
        </div>
    </div>

    {{-- Résumé de la semaine --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Semaine</p>
            <p class="mt-1 text-lg font-bold text-gray-800">
                Du {{ $debut->format('d/m') }} au {{ $fin->format('d/m/Y') }}
            </p>
            @if ($estSemaineCourante)<p class="text-sm text-emerald-600">En cours</p>@endif
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Séances</p>
            <p class="mt-1 text-3xl font-bold text-indigo-600">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Heures</p>
            <p class="mt-1 text-3xl font-bold text-gray-800">{{ rtrim(rtrim(number_format($heures, 1, ',', ' '), '0'), ',') }} h</p>
        </div>
    </div>

    @if ($total === 0)
        <div class="bg-white rounded-2xl shadow-lg p-10 text-center">
            <p class="text-gray-600">Aucune séance pour vous cette semaine.</p>
            @if ($affectations->isEmpty())
                <p class="mt-2 text-sm text-gray-400">
                    Vous n'avez encore aucune affectation. L'administration vous rattache à un département,
                    une filière ou une spécialité depuis son espace.
                </p>
            @else
                <p class="mt-2 text-sm text-gray-400">
                    Un emploi du temps en brouillon n'apparaît pas ici : il devient visible une fois publié.
                </p>
            @endif
        </div>
    @else
        <div class="space-y-5">
            @foreach ($jours as $numero => $jour)
                @php($duJour = $parJour[$numero] ?? collect())
                @continue($duJour->isEmpty())

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-baseline justify-between px-6 py-3 bg-gray-50 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">
                            {{ $jour['long'] }}
                            <span class="ml-2 text-sm font-normal text-gray-400">{{ $debut->copy()->addDays($numero - 1)->format('d/m') }}</span>
                        </h2>
                        <span class="text-sm text-gray-400">{{ $duJour->count() }} séance(s)</span>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        @foreach ($duJour as $seance)
                            <li class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="sm:w-32 shrink-0">
                                    <span class="inline-block px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-semibold">
                                        {{ $seance->horaire() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800">{{ $seance->matiere() }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $seance->emploiDuTemps?->specialite?->name ?? '—' }}
                                        · {{ $seance->emploiDuTemps?->niveau }}
                                        @if ($seance->salleAffichee()) · {{ $seance->salleAffichee() }} @endif
                                    </p>
                                </div>
                                @if ($seance->progression())
                                    <div class="sm:text-right">
                                        <span class="text-xs font-medium text-gray-500">Séance {{ $seance->progression() }}</span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- L'emploi du temps complet de chaque classe où il intervient --}}
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Emplois du temps complets</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($classes as $classe)
                    <a href="/admin/emploi-du-temps/{{ $classe->id }}/pdf" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                        {{ $classe->titre() }} — PDF
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endvolt
</x-layouts.app>
