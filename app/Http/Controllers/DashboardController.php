<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Secretary;
use App\Services\ExpenseCapService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $expenseCapService;

    public function __construct(ExpenseCapService $expenseCapService)
    {
        $this->expenseCapService = $expenseCapService;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->hasRole('mayor')) {
            // Consulta base com todas as despesas e eager loading
            $expenses = Expense::with(['secretary', 'department', 'expenseType'])->get();

            // Cálculo de totais e estatísticas
            $totalExpenses = $expenses->sum('amount');
            $currentMonth = now()->format('Y-m');
            $lastMonth = now()->subMonth()->format('Y-m');

            $currentMonthExpenses = $expenses->filter(function ($expense) use ($currentMonth) {
                return $expense->expense_date->format('Y-m') === $currentMonth;
            })->sum('amount');

            $lastMonthExpenses = $expenses->filter(function ($expense) use ($lastMonth) {
                return $expense->expense_date->format('Y-m') === $lastMonth;
            })->sum('amount');

            $totalTransactions = $expenses->count();

            // Obter secretarias, departamentos e tipos de despesa com suas relações
            $secretaries = Secretary::with(['departments', 'expenses'])->get();
            $departments = Department::with('secretary')->get();
            $expenseTypes = ExpenseType::distinct()->get();

            // Calcular os maiores "gastos" (top performers)
            $topSecretary = $secretaries->sortByDesc(function ($secretary) {
                return $secretary->expenses->sum('amount');
            })->first();

            $topDepartment = $departments->sortByDesc(function ($department) {
                return $department->expenses->sum('amount');
            })->first();

            $averageBySecretary = $secretaries->count() > 0 ? $totalExpenses / $secretaries->count() : 0;

            // Preparar dados para os gráficos
            $expensesBySecretary = $this->calculateExpensesBySecretary($secretaries);
            $monthlyExpenses = $this->calculateMonthlyExpenses($expenses);
            $hierarchicalData = $this->calculateHierarchicalData($secretaries);
            $latestExpenses = $expenses->sortByDesc('expense_date')->take(5);
            $series = $this->calculateSecretarySeries($secretaries, $monthlyExpenses);

            // Dados por tipo de despesa
            $expenseTypeData = ExpenseType::with('expenses')
                ->get()
                ->map(function ($type) {
                    return [
                        'name' => $type->name,
                        'value' => $type->expenses->sum('amount')
                    ];
                })
                ->filter(function ($type) {
                    return $type['value'] > 0;
                })
                ->values();

            $expenseTypes = ExpenseType::select('name')->distinct()->orderBy('name')->get();

            // =====================
            // TETO DE GASTOS
            // =====================
            $capValue = 0;
            $capSource = 'none';

            // Exemplo: buscamos o teto da "topSecretary" (pode não ser o ideal em produção)
            if ($topSecretary) {
                // Lê do Service
                $possibleCap = $this->expenseCapService->getCapForExpense($topSecretary->id);

                // Se vier maior que 0, assumimos que é um teto "macro" (já que não filtramos por tipo)
                if ($possibleCap > 0) {
                    $capValue = $possibleCap;
                    $capSource = 'macro';
                } else {
                    // Se for zero, significa que não encontrou nada
                    // Você pode definir fallback com config('app.default_monthly_budget', 30000) se quiser
                    $capValue = 0;
                    $capSource = 'none';
                }
            }

            return view('dashboard.mayor', compact(
                'totalExpenses',
                'currentMonthExpenses',
                'lastMonthExpenses',
                'totalTransactions',
                'averageBySecretary',
                'expensesBySecretary',
                'monthlyExpenses',
                'latestExpenses',
                'secretaries',
                'departments',
                'topSecretary',
                'topDepartment',
                'hierarchicalData',
                'series',
                'expenseTypes',
                'expenseTypeData',
                'capValue',     // Passa o teto de gastos para a view
                'capSource'     // Passa a origem do teto para a view
            ));
        }

        if ($user->hasRole('secretary')) {
            return view('dashboard.secretary', ['secretary' => $user->secretary]);
        }

        if ($user->hasRole('sector_leader')) {
            return view('dashboard.sector_leader', ['department' => $user->department]);
        }

        return view('dashboard.default');
    }

    /**
     * Método que filtra os dados (provavelmente chamado por AJAX).
     */
    public function filter(Request $request)
    {
        // Consulta base com eager loading das relações necessárias
        $query = Expense::with(['secretary', 'department', 'expenseType']);

        // Definir período de referência - padrão: mês atual
        $referenceStartDate = now()->startOfMonth();
        $referenceEndDate = now()->endOfMonth();

        // Filtro por data
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $referenceStartDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $referenceEndDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $query->whereBetween('expense_date', [$referenceStartDate, $referenceEndDate]);
        }

        // Outros filtros
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('expense_type')) {
            $query->whereHas('expenseType', function ($q) use ($request) {
                $q->where('name', $request->expense_type);
            });
        }

        if ($request->filled('secretary_id')) {
            $query->where('secretary_id', $request->secretary_id);
        }

        // Executa a consulta
        $expenses = $query->get();

        // =======================
        // Período atual/anterior
        // =======================
        $currentPeriodStart = $referenceStartDate->copy();
        $currentPeriodEnd = $referenceEndDate->copy();

        // Calcula o tamanho do período para poder comparar com "período anterior"
        $periodLength = $currentPeriodEnd->diffInDays($currentPeriodStart) + 1;
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodLength - 1);

        // Soma das despesas no período atual
        $currentPeriodExpenses = $expenses->filter(function ($expense) use ($currentPeriodStart, $currentPeriodEnd) {
            return $expense->expense_date >= $currentPeriodStart && $expense->expense_date <= $currentPeriodEnd;
        })->sum('amount');

        // Soma das despesas no período anterior
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            // Sem filtro de data => mês passado no modo "original"
            $lastMonth = now()->subMonth()->format('Y-m');
            $lastMonthExpenses = $expenses->filter(function ($expense) use ($lastMonth) {
                return $expense->expense_date->format('Y-m') === $lastMonth;
            })->sum('amount');
        } else {
            // Com filtro de data => período anterior equivalente
            $lastMonthExpenses = $expenses->filter(function ($expense) use ($previousPeriodStart, $previousPeriodEnd) {
                return $expense->expense_date >= $previousPeriodStart && $expense->expense_date <= $previousPeriodEnd;
            })->sum('amount');
        }

        // Monta array de filtros para o cap service
        $filters = [
            'secretary_id' => $request->secretary_id,
            'department_id' => $request->department_id,
            'expense_type' => $request->expense_type
        ];

        // Obtém o teto de gastos
        $capValue = $this->expenseCapService->getCapForFilters($filters);
        $capSource = 'none';  // Default

        // Se não encontrar teto ou <= 0, usar fallback
        $monthlyBudget = config('app.default_monthly_budget', 30000);
        if (is_null($capValue) || $capValue <= 0) {
            $capValue = $monthlyBudget;
            $capSource = 'none'; // Você pode trocar "none" por "fallback" se preferir
        } else {
            // Se expense_type foi filtrado => 'specific', senão => 'macro'
            $capSource = $request->filled('expense_type') ? 'specific' : 'macro';
        }

        // Retorna JSON com dados para o front-end
        return response()->json([
            'totalExpenses' => $expenses->sum('amount'),
            'currentMonthExpenses' => $currentPeriodExpenses,
            'lastMonthExpenses' => $lastMonthExpenses,
            'totalTransactions' => $expenses->count(),
            'monthlyExpenses' => $this->getMonthlyData($expenses),
            'series' => $this->calculateFilteredSeries($expenses),
            'hierarchicalData' => $this->getHierarchicalData($expenses),
            'expenseTypeData' => $this->getExpenseTypeData($expenses),
            'secretaries' => $this->getSecretariesData($expenses),
            'departmentsData' => $this->getDepartmentsData($expenses),
            'monthlyBudget' => $monthlyBudget,   // Orçamento padrão
            'capValue' => $capValue,        // Teto efetivo
            'capSource' => $capSource,       // Origem do teto
            'referenceStartDate' => $referenceStartDate->format('Y-m-d'),
            'referenceEndDate' => $referenceEndDate->format('Y-m-d'),
        ]);
    }

    // ========================================================
    // =============== MÉTODOS AUXILIARES =====================
    // ========================================================

    private function getDepartmentsData($expenses)
    {
        return Department::with('expenses')
            ->get()
            ->map(function ($department) use ($expenses) {
                $total = $expenses->where('department_id', $department->id)->sum('amount');
                return [
                    'name' => $department->name,
                    'total' => $total,
                    'id' => $department->id
                ];
            })
            ->filter(function ($dept) {
                return $dept['total'] > 0;
            })
            ->sortByDesc('total')
            ->values();
    }

    private function calculateExpensesBySecretary($secretaries)
    {
        return $secretaries->map(function ($secretary) {
            return [
                'name' => $secretary->name,
                'total' => $secretary->expenses->sum('amount')
            ];
        })->values();
    }

    private function calculateMonthlyExpenses($expenses)
    {
        return $expenses
            ->groupBy(function ($expense) {
                return $expense->expense_date->format('Y-m');
            })
            ->map(function ($group, $month) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $month)->format('M/Y'),
                    'total' => $group->sum('amount')
                ];
            })
            ->values();
    }

    private function calculateHierarchicalData($secretaries)
    {
        return $secretaries->map(function ($secretary) {
            return [
                'name' => $secretary->name,
                'id' => $secretary->id, // ID da Secretaria
                'value' => $secretary->expenses->sum('amount'),
                'children' => $secretary->departments->map(function ($department) {
                    return [
                        'name' => $department->name,
                        'id' => $department->id, // ID do Departamento
                        'value' => $department->expenses->sum('amount')
                    ];
                })->filter(function ($dept) {
                    return $dept['value'] > 0;
                })
            ];
        })->filter(function ($sec) {
            return $sec['value'] > 0;
        });
    }

    private function calculateSecretarySeries($secretaries, $monthlyExpenses)
    {
        return $secretaries->map(function ($secretary) use ($monthlyExpenses) {
            return [
                'name' => $secretary->name,
                'type' => 'line',
                'smooth' => true,
                'data' => $monthlyExpenses->map(function ($monthData) use ($secretary) {
                    $monthExpenses = $secretary->expenses->filter(function ($expense) use ($monthData) {
                        return $expense->expense_date->format('M/Y') === $monthData['month'];
                    });
                    return $monthExpenses->sum('amount');
                })->values()
            ];
        });
    }

    private function calculateFilteredSeries($expenses)
    {
        return Secretary::whereHas('expenses', function ($query) use ($expenses) {
            $query->whereIn('id', $expenses->pluck('id'));
        })
            ->get()
            ->map(function ($secretary) use ($expenses) {
                $secretaryExpenses = $expenses->where('secretary_id', $secretary->id);

                return [
                    'name' => $secretary->name,
                    'type' => 'line',
                    'smooth' => true,
                    'data' => $this->getMonthlyData($expenses)
                        ->map(function ($monthData) use ($secretaryExpenses) {
                            return $secretaryExpenses
                                ->filter(function ($expense) use ($monthData) {
                                    return $expense->expense_date->format('M/Y') === $monthData['month'];
                                })
                                ->sum('amount');
                        })->values()
                ];
            });
    }

    private function getHierarchicalData($expenses)
    {
        return Secretary::whereHas('expenses', function ($query) use ($expenses) {
            $query->whereIn('id', $expenses->pluck('id'));
        })
            ->with('departments')
            ->get()
            ->map(function ($secretary) use ($expenses) {
                $secretaryExpenses = $expenses->where('secretary_id', $secretary->id);
                return [
                    'name' => $secretary->name,
                    'value' => $secretaryExpenses->sum('amount'),
                    'children' => $secretary->departments
                        ->map(function ($department) use ($expenses) {
                            $deptExpenses = $expenses->where('department_id', $department->id);
                            return [
                                'name' => $department->name,
                                'value' => $deptExpenses->sum('amount')
                            ];
                        })
                        ->filter(function ($dept) {
                            return $dept['value'] > 0;
                        })
                ];
            })
            ->filter(function ($sec) {
                return $sec['value'] > 0;
            });
    }

    private function getMonthlyData($expenses)
    {
        return $expenses
            ->groupBy(function ($expense) {
                return $expense->expense_date->format('Y-m');
            })
            ->map(function ($group, $month) {
                $date = Carbon::createFromFormat('Y-m', $month);
                return [
                    'month' => $date->format('M/Y'),
                    'total' => $group->sum('amount'),
                    'sort_year' => $date->format('Y'),
                    'sort_month' => $date->format('m')
                ];
            })
            ->sortBy([
                ['sort_year', 'desc'],
                ['sort_month', 'asc']
            ])
            ->values();
    }

    private function getSecretariesData($expenses)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        $totalExpenses = $expenses->sum('amount'); // Usar apenas as despesas filtradas

        return Secretary::whereHas('expenses', function ($query) use ($expenses) {
            $query->whereIn('id', $expenses->pluck('id'));
        })
            ->get()
            ->map(function ($secretary) use ($expenses, $currentMonth, $lastMonth, $totalExpenses) {
                // Filtrar apenas as despesas que pertencem ao conjunto filtrado
                $secretaryExpenses = $expenses->where('secretary_id', $secretary->id);

                $currentMonthExpenses = $secretaryExpenses->filter(function ($expense) use ($currentMonth) {
                    return $expense->expense_date->format('Y-m') === $currentMonth;
                });

                $lastMonthExpenses = $secretaryExpenses->filter(function ($expense) use ($lastMonth) {
                    return $expense->expense_date->format('Y-m') === $lastMonth;
                });

                return [
                    'id' => $secretary->id,
                    'name' => $secretary->name,
                    'total' => $secretaryExpenses->sum('amount'),
                    'currentMonthTotal' => $currentMonthExpenses->sum('amount'),
                    'lastMonthTotal' => $lastMonthExpenses->sum('amount'),
                    'totalExpenses' => $totalExpenses // Usado para calcular a porcentagem
                ];
            })
            ->filter(function ($secretary) {
                return $secretary['total'] > 0; // Remover secretarias sem despesas no período
            })
            ->values();
    }

    public function getSecretaryDetails(Request $request, $id)
    {
        $secretary = Secretary::with(['departments'])->findOrFail($id);

        // Construir a query base com os mesmos filtros do dashboard
        $query = Expense::where('secretary_id', $id);

        if ($request->filled('date_range')) {
            $dateRange = explode(' - ', $request->date_range);
            if (count($dateRange) == 2) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            }
        }

        if ($request->filled('expense_type')) {
            $query->whereHas('expenseType', function ($q) use ($request) {
                $q->where('name', $request->expense_type);
            });
        }

        $expenses = $query->get();
        $totalExpenses = $expenses->sum('amount');

        $departments = $secretary->departments->map(function ($department) use ($expenses, $totalExpenses) {
            $departmentExpenses = $expenses->where('department_id', $department->id);
            $total = $departmentExpenses->sum('amount');

            return [
                'name' => $department->name,
                'total' => $total,
                'percentage' => $totalExpenses > 0 ? ($total / $totalExpenses) * 100 : 0
            ];
        })->filter(function ($dept) {
            return $dept['total'] > 0; // Remover departamentos sem despesas no período
        })->values();

        return response()->json([
            'secretary' => $secretary,
            'departments' => $departments
        ]);
    }

    private function getExpenseTypeData($expenses)
    {
        return ExpenseType::withCount(['expenses' => function ($query) use ($expenses) {
            $query->whereIn('id', $expenses->pluck('id'));
        }])
            ->get()
            ->map(function ($type) use ($expenses) {
                $typeExpenses = $expenses->where('expense_type_id', $type->id);
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'value' => $typeExpenses->sum('amount'),
                    'count' => $typeExpenses->count(),
                    'percentage' => $expenses->sum('amount') > 0
                        ? ($typeExpenses->sum('amount') / $expenses->sum('amount')) * 100
                        : 0
                ];
            })
            ->filter(function ($type) {
                return $type['value'] > 0;
            })
            ->values();
    }

    public function getDashboardData()
    {
        $hierarchicalData = Secretary::with('departments.expenses')
            ->get()
            ->map(function ($secretary) {
                $children = $secretary->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                        'value' => $department->expenses->sum('amount'),
                    ];
                });

                return [
                    'id' => $secretary->id,
                    'name' => $secretary->name,
                    'children' => $children,
                    'value' => $children->sum('value')
                ];
            });

        return [
            'hierarchicalData' => $hierarchicalData,
            // outros dados necessários
        ];
    }
}
