@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Editar Teto de Gastos</h1>

        <form action="{{ route('spending-caps.update', $spendingCap) }}"
              method="POST"
              class="bg-white p-6 rounded shadow-md">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block mb-1">Secretaria</label>
                <select name="secretary_id" class="w-full border rounded p-2">
                    @foreach($secretaries as $secretary)
                        <option value="{{ $secretary->id }}" {{ $spendingCap->secretary_id == $secretary->id ? 'selected' : '' }}>
                            {{ $secretary->name }}
                        </option>
                    @endforeach
                </select>
                @error('secretary_id') <span class="text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block mb-1">Tipo de Despesa (opcional)</label>
                <select name="expense_type_id" class="w-full border rounded p-2">
                    <option value="">-- Teto Geral --</option>
                    @foreach($expenseTypes as $expenseType)
                        <option value="{{ $expenseType->id }}" {{ $spendingCap->expense_type_id == $expenseType->id ? 'selected' : '' }}>
                            {{ $expenseType->name }}
                        </option>
                    @endforeach
                </select>
                @error('expense_type_id') <span class="text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block mb-1">Valor do Teto</label>
                <input type="text"
                       name="cap_value"
                       value="{{ old('cap_value', $spendingCap->cap_value) }}"
                       class="w-full border rounded p-2">
                @error('cap_value') <span class="text-red-600">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Atualizar</button>
        </form>
    </div>
@endsection
