@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Gerenciar Líderes de Setor</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                 role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-3">Líderes de Setor Atuais</h2>
                <table class="w-full bg-gray-50 border">
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Nome</th>
                        <th class="p-2 border">Departamento</th>
                        <th class="p-2 border">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($currentSectorLeaders as $leader)
                        <tr>
                            <td class="p-2 border">{{ $leader->name }}</td>
                            <td class="p-2 border">{{ $leader->department->name }}</td>
                            <td class="p-2 border text-center">
                                <form action="{{ route('sector-leaders.destroy', $leader->id) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-3">Cadastrar Líder de Setor</h2>
                <form action="{{ route('sector-leaders.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-2">Nome</label>
                        <input type="text" name="name" class="w-full p-2 border rounded" required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2">Email</label>
                        <input type="email" name="email" class="w-full p-2 border rounded" required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2">Departamento</label>
                        <select name="department_id" class="w-full p-2 border rounded" required>
                            <option value="">Selecione um departamento</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Cadastrar Líder de Setor
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
