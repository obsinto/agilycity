@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Análise Mensal de Alunos</h1>

        <!-- FILTROS -->
        <form method="GET" class="mb-6 flex items-end space-x-4">
            <!-- Campo Ano -->
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700">Ano</label>
                <select name="year" id="year"
                        class="mt-1 w-32 border border-gray-300 rounded p-1">
                    @php
                        $currentYear = now()->year;
                    @endphp
                    @for($y = $currentYear - 3; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Campo Escola -->
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">Escola</label>
                <select name="department_id" id="department_id"
                        class="mt-1 w-64 border border-gray-300 rounded p-1">
                    <option value="">Todas</option>
                    @foreach($allSchools as $school)
                        <option value="{{ $school->id }}"
                            {{ $departmentId == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botão Filtrar -->
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Filtrar
                </button>
            </div>
        </form>


        <!-- GRÁFICO -->
        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="text-lg font-bold mb-2">Gráfico de Alunos</h2>
            <div id="studentChart" style="height: 400px;"></div>
        </div>

        <!-- TABELA DETALHADA -->
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-lg font-bold mb-4">Detalhamento por Escola</h2>
            <table class="min-w-full border">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 border-r text-left">Escola</th>
                    @foreach($monthsLabels as $label)
                        <th class="px-3 py-2 border-r text-center">{{ $label }}</th>
                    @endforeach
                    <th class="px-3 py-2 text-center">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tableData as $row)
                    <tr>
                        <td class="border px-3 py-2">{{ $row['department'] }}</td>
                        @foreach($row['counts'] as $month => $count)
                            <td class="border px-3 py-2 text-center">{{ $count }}</td>
                        @endforeach
                        <td class="border px-3 py-2 text-center font-semibold">
                            {{ $row['yearly_sum'] }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Inclusão do ECharts (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartDom = document.getElementById('studentChart');
            var myChart = echarts.init(chartDom);

            var option = {
                tooltip: {trigger: 'axis'},
                legend: {},
                // Se houver mais de 1 escola, mostramos a legenda p/ distinguir as linhas
                xAxis: {
                    type: 'category',
                    data: @json($monthsLabels)
                },
                yAxis: {
                    type: 'value',
                    name: 'Alunos'
                },
                series: [
                    // Vamos montar cada objeto { name, type, data } com base no chartData
                    // Exemplo:
                    // { name: 'Escola X', type: 'line', data: [10, 12, 15, ...] }
                        @foreach($chartData as $serie)
                    {
                        name: "{{ $serie['name'] }}",
                        type: 'line',
                        smooth: true,
                        data: @json($serie['data']),
                    },
                    @endforeach
                ]
            };

            myChart.setOption(option);
        });
    </script>
@endpush
