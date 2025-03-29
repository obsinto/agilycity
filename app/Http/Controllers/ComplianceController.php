<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\FixedExpense;
use App\Models\MonthlyExpenseSubmission;
use App\Models\Secretary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    /**
     * Exibe o dashboard de compliance.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $isPrefeito = $user->hasRole('mayor');
        $isSecretario = $user->hasRole('secretary') || $user->hasRole('education_secretary');

        $currentDate = Carbon::now();
        $year = $request->input('year', $currentDate->year);
        $month = $request->input('month', $currentDate->month);

        // Obtém o array de nomes de meses para exibição
        $monthNames = $this->getMonthNames();

        // Se for prefeito, mostrar visão geral de todas as secretarias
        if ($isPrefeito) {
            return $this->mayorDashboard($year, $month, $monthNames);
        }

        // Se for secretário, mostrar visão dos departamentos da secretaria
        if ($isSecretario) {
            return $this->secretaryDashboard($user->secretary_id, $year, $month, $monthNames);
        }

        // Se for líder de departamento, mostrar apenas seu departamento
        if ($user->department) {
            return $this->departmentDashboard($user->department_id, $year, $month, $monthNames);
        }

        // Caso não se encaixe em nenhum dos perfis acima
        return redirect()->route('dashboard')
            ->with('error', 'Você não tem permissão para acessar o dashboard de compliance.');
    }

    /**
     * Dashboard para o prefeito (visão de secretarias).
     */
    private function mayorDashboard($year, $month, $monthNames)
    {
        // Buscar todas as secretarias com seus departamentos
        $secretaries = Secretary::withCount(['departments'])
            ->with(['departments' => function ($query) use ($year, $month) {
                $query->with(['monthlySubmissions' => function ($q) use ($year, $month) {
                    $q->where('year', $year)->where('month', $month);
                }]);
            }])
            ->get();

        // Processar dados para cada secretaria
        $secretariesData = $secretaries->map(function ($secretary) use ($year, $month) {
            $totalDepts = $secretary->departments_count;
            $closedDepts = $secretary->departments->filter(function ($dept) {
                // Verificar se existe alguma submissão e pegar a primeira
                $submission = $dept->monthlySubmissions->first();
                return $submission && $submission->is_submitted;
            })->count();

            return [
                'id' => $secretary->id,
                'name' => $secretary->name,
                'total_departments' => $totalDepts,
                'closed_departments' => $closedDepts,
                'completion_percentage' => $totalDepts > 0 ? ($closedDepts / $totalDepts) * 100 : 0,
                'status' => $totalDepts === $closedDepts ? 'complete' : 'incomplete'
            ];
        });

        // Estatísticas gerais
        $totalDepartments = Department::count();
        $closedDepartments = MonthlyExpenseSubmission::where('year', $year)
            ->where('month', $month)
            ->where('is_submitted', true)
            ->count();

        $overallPercentage = $totalDepartments > 0 ? ($closedDepartments / $totalDepartments) * 100 : 0;

        return view('compliance.mayor-dashboard', [
            'secretariesData' => $secretariesData,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthNames[$month] ?? '',
            'totalDepartments' => $totalDepartments,
            'closedDepartments' => $closedDepartments,
            'overallPercentage' => $overallPercentage,
            'monthNames' => $monthNames,
            'years' => range(date('Y') - 2, date('Y') + 1)
        ]);
    }

    /**
     * Dashboard para secretário (visão de departamentos).
     */
    public function secretaryDashboard($secretaryId, $year, $month, $monthNames)
    {
        $secretary = Secretary::findOrFail($secretaryId);

        // Buscar departamentos desta secretaria
        $departments = Department::where('secretary_id', $secretaryId)
            ->with(['monthlySubmissions' => function ($q) use ($year, $month) {
                $q->where('year', $year)->where('month', $month);
            }])
            ->get();

        // Verificar estado de cada departamento
        $departmentsData = [];
        foreach ($departments as $department) {
            // Verificar se pode fechar o mês
            $closeCheck = $this->canCloseMonth($department->id, $year, $month);

            // Verificar se o mês já está fechado
            $submission = $department->monthlySubmissions->first();
            $isClosed = $submission && $submission->is_submitted;

            // Adicionar informações do departamento
            $departmentsData[] = [
                'id' => $department->id,
                'name' => $department->name,
                'is_closed' => $isClosed,
                'submitted_at' => $submission ? $submission->submitted_at : null,
                'submitted_by' => $submission ? ($submission->submitter->name ?? 'Sistema') : null,
                'status' => $isClosed ? 'closed' : ($closeCheck['can_close'] ? 'ready_to_close' : 'incomplete'),
                'missing_categories_count' => isset($closeCheck['missing_categories']) ? count($closeCheck['missing_categories']) : 0,
                'missing_categories' => isset($closeCheck['missing_categories']) ?
                    ExpenseType::whereIn('id', $closeCheck['missing_categories'])->pluck('name') :
                    [],
                'missing_fixed_expenses_count' => isset($closeCheck['missing_fixed_expenses']) ? count($closeCheck['missing_fixed_expenses']) : 0,
                'missing_fixed_expenses' => isset($closeCheck['missing_fixed_expenses']) ?
                    FixedExpense::whereIn('id', $closeCheck['missing_fixed_expenses'])->get()->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'amount' => $item->amount
                        ];
                    }) :
                    []
            ];
        }

        // Estatísticas gerais desta secretaria
        $totalDepartments = count($departments);
        $closedDepartments = count(array_filter($departmentsData, function ($dept) {
            return $dept['is_closed'];
        }));

        $completionPercentage = $totalDepartments > 0 ? ($closedDepartments / $totalDepartments) * 100 : 0;

        return view('compliance.secretary-dashboard', [
            'departmentsData' => $departmentsData,
            'secretary' => $secretary,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthNames[$month] ?? '',
            'totalDepartments' => $totalDepartments,
            'closedDepartments' => $closedDepartments,
            'completionPercentage' => $completionPercentage,
            'monthNames' => $monthNames,
            'years' => range(date('Y') - 2, date('Y') + 1)
        ]);
    }

    /**
     * Dashboard para líder de departamento.
     */
    private function departmentDashboard($departmentId, $year, $month, $monthNames)
    {
        $department = Department::with('secretary')->findOrFail($departmentId);

        // Verificar se pode fechar o mês
        $closeCheck = $this->canCloseMonth($departmentId, $year, $month);

        // Verificar se o mês já está fechado
        $submission = MonthlyExpenseSubmission::where('department_id', $departmentId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $isClosed = $submission && $submission->is_submitted;

        // Listar despesas do mês
        $expenses = Expense::where('department_id', $departmentId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->with(['expenseType'])
            ->orderBy('expense_date', 'desc')
            ->paginate(10);

        // Dados para a view
        $departmentData = [
            'id' => $department->id,
            'name' => $department->name,
            'secretary' => $department->secretary->name,
            'is_closed' => $isClosed,
            'submitted_at' => $submission ? $submission->submitted_at : null,
            'submitted_by' => $submission ? ($submission->submitter->name ?? 'Sistema') : null,
            'status' => $isClosed ? 'closed' : ($closeCheck['can_close'] ? 'ready_to_close' : 'incomplete'),
            'can_close' => $closeCheck['can_close'],
            'missing_categories_count' => isset($closeCheck['missing_categories']) ? count($closeCheck['missing_categories']) : 0,
            'missing_categories' => isset($closeCheck['missing_categories']) ?
                ExpenseType::whereIn('id', $closeCheck['missing_categories'])->pluck('name') :
                [],
            'missing_fixed_expenses_count' => isset($closeCheck['missing_fixed_expenses']) ? count($closeCheck['missing_fixed_expenses']) : 0,
            'missing_fixed_expenses' => isset($closeCheck['missing_fixed_expenses']) ?
                FixedExpense::whereIn('id', $closeCheck['missing_fixed_expenses'])->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'amount' => $item->amount
                    ];
                }) :
                []
        ];

        return view('compliance.department-dashboard', [
            'department' => $departmentData,
            'expenses' => $expenses,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthNames[$month] ?? '',
            'monthNames' => $monthNames,
            'years' => range(date('Y') - 2, date('Y') + 1)
        ]);
    }

    /**
     * Exibe os detalhes de uma secretaria específica.
     */
    public function secretaryDetails(Request $request, $secretaryId)
    {
        $monthNames = $this->getMonthNames();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        // Verificar permissão
        $user = Auth::user();
        if (!$user->hasRole('mayor') && !$user->hasRole('secretary') && $user->secretary_id != $secretaryId) {
            return redirect()->route('compliance.dashboard')
                ->with('error', 'Você não tem permissão para visualizar esta secretaria.');
        }

        return $this->secretaryDashboard($secretaryId, $year, $month, $monthNames);
    }

    /**
     * Verifica se um departamento pode fechar o mês.
     */
    public function canCloseMonth($departmentId, $year, $month)
    {
        // 1. Verificar se todas as categorias têm pelo menos uma despesa
        $allExpenseTypes = ExpenseType::where('active', true)->pluck('id')->toArray();

        $coveredTypes = Expense::where('department_id', $departmentId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->distinct()
            ->pluck('expense_type_id')
            ->toArray();

        $missingCategories = array_diff($allExpenseTypes, $coveredTypes);

        if (!empty($missingCategories)) {
            return [
                'can_close' => false,
                'reason' => 'missing_categories',
                'missing_categories' => $missingCategories
            ];
        }

        // 2. Verificar despesas fixas esperadas para este mês
        $fixedExpensesExpected = FixedExpense::where('department_id', $departmentId)
            ->where('status', 'active')
            ->where(function ($query) use ($year, $month) {
                // Início antes ou durante o mês verificado
                $query->whereYear('start_date', '<', $year)
                    ->orWhere(function ($q) use ($year, $month) {
                        $q->whereYear('start_date', $year)
                            ->whereMonth('start_date', '<=', $month);
                    });
            })
            ->where(function ($query) use ($year, $month) {
                // Sem data de fim OU fim após ou durante o mês verificado
                $query->whereNull('end_date')
                    ->orWhere(function ($q) use ($year, $month) {
                        $q->whereYear('end_date', '>', $year)
                            ->orWhere(function ($q2) use ($year, $month) {
                                $q2->whereYear('end_date', $year)
                                    ->whereMonth('end_date', '>=', $month);
                            });
                    });
            })
            ->pluck('id')
            ->toArray();

        // 3. Verificar quais despesas fixas foram efetivamente registradas
        $missingFixedExpenses = [];

        foreach ($fixedExpensesExpected as $fixedExpenseId) {
            $exists = Expense::where('department_id', $departmentId)
                ->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month)
                ->where(function ($query) use ($fixedExpenseId) {
                    // Verifica referência direta ou através da observação
                    $query->where('fixed_expense_id', $fixedExpenseId)
                        ->orWhere('observation', 'like', '%[Despesa Fixa: ' . $fixedExpenseId . ']%');
                })
                ->exists();

            if (!$exists) {
                $missingFixedExpenses[] = $fixedExpenseId;
            }
        }

        if (!empty($missingFixedExpenses)) {
            return [
                'can_close' => false,
                'reason' => 'missing_fixed_expenses',
                'missing_fixed_expenses' => $missingFixedExpenses
            ];
        }

        // Se passou por todas as verificações, pode fechar o mês
        return [
            'can_close' => true
        ];
    }

    /**
     * Processa o fechamento do mês.
     */
    public function closeMonth(Request $request)
    {
        $departmentId = $request->input('department_id');
        $year = $request->input('year');
        $month = $request->input('month');

        // Verificar permissão
        $user = Auth::user();
        $department = Department::findOrFail($departmentId);

        if (!$user->hasRole('mayor') &&
            !$user->hasRole('secretary') &&
            !$user->hasRole('education_secretary') &&
            $user->department_id != $departmentId) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para fechar este departamento.');
        }

        // Verificar se pode fechar o mês
        $closeCheck = $this->canCloseMonth($departmentId, $year, $month);

        if (!$closeCheck['can_close']) {
            $reason = '';

            if (isset($closeCheck['reason']) && $closeCheck['reason'] === 'missing_categories') {
                $missingTypeNames = ExpenseType::whereIn('id', $closeCheck['missing_categories'])
                    ->pluck('name')
                    ->toArray();

                $reason = 'Categorias pendentes: ' . implode(', ', $missingTypeNames);
            } elseif (isset($closeCheck['reason']) && $closeCheck['reason'] === 'missing_fixed_expenses') {
                $missingFixedExpenseNames = FixedExpense::whereIn('id', $closeCheck['missing_fixed_expenses'])
                    ->pluck('name')
                    ->toArray();

                $reason = 'Despesas fixas pendentes: ' . implode(', ', $missingFixedExpenseNames);
            }

            return redirect()->back()
                ->with('error', 'Não é possível fechar o mês. ' . $reason);
        }

        // Registrar o fechamento
        MonthlyExpenseSubmission::updateOrCreate(
            [
                'department_id' => $departmentId,
                'year' => $year,
                'month' => $month
            ],
            [
                'is_submitted' => true,
                'submitted_at' => now(),
                'submitted_by' => $user->id,
            ]
        );

        // Registrar atividade (se estiver usando o pacote de activity log)
        if (class_exists('\Spatie\Activitylog\Models\Activity')) {
            activity()
                ->performedOn($department)
                ->causedBy($user)
                ->withProperties([
                    'year' => $year,
                    'month' => $month
                ])
                ->log('closed_month');
        }

        // Redirecionar para a rota apropriada
        if ($user->hasRole('mayor')) {
            return redirect()->route('compliance.secretary-details', [
                'secretary' => $department->secretary_id,
                'year' => $year,
                'month' => $month
            ])->with('success', 'Mês fechado com sucesso para o departamento ' . $department->name);
        }

        return redirect()->route('compliance.dashboard', [
            'year' => $year,
            'month' => $month
        ])->with('success', 'Mês fechado com sucesso!');
    }

    /**
     * Override do status de um departamento (somente para prefeito e secretários).
     */
    public function overrideStatus(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'status' => 'required|in:open,closed',
            'notes' => 'required|string|max:500',
        ]);

        $departmentId = $request->input('department_id');
        $year = $request->input('year');
        $month = $request->input('month');
        $status = $request->input('status');
        $notes = $request->input('notes');

        // Verificar permissão (somente prefeito e secretários)
        $user = Auth::user();
        $department = Department::findOrFail($departmentId);

        if (!$user->hasRole('mayor') &&
            !$user->hasRole('secretary') &&
            !$user->hasRole('education_secretary')) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para sobrescrever o status.');
        }

        // Se o usuário for secretário, só pode gerenciar departamentos da sua secretaria
        if (($user->hasRole('secretary') || $user->hasRole('education_secretary')) &&
            $department->secretary_id != $user->secretary_id) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para gerenciar este departamento.');
        }

        // Atualizar ou criar a submissão
        MonthlyExpenseSubmission::updateOrCreate(
            [
                'department_id' => $departmentId,
                'year' => $year,
                'month' => $month
            ],
            [
                'is_submitted' => $status === 'closed',
                'submitted_at' => $status === 'closed' ? now() : null,
                'submitted_by' => $user->id,
                'notes' => $notes
            ]
        );

        // Registrar atividade (se estiver usando o pacote de activity log)
        if (class_exists('\Spatie\Activitylog\Models\Activity')) {
            activity()
                ->performedOn($department)
                ->causedBy($user)
                ->withProperties([
                    'year' => $year,
                    'month' => $month,
                    'action' => "status_override_to_{$status}",
                    'notes' => $notes
                ])
                ->log('override_month_status');
        }

        $message = $status === 'closed' ?
            'Status alterado para FECHADO com sucesso!' :
            'Status alterado para ABERTO com sucesso!';

        if ($user->hasRole('mayor')) {
            return redirect()->route('compliance.secretary-details', [
                'secretary' => $department->secretary_id,
                'year' => $year,
                'month' => $month
            ])->with('success', $message);
        }

        return redirect()->route('compliance.dashboard', [
            'year' => $year,
            'month' => $month
        ])->with('success', $message);
    }

    /**
     * Retorna um array com os nomes dos meses.
     */
    private function getMonthNames()
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];
    }
}
