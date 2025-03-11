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

        // Filtro de ano (padrão: ano atual)
        $year = $request->input('year', now()->year);

        // (Opcional) Filtro para uma escola específica
        // se o usuário for prefeito ou secretário (ex.: para dropdown).
        // Se não usar esse filtro, pode remover esta linha e o where correspondente.
        $departmentId = $request->input('department_id');

        // Monta uma lista com todas as escolas, caso precise exibir em um <select>
        $allSchools = Department::where('is_school', true)
            ->orderBy('name')
            ->get();

        // Base da query: somente departamentos que sejam escolas
        $departmentsQuery = Department::where('is_school', true);

        // 1) Se for sector_leader, filtra somente o departamento do próprio usuário
        if ($user->hasRole('sector_leader')) {
            $departmentsQuery->where('id', $user->department_id);

            // 2) Se for secretary ou mayor, podemos aplicar um filtro de escola (opcional)
        } else {
            // Se foi selecionada uma escola específica (department_id),
            // filtra somente ela. Se preferir não filtrar, basta comentar esse if.
            if ($departmentId) {
                $departmentsQuery->where('id', $departmentId);
            }
        }

        // Executa a query final de departamentos (escolas)
        $departments = $departmentsQuery->orderBy('name')->get();

        // Montar dados para gráfico e tabela
        // Array com rótulos dos 12 meses
        $monthsLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            // Ex.: ["Jan","Feb","Mar",...]
            $monthsLabels[] = Carbon::createFromDate($year, $m, 1)->format('M');
        }

        // Arrays que a view usará
        $chartData = []; // para o gráfico (ECharts)
        $tableData = []; // para a tabela detalhada

        foreach ($departments as $dept) {
            // Buscar as matrículas do ano para este dept
            // e indexar por mês (keyBy('month'))
            $enrollments = DepartmentEnrollment::where('department_id', $dept->id)
                ->where('year', $year)
                ->select('month', 'students_count')
                ->get()
                ->keyBy('month');

            // Array de 12 posições (1..12) => contagem de alunos ou 0
            $monthlyCounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyCounts[$m] = $enrollments[$m]->students_count ?? 0;
            }

            // Montar série para o gráfico
            $chartData[] = [
                'name' => $dept->name,
                'data' => array_values($monthlyCounts),
            ];

            // Montar dados para a tabela
            $tableData[] = [
                'department' => $dept->name,
                'counts' => $monthlyCounts,
                'yearly_sum' => array_sum($monthlyCounts),
            ];
        }

        // Retorna a view, passando tudo que a Blade precisa
        return view('reports.students', [
            'year' => $year,
            'departmentId' => $departmentId, // se quiser exibir o select filtrado
            'allSchools' => $allSchools,   // lista de escolas pro <select>
            'monthsLabels' => $monthsLabels,
            'chartData' => $chartData,
            'tableData' => $tableData,
        ]);
    }
}
