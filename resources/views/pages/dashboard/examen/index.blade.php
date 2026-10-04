<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Examen;

name('examens.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $examens;

    public function mount()
    {
        \Log::info('mount appelé pour examens.index à ' . now()->toDateTimeString());
        $this->loadData();
    }

    private function loadData()
    {
        try {
            $user = auth()->user();
            \Log::info('Utilisateur chargé', ['user_id' => $user->id, 'filiere_id' => $user->filiere_id ?? 'non défini']);

            // Charger les examens pour la filière de l'utilisateur, avec relations
            $this->examens = Examen::where('filiere_id', $user->filiere_id)
                ->where('status', 'Success')
                ->with(['cours', 'salle'])
                ->get();

            \Log::info('Examens chargés', ['examens_count' => $this->examens->count()]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du chargement des examens : ' . $e->getMessage());
            $this->examens = collect(); // Collection vide en cas d'erreur
        }
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Examens</h1>
                    <p class="text-gray-500">Consultez les plannings et résultats de vos examens.</p>
                </div>
            </div>

            <!-- Planning des Examens -->
            <div class="bg-white rounded-xl shadow p-4 sm:p-5 mb-8">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Planning des Examens</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Nombre de crédit</th>

                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Salle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($examens as $examen)
                                <tr class="bg-gray-50 rounded-lg">
                                    <td class="p-4">{{ $examen->cours->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $examen->cours->credit ?? 'Non défini' }}</td>

                                    <td class="p-4">{{ \Carbon\Carbon::parse($examen->date)->format('d/m/Y') }}</td>
                                    <td class="p-4">{{ $examen->salle->name ?? 'Non défini' }}</td>
                                    <td class="p-4 text-green-600 ">{{ \Carbon\Carbon::parse($examen->heure)->format('H:i') }} - {{ \Carbon\Carbon::parse($examen->heure)->addHours(2)->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-gray-600 text-center">Aucun examen prévu pour votre filière.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Section Résultats des Examens (commentée, inchangée) --}}
            {{-- <div class="bg-white rounded-xl shadow p-4 sm:p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Résultats des Examens</h2>
                <div class="space-y-4">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Algorithmique - <span class="text-gray-600">15/20 (Semestre 1, 2024)</span></p>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Base de Données - <span class="text-gray-600">12/20 (Semestre 1, 2024)</span></p>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les résultats</button>
                </div>
            </div> --}}
        </div>
    @endvolt
</x-layouts.app>
