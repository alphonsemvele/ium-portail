
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('filiere.statistique');
middleware(['auth','verified'])
?>
<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Statistiques Détaillées</h1>
                    <p class="text-gray-500">Analysez les performances et les données des spécialités.</p>
                </div>
            </div>

            <!-- Statistiques par Spécialité -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs uppercase text-gray-500">Taux de Réussite</p>
                    <p class="text-3xl font-bold text-indigo-600">85%</p>
                    <p class="text-xs text-gray-500">Moyenne filière (2024)</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs uppercase text-gray-500">Cours Dispensés</p>
                    <p class="text-3xl font-bold text-indigo-600">26</p>
                    <p class="text-xs text-gray-500">Total filière (2024)</p>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <p class="text-xs uppercase text-gray-500">Étudiants Actifs</p>
                    <p class="text-3xl font-bold text-indigo-600">350</p>
                    <p class="text-xs text-gray-500">Total filière</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-4 sm:p-5 mb-8">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Détails par Spécialité</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Étudiants</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Taux de Réussite</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Personnel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Développement Logiciel</td>
                                <td class="p-4">120</td>
                                <td class="p-4">88%</td>
                                <td class="p-4">8</td>
                                <td class="p-4">10</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Cybersécurité</td>
                                <td class="p-4">80</td>
                                <td class="p-4">82%</td>
                                <td class="p-4">6</td>
                                <td class="p-4">7</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Intelligence Artificielle</td>
                                <td class="p-4">100</td>
                                <td class="p-4">90%</td>
                                <td class="p-4">7</td>
                                <td class="p-4">6</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Réseaux et Télécommunications</td>
                                <td class="p-4">50</td>
                                <td class="p-4">80%</td>
                                <td class="p-4">5</td>
                                <td class="p-4">2</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

