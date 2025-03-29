<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_expense_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->boolean('is_submitted')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Garante que só haverá um registro por departamento/ano/mês
            $table->unique(['department_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expense_submissions');
    }
};
