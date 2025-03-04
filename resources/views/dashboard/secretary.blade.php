@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Dashboard do Secretário</h1>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold mb-3">Departamentos da Secretaria</h2>
                <table class="w-full bg-gray-50 border">
                    <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Departamento</th>
                        <th class="p-2 border">Líderes de Setor</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($departments as $department)
                        <tr>
                            <td class="p-2 border">{{ $department->name }}</td>
                            <td class="p-2 border">
                                {{ $department->sectorLeaders->count() }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-3">Informações da Secretaria</h2>
                <div class="bg-gray-100 p-4 rounded">
                    <p><strong>Nome:</strong> {{ $secretary->name }}</p>
                    <p><strong>Total de Departamentos:</strong> {{ $departments->count() }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
