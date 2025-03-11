<?php

// database/migrations/2023_xx_xx_create_department_enrollments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                ->constrained()
                ->onDelete('cascade');
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('students_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_enrollments');
    }
};

