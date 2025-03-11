@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-xl font-bold mb-4">Relatório Cantina - {{$month}}/{{$year}}</h1>

        <p class="mb-2">Total de Merenda no Mês:
            <strong>R$ {{ number_format($totalMerendaMes, 2, ',', '.') }}</strong>
        </p>
        <p class="mb-2">Total de Alunos (todas as escolas):
            <strong>{{ $totalAlunos }}</strong>
        </p>
        <p class="mb-4">Custo por Aluno:
            <strong>R$ {{ number_format($custoPorAluno, 2, ',', '.') }}</strong>
        </p>

        <table class="min-w-full border">
            <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2 text-left">Escola / Departamento</th>
                <th class="border px-4 py-2 text-center">Alunos</th>
                <th class="border px-4 py-2 text-right">Custo (R$)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($deptCosts as $row)
                <tr>
                    <td class="border px-4 py-2">
                        {{ $row['department'] }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        {{ $row['students_count'] }}
                    </td>
                    <td class="border px-4 py-2 text-right">
                        {{ number_format($row['custo_proporcional'], 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
