<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Section 22: Fixed Assets
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('asset_code', 50);
            $table->string('name', 255);
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('accumulated_depreciation_account_id')->constrained('accounts');
            $table->foreignId('depreciation_expense_account_id')->constrained('accounts');
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 18, 2);
            $table->integer('useful_life_months');
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');
            $table->decimal('salvage_value', 18, 2)->default(0);
            $table->enum('status', ['active', 'disposed'])->default('active');
            $table->timestamps();
        });

        Schema::create('asset_depreciation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->integer('period_no');
            $table->date('schedule_date');
            $table->decimal('depreciation_amount', 18, 2);
            $table->decimal('accumulated_amount', 18, 2);
            $table->decimal('book_value', 18, 2);
            $table->boolean('is_posted')->default(false);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->unique(['fixed_asset_id', 'period_no']);
        });

        // Section 23: Inventory
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('sku', 50);
            $table->string('name', 255);
            $table->string('unit', 20)->default('pcs');
            $table->string('category', 100)->nullable();
            $table->enum('costing_method', ['fifo', 'average'])->default('average');
            $table->foreignId('inventory_account_id')->constrained('accounts');
            $table->foreignId('cogs_account_id')->constrained('accounts');
            $table->foreignId('sales_account_id')->constrained('accounts');
            $table->integer('min_stock')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'sku']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('item_id')->constrained();
            $table->date('movement_date');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('qty', 12, 2);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->timestamps();
        });

        // Section 24: Bank
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('account_id')->constrained('accounts');
            $table->string('bank_name', 255);
            $table->string('account_number', 50);
            $table->string('account_holder_name', 255);
            $table->timestamps();
        });

        Schema::create('bank_statement_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('opening_balance', 18, 2);
            $table->decimal('closing_balance', 18, 2);
            $table->timestamp('imported_at')->useCurrent();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_import_id')->constrained()->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('description', 255);
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->boolean('is_reconciled')->default(false);
            $table->foreignId('matched_journal_entry_line_id')->nullable()->constrained('journal_entry_lines');
        });

        // Section 25 & 33: Tax
        Schema::create('tax_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->enum('tax_type', ['ppn_keluaran','ppn_masukan','pph21','pph23','pph25','pph29','pph4a2','pph15','pph26']);
            $table->date('transaction_date');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('counterparty_name', 255)->nullable();
            $table->string('counterparty_npwp', 30)->nullable();
            $table->decimal('dpp', 18, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->string('document_no', 100)->nullable();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->enum('status', ['belum_lapor','sudah_lapor'])->default('belum_lapor');
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });

        // Section 27: Multi-Currency
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('currency_code', 3);
            $table->date('rate_date');
            $table->decimal('rate_to_idr', 18, 4);
            $table->unique(['company_id', 'currency_code', 'rate_date']);
        });

        // Section 31: Loan/Cicilan
        Schema::create('loan_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('name', 255);
            $table->enum('type', ['bank_loan','leasing','kpr','kredit_investasi']);
            $table->foreignId('liability_account_id')->constrained('accounts');
            $table->foreignId('interest_expense_account_id')->constrained('accounts');
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('interest_rate_per_year', 5, 2);
            $table->integer('tenor_months');
            $table->date('start_date');
            $table->decimal('installment_amount', 18, 2)->nullable();
            $table->enum('calculation_method', ['flat','anuitas','efektif'])->default('flat');
            $table->string('counterparty', 255)->nullable();
            $table->enum('status', ['active','paid_off'])->default('active');
            $table->timestamps();
        });

        Schema::create('loan_installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_facility_id')->constrained()->onDelete('cascade');
            $table->integer('installment_no');
            $table->date('due_date');
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('interest_amount', 18, 2);
            $table->decimal('total_amount', 18, 2);
            $table->enum('status', ['unpaid','paid','overdue'])->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->unique(['loan_facility_id', 'installment_no'], 'loan_installment_unique');
        });

        // Section 32: Kasbon
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('employee_no', 30);
            $table->string('name', 255);
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'employee_no']);
        });

        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->string('advance_no', 50);
            $table->date('advance_date');
            $table->decimal('amount', 18, 2);
            $table->text('reason')->nullable();
            $table->foreignId('account_id')->constrained('accounts');
            $table->enum('settlement_method', ['potong_gaji','kembali_tunai','campuran'])->default('kembali_tunai');
            $table->enum('status', ['outstanding','partial','settled'])->default('outstanding');
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('cash_advance_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained()->onDelete('cascade');
            $table->date('settlement_date');
            $table->decimal('amount', 18, 2);
            $table->enum('method', ['potong_gaji','kembali_tunai']);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_settlements');
        Schema::dropIfExists('cash_advances');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('loan_installment_schedules');
        Schema::dropIfExists('loan_facilities');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('tax_transactions');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statement_imports');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('items');
        Schema::dropIfExists('asset_depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
    }
};
