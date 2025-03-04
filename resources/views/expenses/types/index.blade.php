@extends('layouts.app')

@section('content')
    <div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Tipos de Despesas</h2>
                <button onclick="document.getElementById('newTypeModal').classList.remove('hidden')"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Novo Tipo
                </button>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Descrição
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($types as $type)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $type->name }}</td>
                        <td class="px-6 py-4">{{ $type->description }}</td>
                        <td class="px-6 py-4">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $type->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $type->active ? 'Ativo' : 'Inativo' }}
                        </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="editType({{ $type->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                Editar
                            </button>
                            <form action="{{ route('expense-types.destroy', $type) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Tem certeza?')">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Index -->
    <div id="newTypeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="relative p-8 bg-white w-full max-w-md m-auto flex-col flex rounded-lg">
            <div class="text-lg font-bold mb-4">Novo Tipo de Despesa</div>
            <form action="{{ route('expense-types.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text"
                           name="name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="description"
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button"
                            onclick="document.getElementById('newTypeModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
