@extends('layouts.app')

@section('content')
    <div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Despesas</h2>
                <button type="button" onclick="openModal()"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Nova Despesa
                </button>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Tabela de Despesas -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                            Departamento
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Valor
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Ações
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($expenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $expense->expenseType->name }}</td>
                            <td class="px-6 py-4">{{ $expense->department->name }}</td>
                            <td class="px-6 py-4">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <button onclick="viewExpense({{ $expense->id }})"
                                        class="text-blue-600 hover:text-blue-900">
                                    Ver Detalhes
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>

    <!-- Index Nova Despesa -->
    <div id="newExpenseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
        <div class="relative top-20 mx-auto p-5 border shadow-lg rounded-md bg-white max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Nova Despesa</h3>
                <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" id="expenseForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <select name="expense_type_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            required>
                        <option value="">Selecione...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->hasRole('secretary'))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select name="department_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                required>
                            <option value="">Selecione...</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Valor</label>
                    <input type="text"
                           id="amount"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                    <input type="hidden" id="amount_value" name="amount">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Data</label>
                    <input type="date"
                           name="expense_date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                </div>

{{--                <div class="mb-4">--}}
                {{--                    <label class="block text-sm font-medium text-gray-700">Nº Nota Fiscal</label>--}}
                {{--                    <input type="text"--}}
                {{--                           name="invoice_number"--}}
                {{--                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">--}}
                {{--                </div>--}}

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Observação</label>
                    <textarea name="observation"
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Anexo</label>
                    <input type="file"
                           name="attachment"
                           class="mt-1 block w-full">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/imask@7.5.0/dist/imask.js"></script>

    <script>
        function openModal() {
            const modal = document.getElementById('newExpenseModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal() {
            const modal = document.getElementById('newExpenseModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Fechar modal clicando fora
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('newExpenseModal');
            if (e.target === modal) {
                closeModal();
            }
        });

        // Máscara para o campo de valor
        const numberMask = IMask(document.getElementById('amount'), {
            mask: 'R$ num',
            blocks: {
                num: {
                    mask: Number,
                    scale: 2,
                    thousandsSeparator: '.',
                    padFractionalZeros: true,
                    radix: ',',
                    mapToRadix: ['.']
                }
            }
        });

        // Atualiza o valor real antes do envio do formulário
        document.getElementById('expenseForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Pega o valor puro (sem máscara) e atualiza o input hidden
            let value = numberMask.unmaskedValue;
            document.getElementById('amount_value').value = value;
            this.submit();
        });
    </script>
@endsection
