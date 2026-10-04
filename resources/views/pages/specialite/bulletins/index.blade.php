<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;

name('filiere.bulletins.index');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $user;
    public $selectedYear = '';
    public $showDetailsModal = false;
    public $selectedBulletin = null;

    // Données simulées pour les bulletins
    public $bulletins = [];

    public function mount()
    {
        $this->user = auth()->user();
        $this->selectedYear = date('Y');
        $this->loadBulletins();
    }

    public function loadBulletins()
    {
        // Simulation de données de bulletins de paie
        $this->bulletins = [
            [
                'id' => 1,
                'mois' => 'Janvier',
                'annee' => 2025,
                'periode' => 'Janvier 2025',
                'date_emission' => '2025-01-31',
                'salaire_base' => 350000,
                'primes' => [
                    ['libelle' => 'Prime d\'ancienneté', 'montant' => 35000],
                    ['libelle' => 'Prime de transport', 'montant' => 25000],
                    ['libelle' => 'Prime de logement', 'montant' => 50000],
                ],
                'retenues' => [
                    ['libelle' => 'CNPS', 'montant' => 15750],
                    ['libelle' => 'Impôt sur le revenu (IRPP)', 'montant' => 45000],
                    ['libelle' => 'Cotisation syndicale', 'montant' => 5000],
                ],
                'heures_sup' => 10,
                'taux_horaire_sup' => 2500,
                'statut' => 'payé',
            ],
            [
                'id' => 2,
                'mois' => 'Février',
                'annee' => 2025,
                'periode' => 'Février 2025',
                'date_emission' => '2025-02-28',
                'salaire_base' => 350000,
                'primes' => [
                    ['libelle' => 'Prime d\'ancienneté', 'montant' => 35000],
                    ['libelle' => 'Prime de transport', 'montant' => 25000],
                    ['libelle' => 'Prime de logement', 'montant' => 50000],
                ],
                'retenues' => [
                    ['libelle' => 'CNPS', 'montant' => 15750],
                    ['libelle' => 'Impôt sur le revenu (IRPP)', 'montant' => 45000],
                    ['libelle' => 'Cotisation syndicale', 'montant' => 5000],
                ],
                'heures_sup' => 8,
                'taux_horaire_sup' => 2500,
                'statut' => 'payé',
            ],
            [
                'id' => 3,
                'mois' => 'Mars',
                'annee' => 2025,
                'periode' => 'Mars 2025',
                'date_emission' => '2025-03-31',
                'salaire_base' => 350000,
                'primes' => [
                    ['libelle' => 'Prime d\'ancienneté', 'montant' => 35000],
                    ['libelle' => 'Prime de transport', 'montant' => 25000],
                    ['libelle' => 'Prime de logement', 'montant' => 50000],
                    ['libelle' => 'Prime exceptionnelle', 'montant' => 100000],
                ],
                'retenues' => [
                    ['libelle' => 'CNPS', 'montant' => 15750],
                    ['libelle' => 'Impôt sur le revenu (IRPP)', 'montant' => 52000],
                    ['libelle' => 'Cotisation syndicale', 'montant' => 5000],
                ],
                'heures_sup' => 15,
                'taux_horaire_sup' => 2500,
                'statut' => 'payé',
            ],
            [
                'id' => 4,
                'mois' => 'Avril',
                'annee' => 2025,
                'periode' => 'Avril 2025',
                'date_emission' => null,
                'salaire_base' => 350000,
                'primes' => [
                    ['libelle' => 'Prime d\'ancienneté', 'montant' => 35000],
                    ['libelle' => 'Prime de transport', 'montant' => 25000],
                    ['libelle' => 'Prime de logement', 'montant' => 50000],
                ],
                'retenues' => [
                    ['libelle' => 'CNPS', 'montant' => 15750],
                    ['libelle' => 'Impôt sur le revenu (IRPP)', 'montant' => 45000],
                    ['libelle' => 'Cotisation syndicale', 'montant' => 5000],
                ],
                'heures_sup' => 0,
                'taux_horaire_sup' => 2500,
                'statut' => 'en_attente',
            ],
        ];
    }

    public function calculerTotalPrimes($bulletin)
    {
        return collect($bulletin['primes'])->sum('montant');
    }

    public function calculerTotalRetenues($bulletin)
    {
        return collect($bulletin['retenues'])->sum('montant');
    }

    public function calculerHeuresSupMontant($bulletin)
    {
        return $bulletin['heures_sup'] * $bulletin['taux_horaire_sup'];
    }

    public function calculerBrut($bulletin)
    {
        return $bulletin['salaire_base'] + $this->calculerTotalPrimes($bulletin) + $this->calculerHeuresSupMontant($bulletin);
    }

    public function calculerNet($bulletin)
    {
        return $this->calculerBrut($bulletin) - $this->calculerTotalRetenues($bulletin);
    }

    public function openDetailsModal($id)
    {
        $this->selectedBulletin = collect($this->bulletins)->firstWhere('id', $id);
        $this->showDetailsModal = true;
    }

    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedBulletin = null;
    }

    public function formatMontant($montant)
    {
        return number_format($montant, 0, ',', ' ');
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mes Bulletins de Paie</h1>
                    <p class="text-gray-500">Consultez vos fiches de salaire mensuelles</p>
                </div>
            </div>

            <!-- Carte Employé -->
            <div class="bg-blue-500 rounded-2xl shadow-xl p-6 mb-8 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="Photo" class="w-14 h-14 rounded-full object-cover">
                            @else
                                <span class="text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->lastname ?? '', 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ $user->name }} {{ $user->lastname ?? '' }}</h3>
                            <p class="text-emerald-100">Matricule : {{ $user->matricule ?? 'ENS-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-emerald-200 text-sm">{{ ucfirst($user->role) }} - {{ $user->poste ?? 'Enseignant' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 text-right">
                        <p class="text-emerald-100 text-sm">Année en cours</p>
                        <p class="text-3xl font-bold">{{ $selectedYear }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-500 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100">Salaire de Base</p>
                            <p class="text-2xl font-bold">{{ number_format(350000, 0, ',', ' ') }} F</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-green-500 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100">Bulletins Payés</p>
                            <p class="text-2xl font-bold">{{ collect($bulletins)->where('statut', 'payé')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-500 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100">En Attente</p>
                            <p class="text-2xl font-bold">{{ collect($bulletins)->where('statut', 'en_attente')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-500 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100">Total Net (Année)</p>
                            <p class="text-2xl font-bold">{{ number_format(collect($bulletins)->where('statut', 'payé')->sum(fn($b) => $this->calculerNet($b)), 0, ',', ' ') }} F</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtre par année -->
            <div class="bg-white rounded-xl shadow p-4 mb-6">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par année</label>
                        <select wire:model.live="selectedYear" class="w-full md:w-48 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Liste des Bulletins -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Historique des Bulletins</h2>
                </div>

                @if(count($bulletins) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Période</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Salaire Base</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Primes</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Retenues</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Net à Payer</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($bulletins as $bulletin)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $bulletin['periode'] }}</p>
                                                    @if($bulletin['date_emission'])
                                                        <p class="text-xs text-gray-500">Émis le {{ \Carbon\Carbon::parse($bulletin['date_emission'])->format('d/m/Y') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-700">{{ number_format($bulletin['salaire_base'], 0, ',', ' ') }} F</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                                +{{ number_format($this->calculerTotalPrimes($bulletin), 0, ',', ' ') }} F
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                                                -{{ number_format($this->calculerTotalRetenues($bulletin), 0, ',', ' ') }} F
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-lg font-bold text-emerald-600">{{ number_format($this->calculerNet($bulletin), 0, ',', ' ') }} F</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($bulletin['statut'] === 'payé')
                                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Payé</span>
                                            @else
                                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">En attente</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button wire:click="openDetailsModal({{ $bulletin['id'] }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Voir détails">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </button>
                                                @if($bulletin['statut'] === 'payé')
                                                    <a href="/filiere/bulletins/{{ $bulletin['id'] }}" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Télécharger PDF">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun bulletin de paie</h3>
                        <p class="text-gray-500">Vos bulletins de paie apparaîtront ici une fois émis.</p>
                    </div>
                @endif
            </div>

            <!-- Modal Détails Bulletin -->
            @if($showDetailsModal && $selectedBulletin)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
                    

                        <div class="p-6">
                            <!-- Informations Employé -->
                            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Informations Employé</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Nom complet</p>
                                        <p class="font-medium">{{ $user->name }} {{ $user->lastname ?? '' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Matricule</p>
                                        <p class="font-medium font-mono">{{ $user->matricule ?? 'ENS-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Poste</p>
                                        <p class="font-medium">{{ $user->poste ?? 'Enseignant' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Date d'émission</p>
                                        <p class="font-medium">{{ $selectedBulletin['date_emission'] ? \Carbon\Carbon::parse($selectedBulletin['date_emission'])->format('d/m/Y') : '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Salaire de Base -->
                            <div class="border-b border-gray-200 pb-4 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-700">Salaire de Base</span>
                                    <span class="font-bold text-gray-800">{{ number_format($selectedBulletin['salaire_base'], 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>

                            <!-- Primes et Indemnités -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-green-600 uppercase mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Primes et Indemnités
                                </h3>
                                <div class="bg-green-50 rounded-xl p-4 space-y-2">
                                    @foreach($selectedBulletin['primes'] as $prime)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $prime['libelle'] }}</span>
                                            <span class="text-green-600 font-medium">+{{ number_format($prime['montant'], 0, ',', ' ') }} F</span>
                                        </div>
                                    @endforeach
                                    @if($selectedBulletin['heures_sup'] > 0)
                                        <div class="flex justify-between text-sm border-t border-green-200 pt-2 mt-2">
                                            <span class="text-gray-600">Heures supplémentaires ({{ $selectedBulletin['heures_sup'] }}h × {{ number_format($selectedBulletin['taux_horaire_sup'], 0, ',', ' ') }} F)</span>
                                            <span class="text-green-600 font-medium">+{{ number_format($this->calculerHeuresSupMontant($selectedBulletin), 0, ',', ' ') }} F</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between font-semibold text-green-700 border-t border-green-200 pt-2 mt-2">
                                        <span>Total Gains</span>
                                        <span>+{{ number_format($this->calculerTotalPrimes($selectedBulletin) + $this->calculerHeuresSupMontant($selectedBulletin), 0, ',', ' ') }} F</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Retenues -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-red-600 uppercase mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                    Retenues
                                </h3>
                                <div class="bg-red-50 rounded-xl p-4 space-y-2">
                                    @foreach($selectedBulletin['retenues'] as $retenue)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $retenue['libelle'] }}</span>
                                            <span class="text-red-600 font-medium">-{{ number_format($retenue['montant'], 0, ',', ' ') }} F</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between font-semibold text-red-700 border-t border-red-200 pt-2 mt-2">
                                        <span>Total Retenues</span>
                                        <span>-{{ number_format($this->calculerTotalRetenues($selectedBulletin), 0, ',', ' ') }} F</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Récapitulatif -->
                            <div class="bg-gray-800 text-white rounded-xl p-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Salaire Brut</span>
                                        <span class="font-medium">{{ number_format($this->calculerBrut($selectedBulletin), 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Total Retenues</span>
                                        <span class="font-medium text-red-400">-{{ number_format($this->calculerTotalRetenues($selectedBulletin), 0, ',', ' ') }} FCFA</span>
                                    </div>
                                    <div class="border-t border-gray-600 pt-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-semibold">Net à Payer</span>
                                            <span class="text-2xl font-bold text-emerald-400">{{ number_format($this->calculerNet($selectedBulletin), 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="p-6 border-t border-gray-200 flex justify-between">
                            <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Fermer
                            </button>
                            @if($selectedBulletin['statut'] === 'payé')
                                <a href="/filiere/bulletins/{{ $selectedBulletin['id'] }}" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Voir le bulletin complet
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>