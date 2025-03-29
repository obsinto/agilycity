@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard do Departamento</h1>
                <h2 class="text-lg text-gray-600">{{ $department['name'] }} -
                    Secretaria: {{ $department['secretary'] }}</h2>
            </div>

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

        <!-- Status do departamento -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Status de Fechamento - {{ $monthName }}/{{ $year }}</h2>

            <div class="flex flex-wrap md:flex-nowrap">
                <div class="w-full md:w-1/2 md:pr-6">
                    <div class="flex items-center mb-4">
                        @if($department['is_closed'])
                            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-md flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Mês Fechado</span>
                            </div>
                        @elseif($department['status'] == 'ready_to_close')
                            <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-md flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Pronto para Fechar</span>
                            </div>
                        @else
                            <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-md flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Pendente</span>
                            </div>
                        @endif
                    </div>

                    @if($department['is_closed'])
                        <div class="text-sm text-gray-600 mt-2">
                            <p>Fechado por: {{ $department['submitted_by'] }}</p>
                            <p>Data: {{ $department['submitted_at']->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif

                    @if(!$department['is_closed'] && $department['can_close'])
                        <form action="{{ route('compliance.close-month') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="department_id" value="{{ $department['id'] }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Fechar Mês
                            </button>
                        </form>
                    @endif
                </div>

                <div class="w-full md:w-1/2 mt-4 md:mt-0 md:pl-6 md:border-l border-gray-200">
                    <h3 class="font-medium mb-2">Pendências</h3>

                    @if($department['missing_categories_count'] > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-red-600">Categorias de despesa pendentes:</h4>
                            <ul class="list-disc ml-5 text-sm text-gray-600">
                                @foreach($department['missing_categories'] as $category)
                                    <li>{{ $category }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($department['missing_fixed_expenses_count'] > 0)
                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-red-600">Despesas fixas pendentes:</h4>
                            <ul class="list-disc ml-5 text-sm text-gray-600">
                                @foreach($department['missing_fixed_expenses'] as $expense)
                                    <li>{{ $expense['name'] }} -
                                        R$ {{ number_format($expense['amount'], 2, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($department['missing_categories_count'] == 0 && $department['missing_fixed_expenses_count'] == 0)
                        <p class="text-sm text-green-600">Sem pendências para este mês.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lista de despesas do mês -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Despesas do Mês</h2>

                <a href="{{ route('expenses.create') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Nova Despesa
                </a>
            </div>

            @if($expenses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoria
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descrição
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($expenses as $expense)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $expense->expense_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $expense->expenseType->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $expense->description }}
                                    @if($expense->fixed_expense_id)
                                        <span class="text-xs text-blue-600">[Despesa Fixa]</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if(!$department['is_closed'])
                                        <a href="{{ route('expenses.edit', $expense->id) }}"
                                           class="text-blue-600 hover:text-blue-900 mr-3">Editar</a>

                                        <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Tem certeza que deseja excluir esta despesa?')">
                                                Excluir
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expenses->appends(request()->except('page'))->links() }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    Nenhuma despesa registrada para este mês.
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Override (se o usuário tiver permissão) -->
    @if(auth()->user()->hasRole('mayor') || auth()->user()->hasRole('secretary') || auth()->user()->hasRole('education_secretary'))
        <div id="overrideModal"
             class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 id="modalTitle" class="text-lg font-medium mb-4">Sobrescrever Status</h3>

                <form action="{{ route('compliance.override-status') }}" method="POST">
                    @csrf
                    <input type="hidden" id="modal_department_id" name="department_id" value="{{ $department['id'] }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-select">
                            <option value="open" {{ !$department['is_closed'] ? 'selected' : '' }}>Aberto</option>
                            <option value="closed" {{ $department['is_closed'] ? 'selected' : '' }}>Fechado</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Justificativa</label>
                        <textarea name="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-textarea"
                                  required></textarea>
                        <p class="mt-1 text-xs text-gray-500">Explique o motivo da alteração manual do status.</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-md">Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <button
            type="button"
            onclick="openOverrideModal()"
            class="fixed bottom-6 right-6 bg-blue-600 text-white rounded-full p-3 shadow-lg hover:bg-blue-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>

        <script>
            function openOverrideModal() {
                document.getElementById('modalTitle').textContent = `Sobrescrever Status: {{ $department['name'] }}`;
                document.getElementById('overrideModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('overrideModal').classList.add('hidden');
            }
        </script>
    @endif
@endsection
