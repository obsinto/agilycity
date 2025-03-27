<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentEnrollment;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\MonthlyMeal;
use Illuminate\Http\Request;

class MonthlyMealController extends Controller
{
    /**
     * Exibe o formulário para cadastro e edição dos valores mensais de merenda,
     * utilizando as despesas específicas de merenda escolar.
     */
    public function index(Request $request)
    {
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

        // Buscar o tipo de despesa "Merenda Escolar"
        $merendaType = ExpenseType::where('name', 'Merenda Escolar')
            ->orWhere('is_meal_related', true)
            ->first();

        // Se não encontrou, tentar criar um tipo padrão
        if (!$merendaType) {
            // Verificar se a tabela tem a coluna is_meal_related
            $hasColumn = false;
            try {
                $hasColumn = \Schema::hasColumn('expense_types', 'is_meal_related');
            } catch (\Exception $e) {
                $hasColumn = false;
            }

            // Criar o tipo de despesa padrão
            $merendaType = ExpenseType::create([
                'name' => 'Merenda Escolar',
                'description' => 'Despesas com alimentação escolar',
                'active' => true,
                'is_meal_related' => $hasColumn ? true : null,
            ]);
        }

        // Calcular o total de despesas de merenda para o mês selecionado
        $totalMerendaMes = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->where('expense_type_id', $merendaType->id)
            ->sum('amount');

        // Se existe um registro mensal, atualizamos com o valor das despesas
        if ($monthlyMeal) {
            $monthlyMeal->total_amount = $totalMerendaMes;
            $monthlyMeal->save();
        } else if ($totalMerendaMes > 0) {
            // Se não existe um registro mas temos despesas, criamos um novo
            $monthlyMeal = MonthlyMeal::create([
                'year' => $year,
                'month' => $month,
                'total_amount' => $totalMerendaMes,
                'created_by' => auth()->id()
            ]);
        }

        // Valor por aluno (com base nas despesas específicas de merenda)
        $valorPorAluno = 0;
        if ($totalAlunos > 0) {
            $valorPorAluno = $totalMerendaMes / $totalAlunos;
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

        // Buscar despesas recentes de merenda
        $recentExpenses = Expense::where('expense_type_id', $merendaType->id)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->orderByDesc('expense_date')
            ->get();

        return view('meals.monthly', [
            'year' => $year,
            'month' => $month,
            'monthlyMeal' => $monthlyMeal,
            'availableMonths' => $availableMonths,
            'totalAlunos' => $totalAlunos,
            'valorPorAluno' => $valorPorAluno,
            'schoolsData' => $schoolsData,
            'recentExpenses' => $recentExpenses,
            'totalMerendaMes' => $totalMerendaMes,
            'merendaType' => $merendaType
        ]);
    }

    /**
     * Salva uma nova despesa de merenda escolar diretamente da tela de merenda mensal.
     */
    public function storeExpense(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->can('manage monthly meals')) {
            abort(403, 'Acesso não autorizado');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'observation' => 'nullable|string|max:1000',
        ]);

        // Buscar o tipo de despesa "Merenda Escolar"
        $merendaType = ExpenseType::where('name', 'Merenda Escolar')
            ->orWhere('is_meal_related', true)
            ->first();

        if (!$merendaType) {
            // Criar o tipo padrão se não existir
            $merendaType = ExpenseType::create([
                'name' => 'Merenda Escolar',
                'description' => 'Despesas com alimentação escolar',
                'active' => true,
                'is_meal_related' => true,
            ]);
        }

        // Buscar departamento da Cantina Central
        $cantinaDept = Department::where('name', 'Cantina Central')->first();
        if (!$cantinaDept) {
            return redirect()->route('monthly-meals.index', [
                'year' => $request->year,
                'month' => $request->month
            ])->with('error', 'Departamento Cantina Central não encontrado!');
        }

        // Criar a despesa
        Expense::create([
            'expense_type_id' => $merendaType->id,
            'department_id' => $cantinaDept->id,
            'secretary_id' => $cantinaDept->secretary_id ?? 1, // Assumindo que a cantina tem uma secretaria associada
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'observation' => $request->observation,
        ]);

        return redirect()->route('monthly-meals.index', [
            'year' => $request->year,
            'month' => $request->month
        ])->with('success', 'Despesa de merenda registrada com sucesso!');
    }

    /**
     * Mantido por compatibilidade, mas agora os valores são calculados
     * automaticamente a partir das despesas específicas.
     */
    public function store(Request $request)
    {
        // Verificar permissão
        if (!auth()->user()->can('manage monthly meals')) {
            abort(403, 'Acesso não autorizado');
        }

        return redirect()
            ->route('monthly-meals.index', ['year' => $request->year, 'month' => $request->month])
            ->with('info', 'Os valores agora são calculados automaticamente a partir das despesas específicas de merenda.');
    }
}
