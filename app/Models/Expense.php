<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_type_id',
        'department_id',
        'secretary_id',
        'amount',
        'expense_date',
        'observation',
        'attachment'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }
}
