<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\FixedExpense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FixedExpenseController extends Controller
{
    /**
     * Display a listing of fixed expenses.
     */
    public function index()
    {
        $user = auth()->user();
        $query = FixedExpense::with(['expenseType', 'department', 'secretary']);

        if ($user->hasRole('secretary')) {
            $query->where('secretary_id', $user->secretary_id);
        } else {
            // Líder de setor só vê despesas do seu departamento
            $query->where('department_id', $user->department_id);
        }

        $fixedExpenses = $query->latest()->paginate(15);
        $types = ExpenseType::where('active', true)->get();

        if ($user->hasRole('secretary')) {
            $departments = Department::where('secretary_id', $user->secretary_id)->get();
        } else {
            $departments = collect([$user->department]); // Apenas o departamento dele
        }

        return view('expenses.fixed', compact('fixedExpenses', 'types', 'departments'));
    }

    /**
     * Store a newly created fixed expense.
     */
    public function store(Request $request)
    {
        // Para debug
        \Log::info('Dados recebidos de despesa fixa:', $request->all());

        $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'name' => 'required|string|max:255',
            'observation' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $data = $request->all();

            // Se for líder de setor, usa o departamento dele
            if ($user->hasRole('sector_leader')) {
                $data['department_id'] = $user->department_id;
                $data['secretary_id'] = $user->department->secretary_id;
            } else {
                // Se for secretário, usa a secretaria dele
                $data['secretary_id'] = $user->secretary_id;
            }

            $fixedExpense = FixedExpense::create($data);

            // Gerar despesas para os meses já passados desde a data de início
            $this->generatePastExpenses($fixedExpense);

            return redirect()->route('fixed-expenses.index')
                ->with('success', 'Despesa fixa registrada com sucesso!');

        } catch (\Exception $e) {
            // Para debug
            \Log::error('Erro ao criar despesa fixa:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao registrar despesa fixa: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified fixed expense.
     */
    public function update(Request $request, FixedExpense $fixedExpense)
    {
        $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'name' => 'required|string|max:255',
            'observation' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Verifica se o valor mudou para criar um histórico
        $oldAmount = $fixedExpense->amount;
        $newAmount = $request->amount;
        $changeDate = Carbon::now();

        // Atualiza a despesa fixa
        $fixedExpense->update($request->all());

        // Se o valor mudou, cria uma nova despesa fixa com o valor antigo
        if ($oldAmount != $newAmount) {
            // Desativa a despesa fixa antiga na data da mudança
            $fixedExpense->end_date = $changeDate->subDay();
            $fixedExpense->save();

            // Cria uma nova despesa fixa com o novo valor a partir de hoje
            $newFixedExpense = $fixedExpense->replicate();
            $newFixedExpense->amount = $newAmount;
            $newFixedExpense->start_date = Carbon::now();
            $newFixedExpense->end_date = $request->end_date;
            $newFixedExpense->save();
        }

        return redirect()->route('fixed-expenses.index')
            ->with('success', 'Despesa fixa atualizada com sucesso!');
    }

    /**
     * Gera despesas para os meses já passados desde a data de início
     */
    private function generatePastExpenses(FixedExpense $fixedExpense)
    {
        $startDate = Carbon::parse($fixedExpense->start_date)->startOfMonth();
        $currentDate = Carbon::now()->endOfMonth();

        // Se a data de início for no futuro, não gera despesas
        if ($startDate->greaterThan($currentDate)) {
            return;
        }

        // Gera despesas para cada mês entre a data de início e a data atual
        while ($startDate->lessThanOrEqualTo($currentDate)) {
            // Verifica se já existe uma despesa para este mês/ano
            $exists = Expense::where('expense_type_id', $fixedExpense->expense_type_id)
                ->where('department_id', $fixedExpense->department_id)
                ->whereYear('expense_date', $startDate->year)
                ->whereMonth('expense_date', $startDate->month)
                ->where('observation', 'LIKE', '%[Despesa Fixa: ' . $fixedExpense->id . ']%')
                ->exists();

            // Se não existir, cria a despesa
            if (!$exists) {
                Expense::create([
                    'expense_type_id' => $fixedExpense->expense_type_id,
                    'department_id' => $fixedExpense->department_id,
                    'secretary_id' => $fixedExpense->secretary_id,
                    'amount' => $fixedExpense->amount,
                    'expense_date' => $startDate->copy()->day(15), // Usamos o dia 15 como padrão
                    'observation' => ($fixedExpense->observation ? $fixedExpense->observation . "\n" : '') .
                        "[Despesa Fixa: {$fixedExpense->id}] {$fixedExpense->name}"
                ]);
            }

            // Avança para o próximo mês
            $startDate->addMonth();
        }
    }

    /**
     * Executa a geração automática de despesas fixas para o mês atual
     * Este método pode ser chamado por um job agendado
     */
    public function generateMonthlyExpenses()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->year;
        $month = $currentDate->month;

        // Busca todas as despesas fixas ativas
        $fixedExpenses = FixedExpense::where('status', 'active')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate->startOfMonth());
            })
            ->where('start_date', '<=', $currentDate->endOfMonth())
            ->get();

        $count = 0;
        foreach ($fixedExpenses as $fixedExpense) {
            // Verifica se já existe uma despesa para este mês/ano
            $exists = Expense::where('expense_type_id', $fixedExpense->expense_type_id)
                ->where('department_id', $fixedExpense->department_id)
                ->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month)
                ->where('observation', 'LIKE', '%[Despesa Fixa: ' . $fixedExpense->id . ']%')
                ->exists();

            // Se não existir, cria a despesa
            if (!$exists) {
                Expense::create([
                    'expense_type_id' => $fixedExpense->expense_type_id,
                    'department_id' => $fixedExpense->department_id,
                    'secretary_id' => $fixedExpense->secretary_id,
                    'amount' => $fixedExpense->amount,
                    'expense_date' => $currentDate->copy()->day(15), // Usamos o dia 15 como padrão
                    'observation' => ($fixedExpense->observation ? $fixedExpense->observation . "\n" : '') .
                        "[Despesa Fixa: {$fixedExpense->id}] {$fixedExpense->name}"
                ]);
                $count++;
            }
        }

        return "Foram geradas {$count} despesas fixas para {$month}/{$year}.";
    }

    /**
     * Show the form for editing the specified fixed expense.
     */
    public function edit(FixedExpense $fixedExpense)
    {
        // Verificar se o usuário tem permissão para editar esta despesa fixa
        $user = auth()->user();

        if ($user->hasRole('secretary') && $fixedExpense->secretary_id != $user->secretary_id) {
            abort(403, 'Você não tem permissão para editar esta despesa fixa.');
        } elseif (!$user->hasRole('secretary') && $fixedExpense->department_id != $user->department_id) {
            abort(403, 'Você não tem permissão para editar esta despesa fixa.');
        }

        return response()->json($fixedExpense);
    }
}
