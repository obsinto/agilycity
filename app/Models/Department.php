<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'secretary_id', 'description'];

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(Secretary::class);
    }

    public function sectorLeaders(): HasMany
    {
        return $this->hasMany(User::class, 'department_id')->role('sector_leader');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function enrollments()
    {
        return $this->hasMany(DepartmentEnrollment::class);
    }

    public function scopeSchools($query)
    {
        return $query->where('is_school', true);
    }

}
