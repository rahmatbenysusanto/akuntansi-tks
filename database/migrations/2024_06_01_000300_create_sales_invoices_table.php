<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SALES
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->string('invoice_no', 50);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'posted', 'paid', 'partial', 'void'])->default('draft');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 18, 4)->default(1);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['company_id', 'invoice_no']);
        });

        Schema::create('sales_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
        });

        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('reference_no', 50);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('sales_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 18, 2);
        });

        // PURCHASE
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->string('invoice_no', 50);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'posted', 'paid', 'partial', 'void'])->default('draft');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 18, 4)->default(1);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['company_id', 'invoice_no']);
        });

        Schema::create('purchase_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
        });

        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('reference_no', 50);
            $table->foreignId('journal_entry_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('purchase_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 18, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_allocations');
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_invoice_lines');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('sales_payment_allocations');
        Schema::dropIfExists('sales_payments');
        Schema::dropIfExists('sales_invoice_lines');
        Schema::dropIfExists('sales_invoices');
    }
};
