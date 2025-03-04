<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\SpendingCap;
use Carbon\Carbon;

class ExpenseCapService
{
    /**
     * Get cap value for given expense parameters
     */
    public function getCapForExpense($secretaryId, $departmentId = null, $expenseType = null)
    {
        // Procura por um teto configurado
        $query = SpendingCap::query();

        if ($secretaryId) {
            $query->where('secretary_id', $secretaryId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Trata o expense_type (pode ser ID ou nome)
        if ($expenseType) {
            $expenseTypeId = null;

            // Se for string, busca o ID do tipo de despesa
            if (is_string($expenseType)) {
                $expenseTypeId = ExpenseType::where('name', $expenseType)->value('id');
            } else {
                $expenseTypeId = $expenseType;
            }

            if ($expenseTypeId) {
                $query->where('expense_type_id', $expenseTypeId);
            }
        }

        $spendingCap = $query->first();

        if ($spendingCap) {
            return $spendingCap->cap_value;
        }

        // Caso não exista, calcula a média dos últimos 3 meses
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        $query = Expense::whereBetween('expense_date', [$startDate, $endDate]);

        if ($secretaryId) {
            $query->where('secretary_id', $secretaryId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Trata o expense_type na query de expenses
        if ($expenseType) {
            if (is_string($expenseType)) {
                $query->whereHas('expenseType', function ($q) use ($expenseType) {
                    $q->where('name', $expenseType);
                });
            } else {
                $query->where('expense_type_id', $expenseType);
            }
        }

        // Soma os gastos e divide pelo número de meses (3)
        $total = $query->sum('amount');
        $average = $total / 3;

        // Adiciona 20% de margem ao valor médio
        return $average * 1.2;
    }

    /**
     * Get cap value based on filter array
     */
    public function getCapForFilters(array $filters)
    {
        return $this->getCapForExpense(
            $filters['secretary_id'] ?? null,
            $filters['department_id'] ?? null,
            $filters['expense_type'] ?? null
        );
    }
}
