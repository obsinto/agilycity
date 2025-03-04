@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Tetos de Gastos</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('spending-caps.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Adicionar
            Teto</a>

        <table class="min-w-full mt-4 bg-white shadow rounded">
            <thead>
            <tr>
                <th class="px-4 py-2 border">Secretaria</th>
                <th class="px-4 py-2 border">Tipo de Despesa</th>
                <th class="px-4 py-2 border">Valor do Teto</th>
                <th class="px-4 py-2 border">Ações</th>
            </tr>
            </thead>
            <tbody>
            @foreach($spendingCaps as $cap)
                <tr>
                    <td class="px-4 py-2 border">{{ $cap->secretary->name }}</td>
                    <td class="px-4 py-2 border">
                        {{ $cap->expenseType ? $cap->expenseType->name : 'Teto Geral' }}
                    </td>
                    <td class="px-4 py-2 border">R$ {{ number_format($cap->cap_value, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 border">
                        <a href="{{ route('spending-caps.edit', $cap) }}"
                           class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('spending-caps.destroy', $cap) }}"
                              method="POST"
                              class="inline-block"
                              onsubmit="return confirm('Tem certeza?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">Remover</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
