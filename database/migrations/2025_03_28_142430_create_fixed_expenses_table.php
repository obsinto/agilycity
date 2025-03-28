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
        Schema::create('fixed_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->foreignId('secretary_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->date('start_date'); // Data de início da despesa fixa
            $table->date('end_date')->nullable(); // Data de fim (opcional)
            $table->text('observation')->nullable();
            $table->string('name')->nullable(); // Nome para identificar a despesa fixa
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_expenses');
    }
};
