<?php

use Livewire\Volt\Component;
use App\Models\StaffSatisfactionAnalysis;
use Carbon\Carbon;

new class extends Component {
    public $periodo;
    
    // Chart Data
    public $p1Data = [];
    public $p2Data = [];
    public $p3Data = [];
    public $p4Data = [];
    
    public $totalRespuestas = 0;
    
    public function mount()
    {
        $this->periodo = now()->format('Y-m'); // Default current month
        $this->loadData();
    }
    
    public function updatedPeriodo()
    {
        $this->loadData();
        $this->dispatch('update-charts', [
            'p1' => $this->p1Data,
            'p2' => $this->p2Data,
            'p3' => $this->p3Data,
            'p4' => $this->p4Data,
        ]);
    }
    
    public function loadData()
    {
        $analysis = StaffSatisfactionAnalysis::where('periodo', $this->periodo)->first();
        
        if ($analysis) {
            $this->totalRespuestas = $analysis->total_respuestas;
            
            $this->p1Data = [$analysis->p1_mal_count, $analysis->p1_normal_count, $analysis->p1_bien_count];
            $this->p2Data = [$analysis->p2_mal_count, $analysis->p2_normal_count, $analysis->p2_bien_count];
            $this->p3Data = [$analysis->p3_mal_count, $analysis->p3_normal_count, $analysis->p3_bien_count];
            $this->p4Data = [$analysis->p4_mal_count, $analysis->p4_normal_count, $analysis->p4_bien_count];
        } else {
            $this->totalRespuestas = 0;
            $this->p1Data = [0, 0, 0];
            $this->p2Data = [0, 0, 0];
            $this->p3Data = [0, 0, 0];
            $this->p4Data = [0, 0, 0];
        }
    }
}; ?>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header & Filters -->
        <div class="md:flex md:items-center md:justify-between bg-white p-4 rounded-lg shadow">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Tablero de Satisfacción Personal
                </h2>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <input type="month" wire:model.live="periodo" class="block rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Total Encuestas</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $totalRespuestas }}</dd>
            </div>
             <!-- Link to History -->
             <div class="overflow-hidden rounded-lg bg-indigo-50 px-4 py-5 shadow sm:p-6 flex items-center justify-center">
                <a href="{{ route('staff-satisfaction.historial.importacion') }}" class="text-indigo-600 font-medium hover:underline flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Ver Historial Completo
                </a>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6"
             x-data="chartsHandler(@js($p1Data), @js($p2Data), @js($p3Data), @js($p4Data))"
             x-init="initCharts()"
             @update-charts.window="updateCharts($event.detail)"
        >
            <!-- Chart 1 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">1. Trato Jefe/Supervisor</h3>
                <div class="relative h-64">
                    <canvas id="chartP1"></canvas>
                </div>
            </div>

            <!-- Chart 2 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">2. Trato Compañeros</h3>
                <div class="relative h-64">
                    <canvas id="chartP2"></canvas>
                </div>
            </div>

            <!-- Chart 3 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">3. Clima Laboral</h3>
                <div class="relative h-64">
                    <canvas id="chartP3"></canvas>
                </div>
            </div>

            <!-- Chart 4 -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">4. Comodidad General</h3>
                <div class="relative h-64">
                    <canvas id="chartP4"></canvas>
                </div>
            </div>
        
        </div>
    </div>

    <!-- Chart.js Logic -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        // Register the plugin globally
        Chart.register(ChartDataLabels);

        function chartsHandler(p1, p2, p3, p4) {
            return {
                charts: {},
                data: { p1: p1, p2: p2, p3: p3, p4: p4 },
                colors: ['#EF4444', '#F59E0B', '#10B981'], // Red, Yellow, Green
                labels: ['Mal/Incómodo', 'Normal', 'Bien/Cómodo'],

                initCharts() {
                    const ctx1 = document.getElementById('chartP1').getContext('2d');
                    const ctx2 = document.getElementById('chartP2').getContext('2d');
                    const ctx3 = document.getElementById('chartP3').getContext('2d');
                    const ctx4 = document.getElementById('chartP4').getContext('2d');

                    this.charts.p1 = this.createPieChart(ctx1, this.data.p1);
                    this.charts.p2 = this.createPieChart(ctx2, this.data.p2);
                    this.charts.p3 = this.createPieChart(ctx3, this.data.p3);
                    this.charts.p4 = this.createPieChart(ctx4, this.data.p4);
                },

                createPieChart(ctx, data) {
                    return new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                data: data,
                                backgroundColor: this.colors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                                datalabels: {
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 14
                                    },
                                    formatter: function(value, context) {
                                        let total = context.chart._metasets[context.datasetIndex].total;
                                        let percentage = Math.round((value / total) * 100) + '%';
                                        return value > 0 ? percentage : '';
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            let value = context.raw;
                                            let total = context.chart._metasets[context.datasetIndex].total;
                                            let percentage = Math.round((value / total) * 100) + '%';
                                            return label + value + ' (' + percentage + ')';
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                updateCharts(detail) {
                    this.updateChart(this.charts.p1, detail.p1);
                    this.updateChart(this.charts.p2, detail.p2);
                    this.updateChart(this.charts.p3, detail.p3);
                    this.updateChart(this.charts.p4, detail.p4);
                },

                updateChart(chart, data) {
                    if (chart) {
                        chart.data.datasets[0].data = data;
                        chart.update();
                    }
                }
            }
        }
    </script>
</div>
