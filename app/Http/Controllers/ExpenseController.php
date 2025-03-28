<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // ExpenseController.php - método index
    public function index()
    {
        $user = auth()->user();
        $query = Expense::with(['expenseType', 'department', 'secretary']);

        if ($user->hasRole('secretary')) {
            $query->where('secretary_id', $user->secretary_id);
            $departments = Department::where('secretary_id', $user->secretary_id)->get();
        } else {
            // Líder de setor só vê despesas do seu departamento
            $query->where('department_id', $user->department_id);
            $departments = collect([$user->department]); // Apenas o departamento dele
        }

        $expenses = $query->latest()->paginate(15);
        $types = ExpenseType::where('active', true)->get();

        return view('expenses.index', compact('expenses', 'types', 'departments'));
    }

    public function store(Request $request)
    {
        // Para debug
        \Log::info('Dados recebidos:', $request->all());

        $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'observation' => 'nullable|string',
            'attachment' => 'nullable|file|max:2048',
            'is_fixed' => 'nullable|boolean' // Novo campo para indicar se é uma despesa fixa
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

            // Verifica se é uma despesa fixa
            if ($request->has('is_fixed') && $request->is_fixed) {
                // Redireciona para o formulário de despesa fixa com os dados preenchidos
                return redirect()->route('fixed-expenses.create')
                    ->with('data', $data);
            }

            // Para debug
            \Log::info('Dados para criar:', $data);

            $expense = Expense::create($data);

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('expenses', 'public');
                $expense->attachment = $path;
                $expense->save();
            }

            return redirect()->route('expenses.index')
                ->with('success', 'Despesa registrada com sucesso!');

        } catch (\Exception $e) {
            // Para debug
            \Log::error('Erro ao criar despesa:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao registrar despesa: ' . $e->getMessage());
        }
    }

}
