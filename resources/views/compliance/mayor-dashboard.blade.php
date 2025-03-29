@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard de Compliance</h1>

            <!-- Seletor de ano e mês -->
            <div class="flex space-x-4">
                <form action="{{ route('compliance.dashboard') }}" method="GET" class="flex space-x-2">
                    <select name="month" class="form-select rounded-md shadow-sm">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="form-select rounded-md shadow-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Status geral -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Status Geral de Fechamento - {{ $monthName }}/{{ $year }}</h2>

            <div class="flex items-center mb-2">
                <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                    <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $overallPercentage }}%"></div>
                </div>
                <span class="text-sm font-medium">{{ number_format($overallPercentage, 1) }}%</span>
            </div>

            <div class="text-sm text-gray-600">
                {{ $closedDepartments }} de {{ $totalDepartments }} departamentos fechados
            </div>
        </div>

        <!-- Lista de secretarias -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Secretarias</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($secretariesData as $secretary)
                    <div
                        class="border rounded-lg p-4 {{ $secretary['status'] == 'complete' ? 'border-green-500' : 'border-yellow-500' }}">
                        <h3 class="font-medium text-lg">{{ $secretary['name'] }}</h3>

                        <div class="mt-3">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                         style="width: {{ $secretary['completion_percentage'] }}%"></div>
                                </div>
                                <span
                                    class="ml-2 text-sm">{{ number_format($secretary['completion_percentage'], 1) }}%</span>
                            </div>

                            <div class="mt-1 text-sm text-gray-600">
                                {{ $secretary['closed_departments'] }}/{{ $secretary['total_departments'] }}
                                departamentos
                            </div>
                        </div>

                        @if($secretary['status'] == 'complete')
                            <div class="mt-2 text-sm text-green-600 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                Completo
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

                        <a href="{{ route('compliance.secretary-details', ['secretary' => $secretary['id'], 'year' => $year, 'month' => $month]) }}"
                           class="mt-3 text-sm text-blue-600 hover:underline block">
                            Ver detalhes →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
