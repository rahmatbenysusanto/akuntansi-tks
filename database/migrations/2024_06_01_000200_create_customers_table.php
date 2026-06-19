<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('code', 20);
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('npwp', 30)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->decimal('credit_limit', 18, 2)->default(0);
            $table->foreignId('ar_account_id')->nullable()->constrained('accounts');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('code', 20);
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('npwp', 30)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->foreignId('ap_account_id')->nullable()->constrained('accounts');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customers');
    }
};
