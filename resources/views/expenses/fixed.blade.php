@extends('layouts.app')

@section('content')
    <div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Despesas Fixas</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('expenses.index') }}"
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Despesas Regulares
                    </a>
                    <button type="button" onclick="openModal()"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Nova Despesa Fixa
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabela de Despesas Fixas -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                            Departamento
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Valor
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Início
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Fim</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status
                        </th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Ações
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($fixedExpenses as $expense)
                        <tr>
                            <td class="px-6 py-4">{{ $expense->name }}</td>
                            <td class="px-6 py-4">{{ $expense->expenseType->name }}</td>
                            <td class="px-6 py-4">{{ $expense->department->name }}</td>
                            <td class="px-6 py-4">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $expense->start_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $expense->end_date ? $expense->end_date->format('d/m/Y') : 'Indeterminado' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $expense->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $expense->status == 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="editFixedExpense({{ $expense->id }})"
                                        class="text-blue-600 hover:text-blue-900 mr-2">
                                    Editar
                                </button>
                                <button
                                    onclick="toggleStatus({{ $expense->id }}, '{{ $expense->status == 'active' ? 'inactive' : 'active' }}')"
                                    class="text-{{ $expense->status == 'active' ? 'red' : 'green' }}-600 hover:text-{{ $expense->status == 'active' ? 'red' : 'green' }}-900">
                                    {{ $expense->status == 'active' ? 'Desativar' : 'Ativar' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $fixedExpenses->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Nova Despesa Fixa -->
    <div id="fixedExpenseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
        <div class="relative top-20 mx-auto p-5 border shadow-lg rounded-md bg-white max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold" id="modalTitle">Nova Despesa Fixa</h3>
                <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('fixed-expenses.store') }}" method="POST" id="fixedExpenseForm">
                @csrf
                <div id="method-field"></div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nome da Despesa Fixa</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Despesa</label>
                    <select name="expense_type_id"
                            id="expense_type_id"
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
                                id="department_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                required>
                            <option value="">Selecione...</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="department_id" id="department_id"
                           value="{{ auth()->user()->department_id }}">
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Valor Mensal</label>
                    <input type="text"
                           id="amount"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                    <input type="hidden" id="amount_value" name="amount">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Data de Início</label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Data de Fim (opcional)</label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Deixe em branco para despesas por tempo indeterminado</p>
                </div>

                <div class="mb-4" id="status-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status"
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Observação</label>
                    <textarea name="observation"
                              id="observation"
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
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

    <!-- Form para ativar/desativar -->
    <form id="toggleStatusForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" id="toggle_status">
    </form>

    <script src="https://unpkg.com/imask@7.5.0/dist/imask.js"></script>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Nova Despesa Fixa';
            document.getElementById('fixedExpenseForm').action = "{{ route('fixed-expenses.store') }}";
            document.getElementById('method-field').innerHTML = '';
            document.getElementById('status-field').style.display = 'none';

            // Limpar o formulário
            document.getElementById('fixedExpenseForm').reset();

            const modal = document.getElementById('fixedExpenseModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal() {
            const modal = document.getElementById('fixedExpenseModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function editFixedExpense(id) {
            // Fetch dos dados da despesa fixa
            fetch(`/fixed-expenses/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Editar Despesa Fixa';
                    document.getElementById('fixedExpenseForm').action = `/fixed-expenses/${id}`;
                    document.getElementById('method-field').innerHTML = `<input type="hidden" name="_method" value="PUT">`;
                    document.getElementById('status-field').style.display = 'block';

                    // Preencher o formulário
                    document.getElementById('name').value = data.name;
                    document.getElementById('expense_type_id').value = data.expense_type_id;
                    document.getElementById('department_id').value = data.department_id;
                    document.getElementById('amount').value = `R$ ${parseFloat(data.amount).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    document.getElementById('amount_value').value = data.amount;
                    document.getElementById('start_date').value = data.start_date.split('T')[0];
                    document.getElementById('end_date').value = data.end_date ? data.end_date.split('T')[0] : '';
                    document.getElementById('status').value = data.status;
                    document.getElementById('observation').value = data.observation || '';

                    const modal = document.getElementById('fixedExpenseModal');
                    if (modal) {
                        modal.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados da despesa fixa:', error);
                });
        }

        function toggleStatus(id, newStatus) {
            if (confirm(`Deseja realmente ${newStatus === 'active' ? 'ativar' : 'desativar'} esta despesa fixa?`)) {
                const form = document.getElementById('toggleStatusForm');
                form.action = `/fixed-expenses/${id}`;
                document.getElementById('toggle_status').value = newStatus;
                form.submit();
            }
        }

        // Fechar modal clicando fora
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('fixedExpenseModal');
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
        document.getElementById('fixedExpenseForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Pega o valor puro (sem máscara) e atualiza o input hidden
            let value = numberMask.unmaskedValue;
            document.getElementById('amount_value').value = value;
            this.document.getElementById('amount_value').value = value;
            this.submit();
        });
    </script>
@endsection
