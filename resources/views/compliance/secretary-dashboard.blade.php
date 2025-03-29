@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard da Secretaria</h1>
                <h2 class="text-lg text-gray-600">{{ $secretary->name }}</h2>
            </div>

            <!-- Seletor de ano e mês -->
            <div class="flex space-x-4">
                <form action="{{ route('compliance.secretary-details', ['secretary' => $secretary->id]) }}" method="GET"
                      class="flex space-x-2">
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
            <h2 class="text-xl font-semibold mb-4">Status de Fechamento - {{ $monthName }}/{{ $year }}</h2>

            <div class="flex items-center mb-2">
                <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                    <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                </div>
                <span class="text-sm font-medium">{{ number_format($completionPercentage, 1) }}%</span>
            </div>

            <div class="text-sm text-gray-600">
                {{ $closedDepartments }} de {{ $totalDepartments }} departamentos fechados
            </div>
        </div>

        <!-- Lista de departamentos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Departamentos</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Departamento
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fechado por
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data de Fechamento
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pendências
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($departmentsData as $dept)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $dept['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($dept['is_closed'])
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Fechado
                                    </span>
                                @elseif($dept['status'] == 'ready_to_close')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Pronto para fechar
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dept['submitted_by'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dept['submitted_at'] ? $dept['submitted_at']->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($dept['missing_categories_count'] > 0)
                                    <div class="text-red-600">
                                        {{ $dept['missing_categories_count'] }} categorias pendentes
                                        <span class="group relative cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div
                                                class="hidden group-hover:block absolute z-10 w-64 bg-black text-white text-xs rounded p-2 mt-1">
                                                <ul class="list-disc pl-4">
                                                    @foreach($dept['missing_categories'] as $category)
                                                        <li>{{ $category }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                @endif

                                @if($dept['missing_fixed_expenses_count'] > 0)
                                    <div class="text-red-600 mt-1">
                                        {{ $dept['missing_fixed_expenses_count'] }} despesas fixas pendentes
                                        <span class="group relative cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div
                                                class="hidden group-hover:block absolute z-10 w-64 bg-black text-white text-xs rounded p-2 mt-1">
                                                <ul class="list-disc pl-4">
                                                    @foreach($dept['missing_fixed_expenses'] as $expense)
                                                        <li>{{ $expense['name'] }} - R$ {{ number_format($expense['amount'], 2, ',', '.') }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                @endif

                                @if($dept['missing_categories_count'] == 0 && $dept['missing_fixed_expenses_count'] == 0)
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(!$dept['is_closed'] && $dept['status'] == 'ready_to_close')
                                    <form action="{{ route('compliance.close-month') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="department_id" value="{{ $dept['id'] }}">
                                        <input type="hidden" name="year" value="{{ $year }}">
                                        <input type="hidden" name="month" value="{{ $month }}">
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">Fechar mês
                                        </button>
                                    </form>
                                @endif

                                <!-- Opção de Override (apenas para prefeito e secretários) -->
                                @if(auth()->user()->hasRole('mayor') || auth()->user()->hasRole('secretary') || auth()->user()->hasRole('education_secretary'))
                                    <button
                                        type="button"
                                        class="text-gray-600 hover:text-gray-900 ml-3"
                                        onclick="openOverrideModal('{{ $dept['id'] }}', '{{ $dept['name'] }}', '{{ $dept['is_closed'] ? 'closed' : 'open' }}')">
                                        Sobrescrever
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Override -->
    <div id="overrideModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 id="modalTitle" class="text-lg font-medium mb-4">Sobrescrever Status</h3>

            <form action="{{ route('compliance.override-status') }}" method="POST">
                @csrf
                <input type="hidden" id="modal_department_id" name="department_id">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-select">
                        <option value="open">Aberto</option>
                        <option value="closed">Fechado</option>
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
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-md">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openOverrideModal(departmentId, departmentName, currentStatus) {
            document.getElementById('modalTitle').textContent = `Sobrescrever Status: ${departmentName}`;
            document.getElementById('modal_department_id').value = departmentId;

            // Pré-selecionar o status oposto
            const statusSelect = document.querySelector('select[name="status"]');
            statusSelect.value = currentStatus === 'closed' ? 'open' : 'closed';

            document.getElementById('overrideModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('overrideModal').classList.add('hidden');
        }
    </script>
@endsection
