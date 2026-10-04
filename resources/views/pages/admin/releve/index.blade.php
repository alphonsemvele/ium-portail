<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Examen;
use App\Models\User;
use App\Models\Cycle;

name('admin.releve');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search      = '';
    public $cycle_id           = null;
    public $examen_id          = null;
    public $departement_id     = null;
    public $filiere_id         = null;
    public $specialite_id      = null;

    public $cycles       = [];
    public $examens      = [];
    public $departements = [];
    public $filieres     = [];
    public $specialites  = [];

    public function mount()
    {
        $this->cycles       = Cycle::orderBy('name')->get();
        $this->examens      = collect();
        $this->departements = collect();
        $this->filieres     = collect();
        $this->specialites  = collect();
    }

    public function updatingSearch()       { $this->resetPage(); }
    public function updatingCycleId()      { $this->resetPage(); }
    public function updatingExamenId()     { $this->resetPage(); }
    public function updatingSpecialiteId() { $this->resetPage(); }

    // ── Cascades ──────────────────────────────────────────────────────────────
    public function updatedCycleId($value)
    {
        $this->examen_id      = null;
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->filieres       = collect();
        $this->specialites    = collect();

        if ($value) {
            $this->examens = Examen::where('cycle_id', $value)
                ->whereIn('statut', ['ouvert', 'en_cours', 'ferme'])
                ->orderBy('titre')->get();

            $this->departements = Departement::where('cycle_id', $value)
                ->where('status', 'Success')
                ->orderBy('nom')->get();
        } else {
            $this->examens      = collect();
            $this->departements = collect();
        }
    }

    public function updatedDepartementId($value)
    {
        $this->filiere_id    = null;
        $this->specialite_id = null;
        $this->specialites   = collect();
        $this->filieres = $value
            ? Filiere::where('departement_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();
    }

    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();
    }

    // ── Étudiants ─────────────────────────────────────────────────────────────
    public function getEtudiantsProperty()
    {
        if (!$this->search && !$this->cycle_id) return null;

        // On ne veut exclure que les comptes supprimés (failed) : un étudiant
        // désactivé (pending) doit rester consultable et son relevé régénérable
        // par l'administration.
        $q = User::whereIn('role', ['student', 'etudiant'])
            ->where('status', '!=', 'failed')
            ->with(['specialite.filiere.departement.cycle']);

        if ($this->search) {
            $s = $this->search;
            $q->where(fn($sub) =>
                $sub->where('name', 'like', "%$s%")
                    ->orWhere('lastname',  'like', "%$s%")
                    ->orWhere('matricule', 'like', "%$s%")
            );
        }

        if ($this->specialite_id) {
            $q->where('specialite_id', $this->specialite_id);
        } elseif ($this->filiere_id) {
            $q->whereIn('specialite_id', Specialite::where('filiere_id', $this->filiere_id)->pluck('id'));
        } elseif ($this->departement_id) {
            $fIds = Filiere::where('departement_id', $this->departement_id)->pluck('id');
            $q->whereIn('specialite_id', Specialite::whereIn('filiere_id', $fIds)->pluck('id'));
        } elseif ($this->cycle_id) {
            $dIds = Departement::where('cycle_id', $this->cycle_id)->pluck('id');
            $fIds = Filiere::whereIn('departement_id', $dIds)->pluck('id');
            $q->whereIn('specialite_id', Specialite::whereIn('filiere_id', $fIds)->pluck('id'));
        }

        return $q->orderBy('name')->paginate(20);
    }

    // ── Export PDF ────────────────────────────────────────────────────────────
    public function exportReleve(int $etudiantId)
    {
        $etudiant = User::with(['filiere', 'specialite.filiere', 'cycle'])->findOrFail($etudiantId);

        [$sessions, $globalStats] = \App\Services\ReleveService::build($etudiant, $this->examen_id);

        $filiere = $etudiant->filiere?->name ?? $etudiant->specialite?->filiere?->name ?? $etudiant->specialite?->name;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.releve-etudiant', [
            'user'        => $etudiant,
            'sessions'    => $sessions,
            'globalStats' => $globalStats,
            'filiere'     => $filiere,
        ])->setPaper('a4', 'portrait');

        $filename = 'Releve_' . str_replace(' ', '_', strtoupper($etudiant->name ?? 'etudiant')) . '.pdf';

        // Livewire ne déclenche un téléchargement que pour une StreamedResponse
        // (Pdf::download() renvoie une Illuminate\Http\Response, non reconnue).
        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
};

