<?php

namespace App\Services;

use App\Models\ExpenseType;
use App\Models\Secretary;
use App\Models\SpendingCap;
use Illuminate\Support\Facades\Log;

class ExpenseCapService
{
    // Constante para indicar que não há teto definido
    const NO_CAP_DEFINED = 0;

    /**
     * Obtém o teto de gastos com base em filtros
     *
     * @param array $filters Array com filtros (secretary_id, department_id, expense_type)
     * @return float Valor do teto de gastos ou 0 se não houver teto específico
     */
    public function getCapForFilters(array $filters)
    {
        // Extrai os IDs dos filtros
        $secretaryId = $filters['secretary_id'] ?? null;
        $departmentId = $filters['department_id'] ?? null;
        $expenseTypeName = $filters['expense_type'] ?? null;

        // Se não tiver uma secretaria específica, calcular o teto geral da prefeitura
        if (!$secretaryId) {
            return $this->calculateTotalCityBudgetCap();
        }

        // Se tiver secretaria mas não tiver tipo de despesa, busca o teto geral da secretaria
        if (!$expenseTypeName) {
            return $this->getGeneralCapForSecretary($secretaryId, $departmentId);
        }

        // Se tiver tipo de despesa, busca o teto específico
        $expenseTypeId = null;
        if ($expenseTypeName) {
            $expenseType = ExpenseType::where('name', $expenseTypeName)->first();
            if ($expenseType) {
                $expenseTypeId = $expenseType->id;
            }
        }

        // Busca o teto específico para o tipo de despesa
        $specificCap = $this->getSpecificCapForExpenseType($secretaryId, $departmentId, $expenseTypeId);

        // Se não encontrou um teto específico para o tipo de despesa, retorna 0
        if ($specificCap === null) {
            Log::info('Nenhum teto definido para o tipo de despesa', [
                'secretary_id' => $secretaryId,
                'expense_type_id' => $expenseTypeId,
                'expense_type_name' => $expenseTypeName
            ]);
            return self::NO_CAP_DEFINED; // Retorna 0 indicando que não há teto
        }

        return $specificCap;
    }

    /**
     * Obtém o teto geral para uma secretaria (sem tipo de despesa específico)
     *
     * @param int $secretaryId ID da secretaria
     * @param int|null $departmentId ID do departamento (opcional)
     * @return float Valor do teto geral
     */
    public function getGeneralCapForSecretary($secretaryId, $departmentId = null)
    {
        // Primeiro busca um teto geral para secretaria + departamento
        if ($departmentId) {
            $departmentCap = SpendingCap::where('secretary_id', $secretaryId)
                ->where('department_id', $departmentId)
                ->whereNull('expense_type_id')
                ->first();

            if ($departmentCap) {
                Log::info('Teto geral encontrado para secretaria+departamento:', [
                    'secretary_id' => $secretaryId,
                    'department_id' => $departmentId,
                    'cap_value' => $departmentCap->cap_value
                ]);
                return $departmentCap->cap_value;
            }
        }

        // Se não encontrou, busca o teto geral só da secretaria
        $secretaryCap = SpendingCap::where('secretary_id', $secretaryId)
            ->whereNull('department_id')
            ->whereNull('expense_type_id')
            ->first();

        if ($secretaryCap) {
            Log::info('Teto geral encontrado para secretaria:', [
                'secretary_id' => $secretaryId,
                'cap_value' => $secretaryCap->cap_value
            ]);
            return $secretaryCap->cap_value;
        }

        // Se não encontrou nenhum teto, usa o valor padrão
        $defaultCap = config('app.default_monthly_budget', 30000);
        Log::info('Nenhum teto geral encontrado, usando padrão:', [
            'secretary_id' => $secretaryId,
            'default_cap' => $defaultCap
        ]);

        return $defaultCap;
    }

    /**
     * Obtém o teto específico para um tipo de despesa
     *
     * @param int $secretaryId ID da secretaria
     * @param int|null $departmentId ID do departamento (opcional)
     * @param int|null $expenseTypeId ID do tipo de despesa
     * @return float|null Valor do teto específico, ou null se não encontrar
     */
    public function getSpecificCapForExpenseType($secretaryId, $departmentId = null, $expenseTypeId = null)
    {
        if (!$expenseTypeId) {
            return null;
        }

        // Busca o teto específico para secretaria + departamento + tipo de despesa
        if ($departmentId) {
            $specificCap = SpendingCap::where('secretary_id', $secretaryId)
                ->where('department_id', $departmentId)
                ->where('expense_type_id', $expenseTypeId)
                ->first();

            if ($specificCap) {
                Log::info('Teto específico encontrado para departamento+tipo:', [
                    'secretary_id' => $secretaryId,
                    'department_id' => $departmentId,
                    'expense_type_id' => $expenseTypeId,
                    'cap_value' => $specificCap->cap_value
                ]);
                return $specificCap->cap_value;
            }
        }

        // Busca o teto específico para secretaria + tipo de despesa
        $specificCap = SpendingCap::where('secretary_id', $secretaryId)
            ->whereNull('department_id')
            ->where('expense_type_id', $expenseTypeId)
            ->first();

        if ($specificCap) {
            Log::info('Teto específico encontrado para secretaria+tipo:', [
                'secretary_id' => $secretaryId,
                'expense_type_id' => $expenseTypeId,
                'cap_value' => $specificCap->cap_value
            ]);
            return $specificCap->cap_value;
        }

        // Se não encontrou um teto específico, retorna null
        return null;
    }

    /**
     * Calcula o teto de gastos total da prefeitura somando os tetos gerais de todas as secretarias
     *
     * @return float Valor total do teto de gastos da prefeitura
     */
    public function calculateTotalCityBudgetCap()
    {
        // Busca todas as secretarias
        $secretaries = Secretary::all();
        $totalCap = 0;
        $defaultCapUsedCount = 0;

        Log::info('Calculando teto geral da prefeitura...');

        // Para cada secretaria, busca apenas seu teto geral (não soma os específicos por tipo)
        foreach ($secretaries as $secretary) {
            $generalCap = $this->getGeneralCapForSecretary($secretary->id);
            $totalCap += $generalCap;

            if ($generalCap == config('app.default_monthly_budget', 30000)) {
                $defaultCapUsedCount++;
            }

            Log::info('Adicionando teto da secretaria ' . $secretary->name, [
                'secretary_id' => $secretary->id,
                'general_cap' => $generalCap,
                'is_default' => ($generalCap == config('app.default_monthly_budget', 30000))
            ]);
        }

        Log::info('Teto geral da prefeitura calculado', [
            'total_cap' => $totalCap,
            'secretaries_count' => $secretaries->count(),
            'default_cap_used_count' => $defaultCapUsedCount
        ]);

        return $totalCap;
    }

    /**
     * Método de compatibilidade para código existente
     * Mantido para não quebrar chamadas existentes
     */
    public function getCapForExpense($secretaryId, $departmentId = null, $expenseTypeId = null)
    {
        if ($expenseTypeId) {
            $specificCap = $this->getSpecificCapForExpenseType($secretaryId, $departmentId, $expenseTypeId);
            if ($specificCap !== null) {
                return $specificCap;
            }
        }

        return $this->getGeneralCapForSecretary($secretaryId, $departmentId);
    }
}
