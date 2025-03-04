<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function index()
    {
        $types = ExpenseType::orderBy('name')->get();
        return view('expenses.types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        ExpenseType::create($request->all());
        return redirect()->route('expense-types.index')->with('success', 'Tipo de despesa criado com sucesso!');
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $expenseType->update($request->all());
        return redirect()->route('expense-types.index')->with('success', 'Tipo de despesa atualizado com sucesso!');
    }

    public function destroy(ExpenseType $expenseType)
    {
        if ($expenseType->expenses()->exists()) {
            return redirect()->route('expense-types.index')
                ->with('error', 'Não é possível excluir um tipo que possui despesas vinculadas.');
        }

        $expenseType->delete();
        return redirect()->route('expense-types.index')->with('success', 'Tipo de despesa removido com sucesso!');
    }
}
