@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <!-- Secretaria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Secretaria</label>
                    <select id="secretary" class="mt-1 block w-full p-2 border rounded-md">
                        <option value="">Todas</option>
                        @foreach($secretaries as $secretary)
                            <option value="{{ $secretary->id }}">{{ $secretary->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Departamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Departamento</label>
                    <select id="department" class="mt-1 block w-full p-2 border rounded-md">
                        <option value="">Todos</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" data-secretary="{{ $department->secretary_id }}">
                                {{ $department->name }}
                            </option>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

            <!-- Secretaria com Maior Gasto -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col">
                    <p class="text-sm text-gray-500">Maior Gasto - Secretaria</p>
                    <p class="text-lg font-bold" id="topSecretaryName">{{ $topSecretary->name }}</p>
                    <p class="text-xl" id="topSecretaryAmount">
                        R$ {{ number_format($topSecretary->expenses->sum('amount'), 2, ',', '.') }}
                    </p>
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
                <h2 class="text-lg font-semibold">Distribuição de Gastos por Secretaria/Departamento</h2>
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

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Evolução Mensal</h2>
            <div id="timeline" class="h-96"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Gráfico de Barras -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Despesas por Departamento</h2>
                <div id="barChart" class="h-96"></div>
            </div>
            <!-- Gráfico de Velocímetro (Gauge) -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Desempenho Orçamentário</h2>
                <div id="gaugeChart" class="h-96"></div>
            </div>
        </div>

        <!-- Tabela Detalhada -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Detalhamento por Secretaria</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Secretaria
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Gasto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            % do Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Variação Mensal
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($secretaries as $secretary)
                        <tr class="hover:bg-gray-50 cursor-pointer"
                            onclick="showDepartmentDetails({{ $secretary->id }})">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $secretary->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                R$ {{ number_format($secretary->expenses->sum('amount'), 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $totalExpenses > 0 ? number_format(($secretary->expenses->sum('amount') / $totalExpenses) * 100, 1) : 0 }}
                                %
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $currentMonth = $secretary->expenses->filter(function($expense) {
                                        return $expense->expense_date->format('Y-m') === now()->format('Y-m');
                                    })->sum('amount');

                                    $lastMonth = $secretary->expenses->filter(function($expense) {
                                        return $expense->expense_date->format('Y-m') === now()->subMonth()->format('Y-m');
                                    })->sum('amount');

                                    $variation = $lastMonth != 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
                                @endphp
                                <span class="{{ $variation >= 0 ? 'text-red-500' : 'text-green-500' }}">
                                    {{ number_format(abs($variation), 1) }}%
                                    {{ $variation >= 0 ? '↑' : '↓' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
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
    {{-- Importa o arquivo principal do dashboard (resources/js/dashboard/index.js) via Vite --}}
    @vite('resources/js/dashboard/index.js')

    <script>
        /**
         * Função para limpar todos os filtros
         */
        function clearFilters() {
            // Limpar seleção de secretaria e departamento
            $('#secretary').val('');
            $('#department').val('');
            $('#expenseType').val('');
            $('#department option').show();

            // Resetar daterangepicker para o mês atual
            const picker = $('#dateRange').data('daterangepicker');
            if (picker) {
                const startDate = moment().startOf('month');
                const endDate = moment().endOf('month');
                picker.setStartDate(startDate);
                picker.setEndDate(endDate);
                $('#dateRange').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
            }

            // Atualizar o dashboard
            updateDashboardData(moment().startOf('month'), moment().endOf('month'));
        }

        /**
         * Função para mostrar detalhes de um departamento
         */
        function showDepartmentDetails(secretaryId) {
            // Selecionar a secretaria no filtro
            $('#secretary').val(secretaryId).trigger('change');

            // Rolar até o topo da página
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
    </script>
@endpush
