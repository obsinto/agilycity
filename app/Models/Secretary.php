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
}
