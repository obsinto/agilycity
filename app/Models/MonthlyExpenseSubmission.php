<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyExpenseSubmission extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'department_id',
        'year',
        'month',
        'is_submitted',
        'submitted_at',
        'submitted_by',
        'notes'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'is_submitted' => 'boolean',
        'submitted_at' => 'datetime',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Obter o departamento associado à submissão.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Obter o usuário que enviou a submissão.
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Obter o nome do mês.
     *
     * @return string
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