?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Relevé de Notes</h1>
                    <p class="text-gray-500">Recherchez un étudiant et téléchargez son relevé officiel.</p>
                </div>
            </div>

        {{-- ─── Recherche directe ─── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
                Recherche directe — sans filtre
            </p>
            <div class="relative max-w-lg">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg wire:loading.remove wire:target="search" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                    <svg wire:loading wire:target="search" class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search"
                    placeholder="Nom, prénom ou matricule…"
                    class="w-full pl-10 pr-10 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400
                           placeholder-gray-400 text-gray-700 transition-all duration-150"/>
                @if ($search)
                    <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
            @if ($search)
                <p class="text-xs text-indigo-500 mt-2 ml-1" wire:loading.remove wire:target="search">
                    @if ($this->etudiants)
                        {{ $this->etudiants->total() }} étudiant(s) pour <span class="font-semibold">"{{ $search }}"</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-2 ml-1" wire:loading wire:target="search">Recherche en cours…</p>
            @endif
        </div>

        {{-- ─── Filtres ─── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filtrer par promotion &amp; session
            </p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">

                {{-- Cycle --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cycle</label>
                    <select wire:model.live="cycle_id"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">— Cycle —</option>
                        @foreach ($cycles as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Examen --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Examen / Session
                        @if (!$cycle_id)<span class="text-gray-300 text-[10px]">(cycle d'abord)</span>@endif
                    </label>
                    <select wire:model.live="examen_id" wire:key="ex-{{ $cycle_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Examen —</option>
                        @foreach ($examens as $ex)
                            <option value="{{ $ex->id }}">{{ $ex->titre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Département --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Département
                        @if (!$cycle_id)<span class="text-gray-300 text-[10px]">(cycle d'abord)</span>@endif
                    </label>
                    <select wire:model.live="departement_id" wire:key="dep-{{ $cycle_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$cycle_id)>
                        <option value="">— Département —</option>
                        @foreach ($departements as $d)
                            <option value="{{ $d->id }}">{{ $d->nom }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filière --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Filière
                        @if (!$departement_id)<span class="text-gray-300 text-[10px]">(département d'abord)</span>@endif
                    </label>
                    <select wire:model.live="filiere_id" wire:key="fil-{{ $departement_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$departement_id)>
                        <option value="">— Filière —</option>
                        @foreach ($filieres as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Spécialité --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        Spécialité
                        @if (!$filiere_id)<span class="text-gray-300 text-[10px]">(filière d'abord)</span>@endif
                    </label>
                    <select wire:model.live="specialite_id" wire:key="spe-{{ $filiere_id }}"
                        class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white
                               {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        @disabled(!$filiere_id)>
                        <option value="">— Spécialité —</option>
                        @foreach ($specialites as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Badge session --}}
            <div class="mt-3">
                @if ($examen_id)
                    @php $exNom = $examens->firstWhere('id', $examen_id)?->titre ?? '—'; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Session : {{ $exNom }} — notes filtrées par cette session
                    </span>
                @else
                    <span class="text-xs text-amber-600 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        Aucune session — le relevé inclura toutes les notes disponibles
                    </span>
                @endif
            </div>
        </div>

        {{-- ─── Liste étudiants ─── --}}
        @if (!$search && !$cycle_id)
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-base font-medium text-gray-500">Recherchez un étudiant ou sélectionnez un filtre</p>
                <p class="text-sm mt-1">Utilisez la barre de recherche ou les filtres par promotion.</p>
            </div>

        @elseif ($this->etudiants && $this->etudiants->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-medium">Aucun étudiant trouvé.</p>
                <p class="text-sm mt-1">Modifiez votre recherche ou vos filtres.</p>
            </div>

        @elseif ($this->etudiants)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-700">{{ $this->etudiants->total() }} étudiant(s)</p>
                    <p class="text-xs text-gray-400">Cliquez sur « Télécharger » pour générer le relevé PDF</p>
                </div>

                <table class="w-full text-sm text-left">
                    <thead class="bg-indigo-50 text-indigo-700 text-xs uppercase tracking-wide border-b border-indigo-100">
                        <tr>
                            <th class="py-3 px-5">#</th>
                            <th class="py-3 px-5">Étudiant</th>
                            <th class="py-3 px-5">Matricule</th>
                            <th class="py-3 px-5">Filière / Spécialité</th>
                            <th class="py-3 px-5">Niveau</th>
                            <th class="py-3 px-5 text-center">Relevé PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->etudiants as $etudiant)
                            <tr class="hover:bg-indigo-50/30 transition-colors" wire:key="e{{ $etudiant->id }}">
                                <td class="py-4 px-5 text-gray-400 text-xs font-medium">
                                    {{ ($this->etudiants->currentPage() - 1) * $this->etudiants->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-700 font-bold text-sm">
                                            {{ strtoupper(substr($etudiant->name ?? 'E', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">
                                                {{ strtoupper($etudiant->name ?? '') }}
                                                <span class="font-normal text-gray-600">{{ $etudiant->lastname ?? '' }}</span>
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $etudiant->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 font-mono">
                                        {{ $etudiant->matricule ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5">
                                    <p class="text-xs font-medium text-gray-700">{{ $etudiant->specialite?->filiere?->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-400">{{ $etudiant->specialite?->name ?? '' }}</p>
                                </td>
                                <td class="py-4 px-5 text-gray-600 text-xs">
                                    {{ $etudiant->niveau ?? $etudiant->specialite?->niveau ?? '—' }}
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <button
                                        wire:click="exportReleve({{ $etudiant->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="exportReleve({{ $etudiant->id }})"
                                        class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700
                                               text-white text-xs font-semibold py-2 px-4 rounded-lg
                                               transition-all duration-150 shadow-sm disabled:opacity-60 disabled:cursor-wait">
                                        <svg wire:loading.remove wire:target="exportReleve({{ $etudiant->id }})"
                                             class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <svg wire:loading wire:target="exportReleve({{ $etudiant->id }})"
                                             class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        <span wire:loading.remove wire:target="exportReleve({{ $etudiant->id }})">Télécharger</span>
                                        <span wire:loading       wire:target="exportReleve({{ $etudiant->id }})">Génération…</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($this->etudiants->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        <nav class="flex items-center justify-between select-none">
                            <div class="text-sm text-gray-500">
                                <span class="font-semibold text-gray-700">{{ $this->etudiants->firstItem() }}</span>
                                – <span class="font-semibold text-gray-700">{{ $this->etudiants->lastItem() }}</span>
                                sur <span class="font-semibold text-gray-700">{{ $this->etudiants->total() }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                @if ($this->etudiants->onFirstPage())
                                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <button wire:click="previousPage" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                @endif
                                @php $cur=$this->etudiants->currentPage(); $lst=$this->etudiants->lastPage(); $st=max(1,$cur-2); $en=min($lst,$cur+2); @endphp
                                @if ($st > 1)<button wire:click="gotoPage(1)" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">1</button>@if($st>2)<span class="px-1 text-gray-400">…</span>@endif@endif
                                @for ($p=$st;$p<=$en;$p++)
                                    @if ($p===$cur)<span class="inline-flex items-center justify-center w-9 h-9 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 rounded-lg">{{$p}}</span>
                                    @else<button wire:click="gotoPage({{$p}})" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">{{$p}}</button>@endif
                                @endfor
                                @if ($en < $lst)@if($en<$lst-1)<span class="px-1 text-gray-400">…</span>@endif<button wire:click="gotoPage({{$lst}})" class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">{{$lst}}</button>@endif
                                @if ($this->etudiants->hasMorePages())
                                    <button wire:click="nextPage" class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-3 py-2 text-sm text-gray-300 bg-white border border-gray-200 rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </div>
                        </nav>
                    </div>
                @endif
            </div>
        @endif

    </div>
    @endvolt
</x-layouts.app>