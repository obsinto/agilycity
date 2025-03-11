<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use App\Models\Expense;
use Illuminate\Http\Request;

class CantinaReportController extends Controller
{
    /**
     * Mostra o relatório "Cantina" (rateio on the fly) para um determinado mês/ano.
     */
    public function showMonthCost(Request $request)
    {
        $user = auth()->user();

        // Filtro do ano (ou padrão para o ano atual)
        $year = $request->input('year', now()->year);

        // Base da query de matriculas
        $enrollmentsQuery = DepartmentEnrollment::where('year', $year)
            ->whereHas('department', function ($q) {
                // Sempre pega somente is_school = true
                $q->where('is_school', true);
            });

        // Se for sector_leader, filtra o dept dele
        if ($user->hasRole('sector_leader')) {
            $enrollmentsQuery->where('department_id', $user->department_id);
        }

        // Aqui pegamos o "último mês cadastrado"
        $lastMonth = $enrollmentsQuery->max('month');  // ex.: 9 (setembro)

        // Se não encontrou nenhum (retornou null), podemos dar fallback
        if (!$lastMonth) {
            // Se quiser, pega o mês atual ou 1
            $lastMonth = now()->month;
        }

        // Agora podemos usar $lastMonth em vez de $request->input('month')
        $month = $lastMonth;

        // 1) Descobrir a Cantina Central
        $cantinaDept = Department::where('name', 'Cantina Central')->first();
        if (!$cantinaDept) {
            return "Não existe departamento 'Cantina Central' cadastrado!";
        }

        // 2) Soma das despesas da cantina nesse "último mês"
        $totalMerendaMes = Expense::where('department_id', $cantinaDept->id)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // 3) Soma total de alunos nesse "último mês"
        $totalAlunos = (clone $enrollmentsQuery)
            ->where('month', $month)
            ->sum('students_count');

        $custoPorAluno = $totalAlunos > 0 ? ($totalMerendaMes / $totalAlunos) : 0;

        // 4) Montar detalhamento das escolas
        $deptQuery = Department::where('is_school', true);
        if ($user->hasRole('sector_leader')) {
            $deptQuery->where('id', $user->department_id);
        }
        $departments = $deptQuery->get();

        $deptCosts = [];
        foreach ($departments as $dept) {
            $alunosEscola = DepartmentEnrollment::where('department_id', $dept->id)
                ->where('year', $year)
                ->where('month', $month)
                ->value('students_count') ?? 0;

            $custoEscola = $alunosEscola * $custoPorAluno;
            $deptCosts[] = [
                'department' => $dept->name,
                'students_count' => $alunosEscola,
                'custo_proporcional' => $custoEscola
            ];
        }

        return view('reports.cantina', [
            'year' => $year,
            'month' => $month,
            'totalMerendaMes' => $totalMerendaMes,
            'totalAlunos' => $totalAlunos,
            'custoPorAluno' => $custoPorAluno,
            'deptCosts' => $deptCosts,
        ]);
    }

}
