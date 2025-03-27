<!-- reports/cantina.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-xl font-bold mb-4">Relatório Cantina - {{$month}}/{{$year}}</h1>

        <p class="mb-2">Total de Merenda no Mês:
            <strong>R$ {{ number_format($totalMerendaMes, 2, ',', '.') }}</strong>
        </p>
        <p class="mb-2">
            @if($showAllSchools)
                Total de Alunos (todas as escolas):
            @else
                Total de Alunos:
            @endif
            <strong>{{ number_format($totalAlunos, 0, ',', '.') }}</strong>
        </p>
        <p class="mb-4">Custo por Aluno:
            <strong>R$ {{ is_numeric($custoPorAluno) ? number_format($custoPorAluno, 2, ',', '.') : '0,00' }}</strong>
            <span class="text-sm text-gray-600">
        (Total Merenda ÷ Total Alunos do Sistema)
        @if(!$showAllSchools)
                    <span class="text-blue-600">*</span>
                @endif
    </span>
        </p>

        <!-- Se não mostrar todas as escolas, adicione esta nota explicativa -->
        {{--        @if(!$showAllSchools)--}}
        {{--            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">--}}
        {{--                <div class="flex">--}}
        {{--                    <div class="flex-shrink-0">--}}
        {{--                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"--}}
        {{--                             fill="currentColor">--}}
        {{--                            <path fill-rule="evenodd"--}}
        {{--                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"--}}
        {{--                                  clip-rule="evenodd"/>--}}
        {{--                        </svg>--}}
        {{--                    </div>--}}
        {{--                    <div class="ml-3">--}}
        {{--                        <p class="text-sm text-blue-700">--}}
        {{--                            <span class="text-blue-600">*</span> O custo por aluno é calculado com base no total de--}}
        {{--                            alunos de todas as escolas do sistema para garantir um valor uniforme.--}}
        {{--                        </p>--}}
        {{--                        <p class="text-sm text-blue-700 mt-1">--}}
        {{--                            Você está vendo apenas os dados da sua escola. Para visualizar todas as escolas, entre em--}}
        {{--                            contato com a Secretaria de Educação.--}}
        {{--                        </p>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        @endif--}}

        <!-- Mensagem explicativa para líderes de escola -->
        @if(!$showAllSchools)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Ícone de informação -->
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Você está vendo apenas os dados da sua escola. Para visualizar todas as escolas, entre em
                            contato com a Secretaria de Educação.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <table class="min-w-full border">
            <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2 text-left">Escola / Departamento</th>
                <th class="border px-4 py-2 text-center">Alunos</th>
                <th class="border px-4 py-2 text-right">Custo (R$)</th>
                @if($showAllSchools)
                    <th class="border px-4 py-2 text-right">% do Total</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($deptCosts as $row)
                <tr>
                    <td class="border px-4 py-2">
                        {{ $row['department'] }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        {{ number_format($row['students_count'], 0, ',', '.') }}
                    </td>
                    <td class="border px-4 py-2 text-right">
                        {{ number_format($row['custo_proporcional'], 2, ',', '.') }}
                    </td>
                    @if($showAllSchools)
                        <td class="border px-4 py-2 text-right">
                            @if($totalAlunos > 0)
                                {{ number_format(($row['students_count'] / $totalAlunos) * 100, 2, ',', '.') }}%
                            @else
                                0,00%
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
            @if($showAllSchools && count($deptCosts) > 1)
                <tfoot class="bg-gray-100">
                <tr>
                    <td class="border px-4 py-2 font-bold">Total Geral</td>
                    <td class="border px-4 py-2 text-center font-bold">{{ number_format($totalAlunos, 0, ',', '.') }}</td>
                    <td class="border px-4 py-2 text-right font-bold">
                        R$ {{ number_format($totalMerendaMes, 2, ',', '.') }}</td>
                    <td class="border px-4 py-2 text-right font-bold">100%</td>
                </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
