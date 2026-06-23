<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setup gaji pokok per karyawan
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->decimal('base_salary', 18, 2)->default(0);
            $table->decimal('allowance_transport', 18, 2)->default(0);
            $table->decimal('allowance_meal', 18, 2)->default(0);
            $table->decimal('allowance_other', 18, 2)->default(0);
            $table->decimal('bpjs_kesehatan_pct', 5, 2)->default(1.00); // % potongan karyawan
            $table->decimal('bpjs_tk_pct', 5, 2)->default(2.00);        // % potongan karyawan
            $table->timestamps();
            $table->unique(['company_id', 'employee_id']);
        });

        // Header slip gaji per periode
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('accounting_period_id')->constrained();
            $table->string('reference_no', 50);
            $table->string('description')->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
            // Akun-akun untuk jurnal (dipilih saat buat payroll)
            $table->foreignId('salary_expense_account_id')->nullable()->constrained('accounts'); // Beban Gaji
            $table->foreignId('salary_payable_account_id')->nullable()->constrained('accounts'); // Hutang Gaji
            $table->foreignId('bpjs_payable_account_id')->nullable()->constrained('accounts');   // Hutang BPJS
            $table->foreignId('pph21_payable_account_id')->nullable()->constrained('accounts');  // Hutang PPh 21
            $table->timestamps();
            $table->unique(['company_id', 'accounting_period_id']);
        });

        // Detail per karyawan per payroll
        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained();
            // Pendapatan
            $table->decimal('base_salary', 18, 2)->default(0);
            $table->decimal('allowance_transport', 18, 2)->default(0);
            $table->decimal('allowance_meal', 18, 2)->default(0);
            $table->decimal('allowance_other', 18, 2)->default(0);
            $table->decimal('overtime', 18, 2)->default(0);      // Lembur (input manual)
            $table->decimal('gross_salary', 18, 2)->default(0);  // = base + allowances + overtime
            // Potongan
            $table->decimal('bpjs_kesehatan', 18, 2)->default(0);
            $table->decimal('bpjs_tk', 18, 2)->default(0);
            $table->decimal('pph21', 18, 2)->default(0);
            $table->decimal('kasbon_deduction', 18, 2)->default(0);
            $table->foreignId('cash_advance_id')->nullable()->constrained('cash_advances');
            $table->decimal('total_deduction', 18, 2)->default(0); // = bpjs + pph + kasbon
            $table->decimal('net_salary', 18, 2)->default(0);      // = gross - total_deduction
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_lines');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('employee_salaries');
    }
};
