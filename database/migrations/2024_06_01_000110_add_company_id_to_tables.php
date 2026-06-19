<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'accounts', 'accounting_periods', 'opening_balances',
        'journal_entries', 'journal_entry_lines',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                // Skip if column already exists
                if (!Schema::hasColumn($table, 'company_id')) {
                    $t->foreignId('company_id')->nullable()->constrained('companies')->after('id');
                    $t->index('company_id');
                }
            });
        }

        // Add company_user pivot and current_company_id to users
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role', 50)->default('staff');
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        if (!Schema::hasColumn('users', 'current_company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('current_company_id')->nullable()->constrained('companies')->after('role');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'company_id')) {
                    $t->dropForeign([$table => 'company_id']);
                    $t->dropColumn('company_id');
                }
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_company_id')) {
                $table->dropForeign(['current_company_id']);
                $table->dropColumn('current_company_id');
            }
        });
    }
};
