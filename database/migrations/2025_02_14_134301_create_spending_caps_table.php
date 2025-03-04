<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('spending_caps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretary_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            // expense_type_id é opcional; se for null, indica teto geral
            $table->foreignId('expense_type_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('cap_value', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('spending_caps', function (Blueprint $table) {
            $table->dropForeign(['secretary_id']);
            $table->dropForeign(['expense_type_id']);
        });
        Schema::dropIfExists('spending_caps');
    }
};

