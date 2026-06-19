<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->string('parent_code', 20)->nullable();
            $table->tinyInteger('level');
            $table->enum('category', [
                'aktiva', 'kewajiban', 'modal', 'pendapatan', 'hpp',
                'biaya_operasional', 'pendapatan_biaya_lain', 'biaya_bunga', 'pajak_penghasilan'
            ]);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->enum('report_type', ['balance_sheet', 'income_statement']);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_code');
            $table->index('level');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
