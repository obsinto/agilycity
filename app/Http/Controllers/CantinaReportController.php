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
    /**
     * Mostra o relatório "Cantina" (rateio de despesas específicas de merenda) para um determinado mês/ano.
     */
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
                // Sempre pega somente is_school = true
                $q->where('is_school', true);
            });

        // Se for sector_leader e não puder ver todas as escolas, filtra apenas a escola dele
        if ($isSectorLeader && !$showAllSchools) {
            // Verificar se o usuário tem departamento
            if (!$user->department || !$user->department->is_school) {
                Log::error("CantinaReport - Sector leader without school department: {$user->id}");
                abort(403, 'Seu departamento não é uma escola. Acesso negado.');
            }

            Log::debug("CantinaReport - Filtering for department: {$user->department_id}");
            $enrollmentsQuery->where('department_id', $user->department_id);
        }

        // Aqui pegamos o "último mês cadastrado" ou usamos o mês da requisição
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

        // 4) Definir query para obter número de alunos
        // Primeiro, vamos pegar os alunos das escolas relevantes para nosso contexto
        $enrollmentsForMonth = (clone $enrollmentsQuery)->where('month', $month);

        if ($showAllSchools) {
            // Se pode ver todas as escolas, o total de alunos é de todas as escolas
            $totalAlunos = $enrollmentsForMonth->sum('students_count');
        } else {
            // Caso contrário, o total é apenas da escola do usuário
            $totalAlunos = $enrollmentsForMonth
                ->where('department_id', $user->department_id)
                ->sum('students_count');
        }

        // Log para debug - verificar dados antes do cálculo
        Log::debug("CantinaReport - Pré-cálculo - Total Merenda: {$totalMerendaMes}, Total Alunos: {$totalAlunos}");

        // CORREÇÃO: Inicializar custoPorAluno como zero e fazer verificação robusta
        $custoPorAluno = 0;
        if ($totalAlunos > 0 && $totalMerendaMes > 0) {
            $custoPorAluno = $totalMerendaMes / $totalAlunos;
        }

        Log::debug("CantinaReport - Total Alunos: {$totalAlunos}, Custo por Aluno: {$custoPorAluno}");

        // 5) Montar detalhamento das escolas - APENAS AS PERMITIDAS PARA O USUÁRIO
        $deptQuery = Department::where('is_school', true);

        // IMPORTANTE: Se não puder ver todas as escolas, mostrar apenas sua escola
        if (!$showAllSchools && $user->department) {
            Log::debug("CantinaReport - Filtering departments for user's school only: {$user->department_id}");
            $deptQuery->where('id', $user->department_id);
        }

        $departments = $deptQuery->orderBy('name')->get();

        Log::debug("CantinaReport - Departments count: " . $departments->count());
        foreach ($departments as $dept) {
            Log::debug("CantinaReport - Department in list: {$dept->id} - {$dept->name}");
        }

        // 6) Calcular os custos por escola
        $deptCosts = [];
        $totalCalculatedStudents = 0; // Para verificação de consistência
        $totalCalculatedCosts = 0; // Para verificação de consistência

        foreach ($departments as $dept) {
            // Buscar número de alunos da escola neste mês/ano
            $alunosEscola = DepartmentEnrollment::where('department_id', $dept->id)
                ->where('year', $year)
                ->where('month', $month)
                ->value('students_count') ?? 0;

            // CORREÇÃO: Cálculo do custo proporcional por escola
            // Se estiver vendo apenas uma escola OU se não puder ver todas as escolas,
            // atribui o custo total da merenda para a escola exibida
            if (count($departments) == 1 && !$showAllSchools) {
                $custoEscola = $totalMerendaMes;
            } else {
                // Caso contrário, distribui o custo proporcionalmente
                $custoEscola = $alunosEscola * $custoPorAluno;
            }

            // Acumular para verificação
            $totalCalculatedStudents += $alunosEscola;
            $totalCalculatedCosts += $custoEscola;

            $deptCosts[] = [
                'department' => $dept->name,
                'students_count' => $alunosEscola,
                'custo_proporcional' => $custoEscola
            ];
        }

        // Verificar consistência dos dados calculados
        Log::debug("CantinaReport - Verificação - Total de alunos calculado: {$totalCalculatedStudents} vs Esperado: {$totalAlunos}");
        Log::debug("CantinaReport - Verificação - Total de custos calculado: {$totalCalculatedCosts} vs Esperado: {$totalMerendaMes}");

        // Se houver inconsistência significativa, registrar alerta
        // NOTA: Desativamos a verificação de custo quando está vendo apenas uma escola
        // pois o custo total está sendo atribuído à única escola visível
        if (abs($totalCalculatedStudents - $totalAlunos) > 0.01) {
            Log::warning("CantinaReport - Inconsistência no total de alunos calculado");
        }

        if ($showAllSchools && abs($totalCalculatedCosts - $totalMerendaMes) > 0.01) {
            Log::warning("CantinaReport - Inconsistência no total de custos calculado");
        }

        // Log final para debug - Verificar valores finais
        Log::debug("CantinaReport - FINAL CHECK - Total Merenda: {$totalMerendaMes}");
        Log::debug("CantinaReport - FINAL CHECK - Total Alunos: {$totalAlunos}");
        Log::debug("CantinaReport - FINAL CHECK - Custo por Aluno: {$custoPorAluno}");

        Log::debug("CantinaReport - Rendering view with " . count($deptCosts) . " departments");

        // Passar dados para a view, incluindo a flag showAllSchools
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
