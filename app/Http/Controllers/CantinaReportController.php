<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CantinaReportController extends Controller
{
    public function showMonthCost(Request $request)
    {
        $user = auth()->user();

        // Log para debug
        Log::debug("CantinaReport - User: {$user->name}, ID: {$user->id}");
        Log::debug("CantinaReport - Department: " . ($user->department ? $user->department->name : 'None'));
        Log::debug("CantinaReport - Roles: " . implode(', ', $user->getRoleNames()->toArray()));

        // Verificar perfis do usuário
        $isAdmin = $user->hasRole('admin');
        $isPrefeito = $user->hasRole('mayor');
        $isSecretario = $user->hasRole('secretary');
        $isCantina = $user->department && $user->department->name === 'Cantina Central';
        $isSectorLeader = $user->hasRole('sector_leader');

        // Definir se o usuário pode ver todas as escolas
        $showAllSchools = $isAdmin || $isPrefeito || $isSecretario || $isCantina;

        Log::debug("CantinaReport - Show All Schools: " . ($showAllSchools ? 'Yes' : 'No'));

        // Filtro do ano (ou padrão para o ano atual)
        $year = $request->input('year', now()->year);

        // Base da query de matrículas
        $enrollmentsQuery = DepartmentEnrollment::where('year', $year)
            ->whereHas('department', function ($q) {
                $q->where('is_school', true);
            });

        // Se for sector_leader e não puder ver todas as escolas, filtra apenas a escola dele
        if ($isSectorLeader && !$showAllSchools) {
            if (!$user->department || !$user->department->is_school) {
                Log::error("CantinaReport - Sector leader without school department: {$user->id}");
                abort(403, 'Seu departamento não é uma escola. Acesso negado.');
            }
            Log::debug("CantinaReport - Filtering for department: {$user->department_id}");
            $enrollmentsQuery->where('department_id', $user->department_id);
        }

        // Definir o mês
        $lastMonth = $enrollmentsQuery->max('month') ?? now()->month;
        $month = $request->input('month', $lastMonth);

        Log::debug("CantinaReport - Year: {$year}, Month: {$month}");

        // 1) Descobrir a Cantina Central
        $cantinaDept = Department::where('name', 'Cantina Central')->first();
        if (!$cantinaDept) {
            Log::error("CantinaReport - Cantina Central department not found");
            return "Não existe departamento 'Cantina Central' cadastrado!";
        }

        // 2) Buscar o tipo de despesa de merenda
        $merendaType = ExpenseType::where('name', 'Merenda Escolar')
            ->orWhere('is_meal_related', true)
            ->first();

        if (!$merendaType) {
            Log::error("CantinaReport - Merenda Escolar expense type not found");
            return "Não existe tipo de despesa 'Merenda Escolar' cadastrado!";
        }

        // 3) Soma das despesas específicas de merenda escolar da cantina nesse mês
        $totalMerendaMes = Expense::where('expense_type_id', $merendaType->id)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        Log::debug("CantinaReport - Total Merenda: {$totalMerendaMes}");

        // 4) Total de alunos do sistema (todas as escolas)
        $totalAlunosSistema = DepartmentEnrollment::where('year', $year)
            ->where('month', $month)
            ->whereHas('department', function ($q) {
                $q->where('is_school', true);
            })
            ->sum('students_count');

        // Total de alunos para exibição no relatório
        if ($showAllSchools) {
            $totalAlunos = $totalAlunosSistema;
        } else {
            $totalAlunos = $enrollmentsQuery
                ->where('month', $month)
                ->sum('students_count');
        }

        // 5) Custo por aluno baseado no total de todas as escolas
        $custoPorAluno = $totalAlunosSistema > 0 ? $totalMerendaMes / $totalAlunosSistema : 0;

        Log::debug("CantinaReport - Total Alunos Sistema: {$totalAlunosSistema}, Total Alunos Exibidos: {$totalAlunos}, Custo por Aluno: {$custoPorAluno}");

        // 6) Montar detalhamento das escolas
        $deptQuery = Department::where('is_school', true);
        if (!$showAllSchools && $user->department) {
            $deptQuery->where('id', $user->department_id);
        }

        $departments = $deptQuery->orderBy('name')->get();

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

        // Log final para debug
        Log::debug("CantinaReport - FINAL CHECK - Total Merenda: {$totalMerendaMes}");
        Log::debug("CantinaReport - FINAL CHECK - Total Alunos: {$totalAlunos}");
        Log::debug("CantinaReport - FINAL CHECK - Custo por Aluno: {$custoPorAluno}");

        // Passar dados para a view
        return view('reports.cantina', [
            'year' => $year,
            'month' => $month,
            'totalMerendaMes' => $totalMerendaMes,
            'totalAlunos' => $totalAlunos,
            'custoPorAluno' => $custoPorAluno,
            'deptCosts' => $deptCosts,
            'showAllSchools' => $showAllSchools
        ]);
    }

}

