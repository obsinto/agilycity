<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Secretary extends Model
{
    protected $fillable = ['name', 'description'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function secretary(): HasOne
    {
        return $this->hasOne(User::class, 'secretary_id')->role('secretary');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isMonthClosed($year, $month)
    {
        // Conta quantos departamentos estão nesta secretaria
        $totalDepartments = $this->departments()->count();

        if ($totalDepartments === 0) {
            return true;
        }

        // Conta quantos departamentos fecharam o mês
        $closedDepartments = $this->departments()
            ->whereHas('monthlySubmissions', function ($query) use ($year, $month) {
                $query->where('year', $year)
                    ->where('month', $month)
                    ->where('is_submitted', true);
            })
            ->count();

        // Retorna true se todos os departamentos estiverem fechados
        return $totalDepartments === $closedDepartments;
    }
}

