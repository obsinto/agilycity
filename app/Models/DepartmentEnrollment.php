<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/DepartmentEnrollment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'year',
        'month',
        'students_count',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

