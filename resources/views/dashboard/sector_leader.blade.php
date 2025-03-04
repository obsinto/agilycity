@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard do Líder de Setor</h1>

        <div>
            <h2 class="text-xl font-semibold mb-3">Informações do Departamento</h2>
            <div class="bg-gray-100 p-4 rounded">
                <p><strong>Departamento:</strong> {{ $department->name }}</p>
                <p><strong>Secretaria:</strong> {{ $department->secretary->name }}</p>
            </div>
        </div>
    </div>
@endsection
