<!-- meals/monthly.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Controle de Merenda Mensal') }}
        </h2>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <!-- Alerta de sucesso -->
                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                 role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif

                        <!-- Formulário de filtro -->
                        <form action="{{ route('monthly-meals.index') }}" method="GET" class="mb-6">
                            <div class="flex flex-wrap items-end space-x-4">
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700">Ano</label>
                                    <select id="year"
                                            name="year"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @for ($y = now()->year + 1; $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label for="month" class="block text-sm font-medium text-gray-700">Mês</label>
                                    <select id="month"
                                            name="month"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        @php
                                            $months = [
                                                1 => 'Janeiro',
                                                2 => 'Fevereiro',
                                                3 => 'Março',
                                                4 => 'Abril',
                                                5 => 'Maio',
                                                6 => 'Junho',
                                                7 => 'Julho',
                                                8 => 'Agosto',
                                                9 => 'Setembro',
                                                10 => 'Outubro',
                                                11 => 'Novembro',
                                                12 => 'Dezembro'
                                            ];
                                        @endphp

                                        @foreach ($months as $key => $name)
                                            <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <button type="submit"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Card com Cadastro de Valor Mensal -->
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Cadastro de Valor Mensal da Merenda</h3>

                            <form action="{{ route('monthly-meals.store') }}" method="POST">
                                @csrf

                                <input type="hidden" name="year" value="{{ $year }}">
                                <input type="hidden" name="month" value="{{ $month }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="total_amount" class="block text-sm font-medium text-gray-700">Valor
                                            Total da Merenda</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">R$</span>
                                            </div>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="total_amount"
                                                   id="total_amount"
                                                   value="{{ old('total_amount', $monthlyMeal->total_amount ?? '') }}"
                                                   class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                                   placeholder="0,00">
                                        </div>
                                        @error('total_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total de Alunos
                                            Matriculados</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                   readonly
                                                   value="{{ number_format($totalAlunos, 0, ',', '.') }}"
                                                   class="bg-gray-100 block w-full px-3 py-2 sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Valor por Aluno</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">R$</span>
                                            </div>
                                            <input type="text"
                                                   readonly
                                                   value="{{ number_format($valorPorAluno, 2, ',', '.') }}"
                                                   class="bg-gray-100 block w-full pl-10 px-3 py-2 sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button type="submit"
                                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        {{ $monthlyMeal ? 'Atualizar Valor' : 'Cadastrar Valor' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tabela com o rateio por escola -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Rateio da Merenda por Escola - {{ $months[$month] ?? '' }}/{{ $year }}
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Distribuição proporcional ao número de alunos matriculados
                                </p>
                            </div>

                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Escola
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total de Alunos
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor Proporcional
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            % do Total
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @if(count($schoolsData) > 0)
                                        @foreach($schoolsData as $school)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $school['department'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ number_format($school['students_count'], 0, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    R$ {{ number_format($school['custo_proporcional'], 2, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($totalAlunos > 0)
                                                        {{ number_format(($school['students_count'] / $totalAlunos) * 100, 2, ',', '.') }}
                                                        %
                                                    @else
                                                        0,00%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4"
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                Nenhuma escola encontrada.
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Total Geral
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ number_format($totalAlunos, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            R$ {{ number_format($monthlyMeal->total_amount ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            100%
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Meses cadastrados -->
                        @if(count($availableMonths) > 0)
                            <div class="mt-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Meses Cadastrados</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($availableMonths as $mealRecord)
                                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                            <h4 class="font-medium">{{ $months[$mealRecord->month] ?? '' }}
                                                /{{ $mealRecord->year }}</h4>
                                            <p class="text-sm text-gray-600">Valor Total:
                                                R$ {{ number_format($mealRecord->total_amount, 2, ',', '.') }}</p>
                                            <a href="{{ route('monthly-meals.index', ['year' => $mealRecord->year, 'month' => $mealRecord->month]) }}"
                                               class="text-blue-600 text-sm underline mt-2 inline-block">Visualizar</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
