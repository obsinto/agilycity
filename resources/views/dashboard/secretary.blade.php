@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Cabeçalho com Nome da Secretaria -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard - {{ $secretary->name }}</h1>
            <p class="text-sm text-gray-500">Visão geral dos gastos da sua secretaria</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Range de Data -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Período</label>
                    <input type="text"
                           id="dateRange"
                           name="dateRange"
                           class="mt-1 block w-full p-2 border rounded-md bg-white cursor-pointer"
                           readonly
                           placeholder="Selecione o período"/>
                </div>

                <!-- Departamento (limitado à secretaria do usuário) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Departamento</label>
                    <select id="department" class="mt-1 block w-full p-2 border rounded-md">
                        <option value="">Todos</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo de Despesa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <select id="expenseType" class="mt-1 block w-full p-2 border rounded-md">
                        <option value="">Todos</option>
                        @foreach($expenseTypes as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botão Limpar Filtros -->
                <div class="flex items-end">
                    <button onclick="clearFilters()"
                            class="w-full p-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200">
                        Limpar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Cards Principais -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Gasto -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col">
                    <p class="text-sm text-gray-500">Total de Gastos</p>
                    <p class="text-2xl font-bold" id="totalExpenses">
                        R$ {{ number_format($totalExpenses, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Comparativo Mensal -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col">
                    <p class="text-sm text-gray-500">Gastos Mês Atual</p>
                    <p class="text-2xl font-bold" id="currentMonthExpenses">
                        R$ {{ number_format($currentMonthExpenses, 2, ',', '.') }}
                    </p>
                    <div class="flex items-center mt-2 month-variation">
                        @php
                            $percentChange = $lastMonthExpenses != 0
                                ? (($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100
                                : 0;
                        @endphp
                        <span class="{{ $percentChange >= 0 ? 'text-red-500' : 'text-green-500' }} flex items-center">
                            @if($percentChange >= 0)
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M5 15l7-7 7 7"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            @endif
                            {{ number_format(abs($percentChange), 1) }}% em relação ao mês anterior
                        </span>
                    </div>
                </div>
            </div>

            <!-- Departamento com Maior Gasto -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col">
                    <p class="text-sm text-gray-500">Maior Gasto - Departamento</p>
                    <p class="text-lg font-bold" id="topDepartmentName">{{ $topDepartment->name }}</p>
                    <p class="text-xl" id="topDepartmentAmount">
                        R$ {{ number_format($topDepartment->expenses->sum('amount'), 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Treemap -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Distribuição de Gastos por Departamento</h2>
                <div class="text-sm text-gray-500">
                    Clique para filtrar | Duplo clique para limpar filtros
                </div>
            </div>
            <div id="treemap" class="h-96"></div>
        </div>

        <!-- Expense Type Pie Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Distribuição por Tipo de Despesa</h2>
                <div class="text-sm text-gray-500">
                    Clique para filtrar | Clique novamente para remover filtro
                </div>
            </div>
            <div id="expenseTypePie" class="h-96"></div>
        </div>

        // Vistoria
        <!-- Status de Fechamento de Departamentos -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Status de Fechamento - {{ $monthName }}/{{ $year }}</h2>
                <a href="{{ route('compliance.dashboard') }}" class="text-sm text-blue-600 hover:underline">
                    Ver dashboard completo →
                </a>
            </div>

            <div class="flex items-center mb-4">
                <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                    <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                </div>
                <span class="text-sm font-medium">{{ number_format($completionPercentage, 1) }}%</span>
            </div>

            <div class="text-sm text-gray-600 mb-4">
                {{ $closedDepartments }} de {{ $totalDepartments }} departamentos fechados
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($departmentsData as $dept)
                    <div
                        class="border rounded-lg p-4 {{ $dept['is_closed'] ? 'border-green-500' : 'border-yellow-500' }}">
                        <h3 class="font-medium">{{ $dept['name'] }}</h3>

                        @if($dept['is_closed'])
                            <div class="mt-2 text-sm text-green-600 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                Mês Fechado
                            </div>
                        @elseif($dept['status'] == 'ready_to_close')
                            <div class="mt-2 text-sm text-blue-600 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pronto para Fechar
                            </div>
                        @else
                            <div class="mt-2 text-sm text-yellow-600 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pendente
                            </div>
                        @endif

                        @if(!$dept['is_closed'] && count($dept['missing_categories']) > 0 || count($dept['missing_fixed_expenses']) > 0)
                            <div class="mt-1 text-xs text-red-500">
                                {{ count($dept['missing_categories']) + count($dept['missing_fixed_expenses']) }}
                                pendências
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>


        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Evolução Mensal</h2>
            <div id="timeline" class="h-96"></div>
        </div>

        <!-- Gráfico de Barras -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Despesas por Departamento</h2>
            <div id="barChart" class="h-96"></div>
        </div>
    </div>

    <!-- Loading State -->
    <div class="dashboard-loading fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
            <svg class="animate-spin h-6 w-6 text-blue-600 mr-3"
                 xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Carregando dados...</span>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Estilos personalizados para o DateRangePicker */
        .daterangepicker {
            font-family: inherit;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .daterangepicker .ranges li {
            background-color: #f9fafb;
            border-radius: 0.25rem;
            color: #374151;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
        }

        .daterangepicker .ranges li:hover {
            background-color: #f3f4f6;
        }

        .daterangepicker .ranges li.active {
            background-color: #3b82f6;
            color: white;
        }

        .daterangepicker td.active,
        .daterangepicker td.active:hover {
            background-color: #3b82f6;
        }

        .daterangepicker td.in-range {
            background-color: #dbeafe;
        }

        .daterangepicker .drp-buttons .btn {
            border-radius: 0.25rem;
        }

        .daterangepicker .drp-buttons .applyBtn {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .daterangepicker .drp-buttons .applyBtn:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
    </style>
@endpush

@push('scripts')
    @vite('resources/js/components/dashboard/secretary/index.js')

    <script>
        /**
         * Função para limpar todos os filtros
         */
        function clearFilters() {
            $('#department').val('');
            $('#expenseType').val('');

            const picker = $('#dateRange').data('daterangepicker');
            if (picker) {
                const startDate = moment().startOf('month');
                const endDate = moment().endOf('month');
                picker.setStartDate(startDate);
                picker.setEndDate(endDate);
                $('#dateRange').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
            }

            if (typeof window.loadSecretaryDashboardData === 'function') {
                window.loadSecretaryDashboardData({
                    date_range: `${moment().startOf('month').format('DD/MM/YYYY')} - ${moment().endOf('month').format('DD/MM/YYYY')}`
                });
            }
        }
    </script>
@endpush
