<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'total_amount',
        'created_by',
    ];

    /**
     * Relacionamento com o usuário que criou o registro
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Retorna o nome do mês formatado
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        return $months[$this->month] ?? '';
    }
}
