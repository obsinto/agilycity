<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_type_id',
        'department_id',
        'secretary_id',
        'amount',
        'start_date',
        'end_date',
        'observation',
        'name',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relacionamento com tipo de despesa
     */
    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    /**
     * Relacionamento com departamento
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relacionamento com secretaria
     */
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    /**
     * Verifica se a despesa fixa está ativa para um mês específico
     */
    public function isActiveForMonth($year, $month)
    {
        if ($this->status !== 'active') {
            return false;
        }

        $targetDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $startDate = $this->start_date->startOfMonth();

        // Se tiver data de fim, verifica se o mês/ano alvo está após a data de fim
        if ($this->end_date) {
            $endDate = $this->end_date->endOfMonth();
            return $targetDate->between($startDate, $endDate);
        }

        // Se não tiver data de fim, verifica se o mês/ano alvo é igual ou posterior à data de início
        return $targetDate->greaterThanOrEqualTo($startDate);
    }
}
