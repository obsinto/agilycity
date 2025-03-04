@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Gerenciar Secretários</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <!-- Secretarias -->
            <div>
                <h3 class="text-xl font-semibold mb-3">Secretarias</h3>
                <table class="w-full bg-gray-50 border">
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Secretaria</th>
                        <th class="p-2 border">Secretário Atual</th>
                        <th class="p-2 border">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($secretaries as $secretary)
                        <tr>
                            <td class="p-2 border">{{ $secretary->name }}</td>
                            <td class="p-2 border">
                                {{ $secretary->secretary?->name ?? 'Não definido' }}
                            </td>
                            <td class="p-2 border text-center">
                                @if($secretary->secretary)
                                    <form action="{{ route('secretaries.remove', $secretary->secretary->id) }}"
                                          method="POST"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline">Remover</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Associar Secretário -->
            <div>
                <h3 class="text-xl font-semibold mb-3">Associar Secretário</h3>
                <form action="{{ route('secretaries.associate') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-2">Usuário</label>
                        <select name="user_id" class="w-full p-2 border rounded" required>
                            <option value="">Selecione um usuário</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2">Secretaria</label>
                        <select name="secretary_id" class="w-full p-2 border rounded" required>
                            <option value="">Selecione uma secretaria</option>
                            @foreach($secretaries as $secretary)
                                @if(!$secretary->secretary)
                                    <option value="{{ $secretary->id }}">{{ $secretary->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Associar Secretário
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
