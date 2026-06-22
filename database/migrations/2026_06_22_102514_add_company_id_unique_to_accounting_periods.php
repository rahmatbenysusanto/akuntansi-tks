<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_periods', function (Blueprint $table) {
            // Drop old unique constraint that didn't include company_id
            $table->dropUnique(['month', 'year']);

            // Add new unique constraint with company_id for multi-tenant
            $table->unique(['company_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('accounting_periods', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'month', 'year']);
            $table->unique(['month', 'year']);
        });
    }
};
