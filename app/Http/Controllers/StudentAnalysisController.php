<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentAnalysisController extends Controller
{
    /**
     * Exibe a tela de Análise Mensal de Alunos,
     * com filtros e detalhamento por escola.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', now()->year);
        $departmentId = $request->input('department_id');

        // Obter todas as escolas, mas restringir caso o usuário não seja prefeito ou secretário de educação
        $allSchools = Department::where('is_school', true)
            ->orderBy('name');

        // Filtrar departamentos conforme a permissão do usuário
        if (!$user->hasRole('mayor') && !$user->can('view all schools')) {
            // Se não for prefeito nem secretário de educação, só pode ver sua própria escola
            $allSchools->where('id', $user->department_id);
            $departmentId = $user->department_id; // Força a seleção da própria escola
        }

        $allSchools = $allSchools->get();

        // Buscar apenas as escolas que o usuário pode ver
        $departmentsQuery = Department::where('is_school', true);

        if (!$user->hasRole('mayor') && !$user->can('view all schools')) {
            $departmentsQuery->where('id', $user->department_id);
        } elseif ($departmentId) {
            $departmentsQuery->where('id', $departmentId);
        }

        $departments = $departmentsQuery->orderBy('name')->get();

        // Preparação de dados para gráfico e tabela
        $monthsLabels = collect(range(1, 12))->map(fn($m) => Carbon::createFromDate($year, $m, 1)->format('M'));

        $chartData = [];
        $tableData = [];

        foreach ($departments as $dept) {
            $enrollments = DepartmentEnrollment::where('department_id', $dept->id)
                ->where('year', $year)
                ->select('month', 'students_count')
                ->get()
                ->keyBy('month');

            $monthlyCounts = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => $enrollments[$m]->students_count ?? 0]);

            $chartData[] = [
                'name' => $dept->name,
                'data' => $monthlyCounts->values()->toArray(),
            ];

            $tableData[] = [
                'department' => $dept->name,
                'counts' => $monthlyCounts,
                'yearly_sum' => $monthlyCounts->sum(),
            ];
        }

        return view('reports.students', compact('year', 'departmentId', 'allSchools', 'monthsLabels', 'chartData', 'tableData'));
    }

}
