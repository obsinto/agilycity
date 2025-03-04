@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-2xl font-bold mb-4">Gerenciar Departamentos</h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lista de Departamentos -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Departamentos Atuais</h3>

                    @if($departments->isEmpty())
                        <p class="text-gray-500">Nenhum departamento cadastrado.</p>
                    @else
                        @foreach($departments as $department)
                            <div class="mb-4 p-4 border rounded">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium">{{ $department->name }}</h4>
                                        @if($department->description)
                                            <p class="text-sm text-gray-600">{{ $department->description }}</p>
                                        @endif
                                    </div>
                                    <form method="POST"
                                          action="{{ route('secretary.departments.delete', $department->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800"
                                                onclick="return confirm('Tem certeza que deseja remover este departamento?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Formulário de Criação -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Adicionar Departamento</h3>

                    <form method="POST" action="{{ route('secretary.departments.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Departamento
                            </label>
                            <input type="text" name="name"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição (opcional)
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Adicionar Departamento
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
