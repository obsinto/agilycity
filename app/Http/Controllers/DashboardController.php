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

        // Redireciona para o dashboard apropriado com base no papel do usuário
        if ($user->hasRole('mayor')) {
            return redirect()->route('mayor.dashboard');
        } elseif ($user->hasRole('secretary')) {
            return redirect()->route('secretary.dashboard');
        } elseif ($user->hasRole('sector_leader')) {
            return redirect()->route('sector.dashboard');
        }

        return view('dashboard.default');
    }

    public function mayorDashboard()
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
    /**
     * Método que filtra os dados de acordo com o papel do usuário e os filtros aplicados.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Método que filtra os dados de acordo com o papel do usuário e os filtros aplicados.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        $user = Auth::user();

        // Consulta base com eager loading das relações necessárias
        $query = Expense::with(['secretary', 'department', 'expenseType']);

        // Aplicar restrições de segurança baseadas no papel do usuário
        if ($user->hasRole('secretary') && $user->secretary) {
            // Secretário só pode ver dados de sua própria secretaria
            $query->whereHas('department', function ($q) use ($user) {
                $q->where('secretary_id', $user->secretary->id);
            });
        } elseif ($user->hasRole('sector_leader') && $user->department) {
            // Líder de setor só pode ver dados de seu próprio departamento
            $query->where('department_id', $user->department->id);
        }
        // Prefeito não tem restrições e pode ver todos os dados

        // Definir período de referência - padrão: mês atual
        $referenceStartDate = now()->startOfMonth();
        $referenceEndDate = now()->endOfMonth();

        // Filtro por data
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $referenceStartDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $referenceEndDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $query->whereBetween('expense_date', [$referenceStartDate, $referenceEndDate]);
        }

        // Aplicar filtros adicionais (respeitando as restrições de permissão)
        if ($request->filled('department_id')) {
            // Se for secretário, verificar se o departamento pertence à sua secretaria
            if ($user->hasRole('secretary') && $user->secretary) {
                $departmentIds = Department::where('secretary_id', $user->secretary->id)
                    ->pluck('id')
                    ->toArray();

                if (in_array($request->department_id, $departmentIds)) {
                    $query->where('department_id', $request->department_id);
                }
            } // Se for líder de setor, ignorar o filtro pois ele já está restrito ao seu departamento
            elseif (!$user->hasRole('sector_leader')) {
                // Apenas prefeito pode filtrar por qualquer departamento
                $query->where('department_id', $request->department_id);
            }
        }

        if ($request->filled('expense_type')) {
            $query->whereHas('expenseType', function ($q) use ($request) {
                $q->where('name', $request->expense_type);
            });
        }

        if ($request->filled('secretary_id')) {
            // Apenas prefeito pode filtrar por secretaria
            if (!$user->hasRole('secretary') && !$user->hasRole('sector_leader')) {
                $query->where('secretary_id', $request->secretary_id);
            }
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
            'secretary_id' => $user->hasRole('secretary') ? $user->secretary->id : $request->secretary_id,
            'department_id' => $user->hasRole('sector_leader') ? $user->department->id : $request->department_id,
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

        // Cálculo de totais com base no papel do usuário
        $totalExpenses = $expenses->sum('amount');

        // Para o prefeito, oferecemos tanto o total filtrado quanto o total geral
        $globalTotalExpenses = null;
        $globalCurrentMonthExpenses = null;
        $globalLastMonthExpenses = null;

        if ($user->hasRole('mayor')) {
            // Se filtros foram aplicados, oferecemos também os totais gerais sem filtros
            if ($request->filled('secretary_id') || $request->filled('department_id') || $request->filled('expense_type') ||
                ($request->filled('start_date') && $request->filled('end_date'))) {

                $globalTotalExpenses = Expense::sum('amount');

                // Cálculo do total do mês atual global
                $currentMonth = now()->format('Y-m');
                $globalCurrentMonthExpenses = Expense::whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$currentMonth])
                    ->sum('amount');

                // Cálculo do total do mês anterior global
                $lastMonth = now()->subMonth()->format('Y-m');
                $globalLastMonthExpenses = Expense::whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$lastMonth])
                    ->sum('amount');
            } else {
                // Se não há filtros, os totais já são globais
                $globalTotalExpenses = $totalExpenses;
                $globalCurrentMonthExpenses = $currentPeriodExpenses;
                $globalLastMonthExpenses = $lastMonthExpenses;
            }
        }

        // Retorna JSON com dados para o front-end
        return response()->json([
            'totalExpenses' => $totalExpenses,
            'globalTotalExpenses' => $globalTotalExpenses, // Apenas para o prefeito
            'currentMonthExpenses' => $currentPeriodExpenses,
            'globalCurrentMonthExpenses' => $globalCurrentMonthExpenses, // Apenas para o prefeito
            'lastMonthExpenses' => $lastMonthExpenses,
            'globalLastMonthExpenses' => $globalLastMonthExpenses, // Apenas para o prefeito
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
            'userRole' => $user->getRoleNames()->first() // Inclui o papel do usuário para o frontend saber o que exibir
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


    public function secretaryDashboard()
    {
        $user = Auth::user();

        // Verifica se o usuário tem uma secretaria associada
        if (!$user->secretary) {
            return redirect()->route('dashboard')->with('error', 'Secretaria não encontrada');
        }

        $secretary = $user->secretary;

        // Carrega os departamentos e suas despesas em uma única consulta
        $departments = Department::where('secretary_id', $secretary->id)
            ->with(['expenses' => function ($query) {
                $query->where('expense_date', '>=', now()->subYear()); // Filtra despesas do último ano
            }])
            ->get();

        // Tipos de despesa
        $expenseTypes = ExpenseType::all();

        // Despesas da secretaria (todos os departamentos)
        // Usa flatMap para combinar as coleções de despesas de todos os departamentos em uma única coleção
        $expenses = $departments->flatMap->expenses;

        // Total de gastos - APENAS DA SECRETARIA DO USUÁRIO
        $totalExpenses = $expenses->sum('amount');

        // Gastos do mês atual - APENAS DA SECRETARIA DO USUÁRIO
        $currentMonthExpenses = $expenses->filter(function ($expense) {
            return $expense->expense_date->format('Y-m') === now()->format('Y-m');
        })->sum('amount');

        // Gastos do mês anterior - APENAS DA SECRETARIA DO USUÁRIO
        $lastMonthExpenses = $expenses->filter(function ($expense) {
            return $expense->expense_date->format('Y-m') === now()->subMonth()->format('Y-m');
        })->sum('amount');

        // Departamento com maior gasto - APENAS DA SECRETARIA DO USUÁRIO
        $topDepartment = $departments->sortByDesc(function ($department) {
            return $department->expenses->sum('amount');
        })->first();

        // Dados para o gráfico de Evolução Mensal - APENAS DA SECRETARIA DO USUÁRIO
        $monthlyExpenses = $this->getMonthlyExpenses($secretary->id);

        return view('dashboard.secretary', compact(
            'secretary',
            'departments',
            'expenseTypes',
            'totalExpenses',
            'currentMonthExpenses',
            'lastMonthExpenses',
            'topDepartment',
            'monthlyExpenses'
        ));
    }

    /**
     * Retorna as despesas mensais da secretaria para o gráfico de Evolução Mensal
     */
    private function getMonthlyExpenses($secretaryId)
    {
        return Expense::whereHas('department', function ($query) use ($secretaryId) {
            $query->where('secretary_id', $secretaryId);
        })
            ->selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }


    /**
     * Dashboard para líderes de setor
     * Exibe informações e gráficos específicos para o departamento do líder de setor
     *
     * @return \Illuminate\View\View
     */
    /**
     * Dashboard para líderes de setor
     * Exibe informações e gráficos específicos para o departamento do líder de setor
     *
     * @return \Illuminate\View\View
     */
    public function sectorLeaderDashboard()
    {
        $user = Auth::user();

        // Verifica se o usuário tem um departamento associado
        if (!$user->department) {
            return redirect()->route('dashboard')->with('error', 'Departamento não encontrado');
        }

        $department = $user->department;

        // Carrega APENAS as despesas do departamento específico do líder
        $expenses = Expense::where('department_id', $department->id)
            ->where('expense_date', '>=', now()->subYear()) // Filtra despesas do último ano
            ->with(['expenseType'])
            ->get();

        // Tipos de despesa
        $expenseTypes = ExpenseType::all();

        // Total de gastos (apenas do departamento específico)
        $totalExpenses = $expenses->sum('amount');

        // Gastos do mês atual
        $currentMonthExpenses = $expenses->filter(function ($expense) {
            return $expense->expense_date->format('Y-m') === now()->format('Y-m');
        })->sum('amount');

        // Gastos do mês anterior
        $lastMonthExpenses = $expenses->filter(function ($expense) {
            return $expense->expense_date->format('Y-m') === now()->subMonth()->format('Y-m');
        })->sum('amount');

        // Teto de gastos (budget cap) para o departamento
        $budgetCap = $this->expenseCapService->getCapForExpense($department->id) ??
            config('app.default_department_budget', 15000);

        // Dados para o gráfico de Evolução Mensal
        $monthlyExpenses = $this->getMonthlyExpensesForDepartment($department->id);

        // Calcular dados por tipo de despesa para o gráfico de pizza
        $expenseTypeData = $this->getExpenseTypeDataForDepartment($expenses);

        // Despesas recentes (últimas 10)
        $recentExpenses = Expense::where('department_id', $department->id)
            ->with('expenseType')
            ->orderBy('expense_date', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.sector_leader', compact(
            'department',
            'expenses',
            'expenseTypes',
            'totalExpenses',
            'currentMonthExpenses',
            'lastMonthExpenses',
            'budgetCap',
            'monthlyExpenses',
            'expenseTypeData',
            'recentExpenses'
        ));
    }

    /**
     * Retorna as despesas mensais do departamento para o gráfico de Evolução Mensal
     *
     * @param int $departmentId ID do departamento
     * @return \Illuminate\Support\Collection Coleção de despesas agrupadas por mês
     */
    private function getMonthlyExpensesForDepartment($departmentId)
    {
        return Expense::where('department_id', $departmentId)
            ->where('expense_date', '>=', now()->subYear()) // Limita aos últimos 12 meses
            ->selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                // Formata o mês para ser mais amigável na exibição
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M/Y'),
                    'total' => $item->total,
                    'sort_year' => $item->year,
                    'sort_month' => $item->month
                ];
            });
    }

    /**
     * Calcula os dados por tipo de despesa para o departamento
     *
     * @param \Illuminate\Support\Collection $expenses Coleção de despesas do departamento
     * @return \Illuminate\Support\Collection Dados formatados para o gráfico de pizza
     */
    private function getExpenseTypeDataForDepartment($expenses)
    {
        return $expenses->groupBy('expense_type_id')
            ->map(function ($group, $typeId) use ($expenses) {
                $type = ExpenseType::find($typeId);
                return [
                    'id' => $typeId,
                    'name' => $type ? $type->name : 'Desconhecido',
                    'value' => $group->sum('amount'),
                    'count' => $group->count(),
                    'percentage' => $expenses->sum('amount') > 0
                        ? ($group->sum('amount') / $expenses->sum('amount')) * 100
                        : 0
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    /**
     * Método para filtrar dados do dashboard de setor via AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterSectorDashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user->department) {
            return response()->json(['error' => 'Departamento não encontrado'], 404);
        }

        $departmentId = $user->department->id;

        // Consulta base
        $query = Expense::where('department_id', $departmentId);

        // Definir período de referência - padrão: mês atual
        $referenceStartDate = now()->startOfMonth();
        $referenceEndDate = now()->endOfMonth();

        // Filtro por data
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $referenceStartDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $referenceEndDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $query->whereBetween('expense_date', [$referenceStartDate, $referenceEndDate]);
        }

        // Filtro por tipo de despesa
        if ($request->filled('expense_type')) {
            $query->whereHas('expenseType', function ($q) use ($request) {
                $q->where('name', $request->expense_type);
            });
        }

        // Executa a consulta
        $expenses = $query->with('expenseType')->get();

        // Calcula dados para períodos atual e anterior
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
        $previousPeriodExpenses = Expense::where('department_id', $departmentId)
            ->whereBetween('expense_date', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('amount');

        // Teto de gastos
        $budgetCap = $this->expenseCapService->getCapForExpense($departmentId) ??
            config('app.default_department_budget', 15000);

        // Retorna JSON com dados para o front-end
        return response()->json([
            'totalExpenses' => $expenses->sum('amount'),
            'currentMonthExpenses' => $currentPeriodExpenses,
            'lastMonthExpenses' => $previousPeriodExpenses,
            'budgetCap' => $budgetCap,
            'monthlyExpenses' => $this->getMonthlyData($expenses),
            'expenseTypeData' => $this->getExpenseTypeDataForDepartment($expenses),
            'referenceStartDate' => $referenceStartDate->format('Y-m-d'),
            'referenceEndDate' => $referenceEndDate->format('Y-m-d'),
        ]);
    }

    /**
     * Método para filtrar dados do dashboard de secretário via AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterSecretaryDashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user->secretary) {
            return response()->json(['error' => 'Secretaria não encontrada'], 404);
        }

        $secretaryId = $user->secretary->id;

        // Consulta base - apenas despesas dos departamentos da secretaria do usuário
        $query = Expense::whereHas('department', function ($q) use ($secretaryId) {
            $q->where('secretary_id', $secretaryId);
        });

        // Definir período de referência - padrão: mês atual
        $referenceStartDate = now()->startOfMonth();
        $referenceEndDate = now()->endOfMonth();

        // Filtro por data
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $referenceStartDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $referenceEndDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $query->whereBetween('expense_date', [$referenceStartDate, $referenceEndDate]);
        }

        // Filtro por departamento (apenas da secretaria do usuário)
        if ($request->filled('department_id')) {
            $departmentIds = Department::where('secretary_id', $secretaryId)
                ->pluck('id')
                ->toArray();

            if (in_array($request->department_id, $departmentIds)) {
                $query->where('department_id', $request->department_id);
            }
        }

        // Filtro por tipo de despesa
        if ($request->filled('expense_type')) {
            $query->whereHas('expenseType', function ($q) use ($request) {
                $q->where('name', $request->expense_type);
            });
        }

        // Executa a consulta
        $expenses = $query->with(['department', 'expenseType'])->get();

        // Carrega os departamentos da secretaria
        $departments = Department::where('secretary_id', $secretaryId)->with('expenses')->get();

        // Calcula dados para períodos atual e anterior
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
        $previousPeriodExpenses = Expense::whereHas('department', function ($q) use ($secretaryId) {
            $q->where('secretary_id', $secretaryId);
        })
            ->whereBetween('expense_date', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('amount');

        // Prepara os dados dos departamentos (para gráfico de barras)
        $departmentsData = $departments->map(function ($department) use ($expenses) {
            $deptExpenses = $expenses->where('department_id', $department->id);
            return [
                'id' => $department->id,
                'name' => $department->name,
                'total' => $deptExpenses->sum('amount')
            ];
        })
            ->filter(function ($dept) {
                return $dept['total'] > 0;
            })
            ->sortByDesc('total')
            ->values();

        // Prepara dados de tipo de despesa (para gráfico de pizza)
        $expenseTypeData = ExpenseType::withCount(['expenses' => function ($query) use ($expenses) {
            $query->whereIn('id', $expenses->pluck('id'));
        }])
            ->get()
            ->map(function ($type) use ($expenses) {
                $typeExpenses = $expenses->where('expense_type_id', $type->id);
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'value' => $typeExpenses->sum('amount'),
                    'count' => $typeExpenses->count()
                ];
            })
            ->filter(function ($type) {
                return $type['value'] > 0;
            })
            ->values();

        // Prepara dados para o gráfico treemap
        $hierarchicalData = [
            'name' => $user->secretary->name,
            'value' => $expenses->sum('amount'),
            'children' => $departments->map(function ($department) use ($expenses) {
                $deptExpenses = $expenses->where('department_id', $department->id);
                return [
                    'name' => $department->name,
                    'id' => $department->id,
                    'value' => $deptExpenses->sum('amount')
                ];
            })
                ->filter(function ($dept) {
                    return $dept['value'] > 0;
                })
                ->values()
        ];

        // Dados mensais para o gráfico de timeline
        $monthlyExpenses = $expenses
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
                ['sort_year', 'asc'],
                ['sort_month', 'asc']
            ])
            ->values();

        // Prepara os dados para as séries do gráfico de timeline (por departamento)
        $series = $departments
            ->map(function ($department) use ($monthlyExpenses, $expenses) {
                $departmentExpenses = $expenses->where('department_id', $department->id);

                return [
                    'name' => $department->name,
                    'type' => 'line',
                    'smooth' => true,
                    'data' => $monthlyExpenses->map(function ($monthData) use ($departmentExpenses) {
                        $monthDate = Carbon::createFromFormat('M/Y', $monthData['month'], 'America/Sao_Paulo');
                        $yearMonth = $monthDate->format('Y-m');

                        return $departmentExpenses
                            ->filter(function ($expense) use ($yearMonth) {
                                return $expense->expense_date->format('Y-m') === $yearMonth;
                            })
                            ->sum('amount');
                    })->values()
                ];
            })
            ->filter(function ($series) {
                // Remove séries que não têm dados (todos zeros)
                return collect($series['data'])->sum() > 0;
            })
            ->values();

        // Retorna JSON com dados para o front-end
        return response()->json([
            'totalExpenses' => $expenses->sum('amount'),
            'currentMonthExpenses' => $currentPeriodExpenses,
            'lastMonthExpenses' => $previousPeriodExpenses,
            'departmentsData' => $departmentsData,
            'monthlyExpenses' => $monthlyExpenses,
            'hierarchicalData' => $hierarchicalData,
            'expenseTypeData' => $expenseTypeData,
            'series' => $series,
            'referenceStartDate' => $referenceStartDate->format('Y-m-d'),
            'referenceEndDate' => $referenceEndDate->format('Y-m-d'),
        ]);
    }

}
