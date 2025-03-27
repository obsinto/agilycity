<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active','is_meal_related'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
