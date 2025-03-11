@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-6 shadow rounded"
         x-data="enrollmentPage()">
        <h2 class="text-xl font-bold mb-4">
            Cadastrar/Atualizar Alunos - {{ $department->name }}
        </h2>

        <!-- FORMULÁRIO DE CADASTRO (novos registros) -->
        <form action="{{ route('enrollments.store') }}" method="POST" class="mb-8">
            @csrf
            <!-- Campo Ano -->
            <div class="mb-4">
                <label for="year" class="block text-sm font-medium text-gray-700">Ano</label>
                <input type="number" name="year" id="year"
                       value="{{ old('year', now()->year) }}"
                       class="mt-1 block w-48 border border-gray-300 rounded p-1">
                @error('year')
                <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo Mês -->
            <div class="mb-4">
                <label for="month" class="block text-sm font-medium text-gray-700">Mês</label>
                <select name="month" id="month"
                        class="mt-1 block w-48 border border-gray-300 rounded p-1">
                    @for($m = 1; $m <= 12; $m++)
                        @php
                            $monthName = \Carbon\Carbon::create()
                                ->month($m)
                                ->locale('pt_BR')
                                ->isoFormat('MMMM');
                            $monthName = ucfirst($monthName);
                        @endphp
                        <option value="{{ $m }}"
                                @if($m == old('month', now()->month)) selected @endif>
                            {{ $m }} - {{ $monthName }}
                        </option>
                    @endfor
                </select>
                @error('month')
                <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo Número de Alunos -->
            <div class="mb-4">
                <label for="students_count" class="block text-sm font-medium text-gray-700">
                    Nº de Alunos
                </label>
                <input type="number" name="students_count" id="students_count"
                       value="{{ old('students_count') }}"
                       class="mt-1 block w-48 border border-gray-300 rounded p-1">
                @error('students_count')
                <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Salvar
            </button>
        </form>

        <!-- HISTÓRICO DE ALUNOS (LISTA) -->
        <h3 class="text-lg font-semibold mb-2">
            Histórico de Alunos em {{ $department->name }}
        </h3>
        @if($enrollments->isEmpty())
            <p class="text-sm text-gray-500">
                Ainda não há registros de alunos para {{ $department->name }}.
            </p>
        @else
            <table class="min-w-full border">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border-r text-left">Ano</th>
                    <th class="px-4 py-2 border-r text-left">Mês</th>
                    <th class="px-4 py-2 text-left">Qtd. Alunos</th>
                    <th class="px-4 py-2 text-center">Ações</th>
                </tr>
                </thead>
                <tbody>
                @foreach($enrollments as $enroll)
                    @php
                        $monthNameHist = \Carbon\Carbon::create()
                            ->month($enroll->month)
                            ->locale('pt_BR')
                            ->isoFormat('MMMM');
                        $monthNameHist = ucfirst($monthNameHist);
                    @endphp
                    <tr>
                        <td class="border px-4 py-2">
                            {{ $enroll->year }}
                        </td>
                        <td class="border px-4 py-2">
                            {{ $enroll->month }} - {{ $monthNameHist }}
                        </td>
                        <td class="border px-4 py-2">
                            {{ $enroll->students_count }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            <!-- Botão Editar abre modal -->
                            <button type="button"
                                    class="inline-block bg-yellow-400 text-white px-3 py-1 rounded mr-2"
                                    @click="
                                openEditModal(
                                    {{ $enroll->id }},
                                    {{ $enroll->year }},
                                    {{ $enroll->month }},
                                    {{ $enroll->students_count }}
                                )
                            ">
                                Editar
                            </button>

                            <!-- Excluir (form DELETE) -->
                            <form action="{{ route('enrollments.destroy', $enroll->id) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('Tem certeza que deseja excluir este registro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 text-white px-3 py-1 rounded">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <!-- MODAL DE EDIÇÃO (Alpine.js) -->
        <div
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
            x-show="editModalOpen"
            x-transition
            style="display: none;"
        >
            <div class="bg-white w-1/2 p-6 rounded shadow"
                 @click.away="editModalOpen=false">
                <h2 class="text-xl font-bold mb-4">Editar Matrícula</h2>
                <form :action="updateUrl" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Campo Ano -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Ano</label>
                        <input type="number"
                               class="mt-1 block w-48 border border-gray-300 rounded p-1"
                               name="year"
                               x-model="editForm.year">
                    </div>

                    <!-- Campo Mês -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Mês</label>
                        <select class="mt-1 block w-48 border border-gray-300 rounded p-1"
                                name="month"
                                x-model="editForm.month">
                            <template x-for="m in 12">
                                <option :value="m" x-text="formatMonth(m)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Campo Nº de Alunos -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nº de Alunos</label>
                        <input type="number"
                               class="mt-1 block w-48 border border-gray-300 rounded p-1"
                               name="students_count"
                               x-model="editForm.students_count">
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded">
                            Atualizar
                        </button>
                        <button type="button"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded"
                                @click="editModalOpen = false">
                            Fechar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>


        // Precisamos de Alpine.js para funcionar.
        // Se já tiver Alpine no seu layout, OK.
        // Se não, inclua:


        function enrollmentPage() {
            return {
                editModalOpen: false,
                updateUrl: '',
                editForm: {
                    year: '',
                    month: '',
                    students_count: '',
                },
                openEditModal(enrollmentId, year, month, students_count) {
                    // Setamos as variáveis do formulário
                    this.editForm.year = year;
                    this.editForm.month = month;
                    this.editForm.students_count = students_count;
                    // Monta a URL para o form PUT
                    this.updateUrl = `/enrollments/${enrollmentId}`;
                    // Exibe o modal
                    this.editModalOpen = true;
                },
                formatMonth(m) {
                    // Exemplo simples: exibe m + " - " + mes em português
                    // Em Alpine.js puro, sem import de Carbon, vamos simplificar
                    // Caso queira algo mais sofisticado, busque mes em array
                    const nomesMes = [
                        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                    ];
                    return m + " - " + nomesMes[m - 1];
                }
            }
        }
    </script>
@endsection
