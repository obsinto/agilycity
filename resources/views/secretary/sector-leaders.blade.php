<!-- resources/views/secretary/sector-leaders.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-2xl font-bold mb-4">Gestão de Líderes de Setor</h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lista de Departamentos -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Departamentos</h3>

                    @foreach($departments as $department)
                        <div class="mb-4 p-4 border rounded">
                            <h4 class="font-medium">{{ $department->name }}</h4>

                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Líderes atuais:</p>
                                @forelse($department->sectorLeaders as $leader)
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="text-sm">{{ $leader->name }}</span>
                                        <form method="POST"
                                              action="{{ route('secretary.remove-leader', $leader->id) }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 text-sm hover:underline">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">Nenhum líder associado</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Formulário de Associação -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Associar Líder</h3>

                    <form method="POST" action="{{ route('secretary.assign-leader') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Líder de Setor
                            </label>
                            <select name="leader_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecione um líder</option>
                                @foreach($availableLeaders as $leader)
                                    <option value="{{ $leader->id }}">{{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento
                            </label>
                            <select name="department_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecione um departamento</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Associar Líder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
