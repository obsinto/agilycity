<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use App\Models\MonthlyMeal;
use Illuminate\Http\Request;

class MonthlyMealController extends Controller
{
    /**
     * Exibe o formulário para cadastro e edição dos valores mensais de merenda.
     */
    public function index(Request $request)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // Verificar permissão
        if (!auth()->user()->can('manage monthly meals')) {
            abort(403, 'Acesso não autorizado');
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        // Buscar o registro do mês atual, se existir
        $monthlyMeal = MonthlyMeal::where('year', $year)
            ->where('month', $month)
            ->first();

        // Buscar todos os meses cadastrados para seleção
        $availableMonths = MonthlyMeal::select('year', 'month', 'total_amount')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Dados para o rateio (escolas e total de alunos)
        $totalAlunos = DepartmentEnrollment::where('year', $year)
            ->where('month', $month)
            ->whereHas('department', function ($q) {
                $q->where('is_school', true);
            })
            ->sum('students_count');

        // Valor por aluno (se existir registro para o mês)
        $valorPorAluno = 0;
        if ($monthlyMeal && $totalAlunos > 0) {
            $valorPorAluno = $monthlyMeal->total_amount / $totalAlunos;
        }

        // Escolas e seus alunos para o rateio
        $schools = Department::where('is_school', true)
            ->orderBy('name')
            ->get();

        $schoolsData = [];
        foreach ($schools as $school) {
            $alunosEscola = DepartmentEnrollment::where('department_id', $school->id)
                ->where('year', $year)
                ->where('month', $month)
                ->value('students_count') ?? 0;

            $custoEscola = $alunosEscola * $valorPorAluno;

            $schoolsData[] = [
                'department' => $school->name,
                'students_count' => $alunosEscola,
                'custo_proporcional' => $custoEscola
            ];
        }

        return view('meals.monthly', [
            'year' => $year,
            'month' => $month,
            'monthlyMeal' => $monthlyMeal,
            'availableMonths' => $availableMonths,
            'totalAlunos' => $totalAlunos,
            'valorPorAluno' => $valorPorAluno,
            'schoolsData' => $schoolsData
        ]);
    }

    /**
     * Salva ou atualiza o valor mensal da merenda.
     */
    public function store(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->can('manage monthly meals')) {
            abort(403, 'Acesso não autorizado');
        }

        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'total_amount' => 'required|numeric|min:0',
        ]);

        // Buscar ou criar o registro
        $monthlyMeal = MonthlyMeal::updateOrCreate(
            [
                'year' => $request->year,
                'month' => $request->month
            ],
            [
                'total_amount' => $request->total_amount,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('monthly-meals.index', ['year' => $request->year, 'month' => $request->month])
            ->with('success', 'Valor da merenda mensal cadastrado com sucesso!');
    }
}
