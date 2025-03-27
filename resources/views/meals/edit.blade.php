<!-- expenses/edit_meal.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Editar Despesa de Merenda Escolar') }}
        </h2>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <!-- Alerta de erro -->
                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                 role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <!-- Card com Edição de Despesa de Merenda -->
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Despesa #{{ $expense->id }}</h3>

                            <form action="{{ route('expenses.meal.update', $expense->id) }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="expense_type_id" class="block text-sm font-medium text-gray-700">
                                            Tipo de Despesa
                                        </label>
                                        <select id="expense_type_id"
                                                name="expense_type_id"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @foreach ($expenseTypes as $type)
                                                <option
                                                    value="{{ $type->id }}" {{ $expense->expense_type_id == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('expense_type_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700">
                                            Valor da Despesa
                                        </label>
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
                                                   value="{{ old('amount', $expense->amount) }}"
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
                                               value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md">
                                        @error('expense_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="attachment" class="block text-sm font-medium text-gray-700">
                                            Comprovante/Nota Fiscal (opcional)
                                        </label>
                                        @if($expense->attachment)
                                            <div class="mt-1 mb-2 flex items-center">
                                                <span class="text-sm text-gray-500 mr-2">Anexo atual:</span>
                                                <a href="{{ Storage::url($expense->attachment) }}"
                                                   target="_blank"
                                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                                    Ver anexo
                                                </a>
                                            </div>
                                        @endif
                                        <input type="file"
                                               name="attachment"
                                               id="attachment"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md">
                                        @error('attachment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">
                                            Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 2MB.
                                            Deixe em branco para manter o anexo atual.
                                        </p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="observation" class="block text-sm font-medium text-gray-700">
                                            Observações (opcional)
                                        </label>
                                        <textarea
                                            name="observation"
                                            id="observation"
                                            rows="3"
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-md">{{ old('observation', $expense->observation) }}</textarea>
                                        @error('observation')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center space-x-3">
                                    <button type="submit"
                                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Salvar Alterações
                                    </button>
                                    <a href="{{ route('expenses.meal.list') }}"
                                       class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
