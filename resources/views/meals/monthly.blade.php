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
                            <div
                                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif

                        <!-- Alerta de erro -->
                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                 role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
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
                                            <option
                                                value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
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
                                            <option
                                                value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $name }}</option>
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

                        <!-- Mensagem explicativa para líderes de escola -->
                        @if(!$showAllSchools)
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <!-- Ícone de informação -->
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Você está vendo apenas os dados da sua escola. Para registrar despesas de
                                            merenda, entre em contato com a Secretaria de Educação ou com a Cantina
                                            Central.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Card com Cadastro de Despesa de Merenda - somente para usuários autorizados -->
                        @if($canAddExpenses)
                            <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Nova Despesa com Merenda Escolar</h3>

                                <form action="{{ route('monthly-meals.store-expense') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">Valor
                                                da Despesa</label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="amount"
                                                       id="amount"
                                                       value="{{ old('amount') }}"
                                                       class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                                       placeholder="0,00">
                                            </div>
                                            @error('amount')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="expense_date" class="block text-sm font-medium text-gray-700">
                                                Data da Despesa
                                            </label>
                                            <input type="date"
                                                   name="expense_date"
                                                   id="expense_date"
                                                   value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md">
                                            @error('expense_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label for="observation" class="block text-sm font-medium text-gray-700">
                                                Observações (opcional)
                                            </label>
                                            <textarea
                                                name="observation"
                                                id="observation"
                                                rows="2"
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md">{{ old('observation') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <button type="submit"
                                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Registrar Nova Despesa
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <!-- Card com Resumo da Merenda do Mês -->
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo da Merenda
                                - {{ $months[$month] ?? '' }}/{{ $year }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Valor
                                        Total da Merenda</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input type="text"
                                               readonly
                                               value="{{ number_format($totalMerendaMes, 2, ',', '.') }}"
                                               class="bg-gray-100 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Soma de todas as despesas de merenda no mês.
                                    </p>
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
                                    <p class="mt-1 text-xs text-gray-500">
                                        Dados obtidos do cadastro de matrículas.
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Valor por Aluno</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">R$</span>
                                        </div>
                                        <input type="text"
                                               readonly
                                               value="{{ number_format($valorPorAluno, 2, ',', '.') }}"
                                               class="bg-gray-100 block w-full pl-10 px-3 py-2 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Calculado com base nas despesas e no total de alunos.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Despesas do Mês Atual -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Despesas de Merenda - {{ $months[$month] ?? '' }}/{{ $year }}
                                </h3>
                            </div>

                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Observação
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @if(count($recentExpenses) > 0)
                                        @foreach($recentExpenses as $expense)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $expense->expense_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                    {{ $expense->observation ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                Nenhuma despesa registrada neste mês.
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tabela com o rateio por escola -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    @if($showAllSchools)
                                        Rateio da Merenda por Escola - {{ $months[$month] ?? '' }}/{{ $year }}
                                    @else
                                        Merenda da Escola - {{ $months[$month] ?? '' }}/{{ $year }}
                                    @endif
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
                                    @if($showAllSchools)
                                        <tfoot class="bg-gray-50">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                Total Geral
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($totalAlunos, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                R$ {{ number_format($totalMerendaMes, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                100%
                                            </td>
                                        </tr>
                                        </tfoot>
                                    @endif
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
