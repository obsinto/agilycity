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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->foreignId('secretary_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('observation')->nullable();
//            $table->string('invoice_number')->nullable();
            $table->string('attachment')->nullable(); // Para arquivos/comprovantes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
