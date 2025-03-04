<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendingCap extends Model
{
    use HasFactory;

    protected $fillable = [
        'secretary_id',
        'expense_type_id',
        'cap_value',
    ];

    // Relação com a Secretaria
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    // Relação com o Tipo de Despesa (opcional)
    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
