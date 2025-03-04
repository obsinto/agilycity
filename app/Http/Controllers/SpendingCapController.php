<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpendingCapRequest;
use App\Models\ExpenseType;
use App\Models\Secretary;
use App\Models\SpendingCap;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;

class SpendingCapController extends Controller
{
    use HasRoles, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // Apenas o prefeito deverá acessar estes métodos:

    // Lista todos os tetos de gastos
    public function index()
    {
        $spendingCaps = SpendingCap::with(['secretary', 'expenseType'])->get();
        return view('spending_caps.index', compact('spendingCaps'));
    }

    // Exibe o formulário de criação
    public function create()
    {
        $secretaries = Secretary::all();
        $expenseTypes = ExpenseType::all();
        return view('spending_caps.create', compact('secretaries', 'expenseTypes'));
    }

    // Armazena o novo teto de gastos
    public function store(SpendingCapRequest $request)
    {
        SpendingCap::create($request->validated());

        return redirect()->route('spending-caps.index')->with('success', 'Teto de gastos criado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(SpendingCap $spendingCap)
    {
        $secretaries = Secretary::all();
        $expenseTypes = ExpenseType::all();
        return view('spending_caps.edit', compact('spendingCap', 'secretaries', 'expenseTypes'));
    }

    // Atualiza o registro
    public function update(Request $request, SpendingCap $spendingCap)
    {
        $validated = $request->validate([
            'secretary_id' => 'required|exists:secretaries,id',
            'expense_type_id' => 'nullable|exists:expense_types,id',
            'cap_value' => 'required|numeric|min:0',
        ]);

        $spendingCap->update($validated);

        return redirect()->route('spending-caps.index')->with('success', 'Teto de gastos atualizado com sucesso!');
    }

    // Remove o registro
    public function destroy(SpendingCap $spendingCap)
    {
        $spendingCap->delete();
        return redirect()->route('spending-caps.index')->with('success', 'Teto de gastos removido com sucesso!');
    }
}
