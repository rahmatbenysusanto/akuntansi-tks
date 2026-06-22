<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['inventory_account_id']);
            $table->dropForeign(['cogs_account_id']);
            $table->dropForeign(['sales_account_id']);

            // Make columns nullable (use unsignedBigInteger, not foreignId)
            $table->unsignedBigInteger('inventory_account_id')->nullable()->change();
            $table->unsignedBigInteger('cogs_account_id')->nullable()->change();
            $table->unsignedBigInteger('sales_account_id')->nullable()->change();

            // Re-add foreign key constraints (now nullable)
            $table->foreign('inventory_account_id')->references('id')->on('accounts');
            $table->foreign('cogs_account_id')->references('id')->on('accounts');
            $table->foreign('sales_account_id')->references('id')->on('accounts');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['inventory_account_id']);
            $table->dropForeign(['cogs_account_id']);
            $table->dropForeign(['sales_account_id']);

            $table->foreignId('inventory_account_id')->nullable(false)->change();
            $table->foreignId('cogs_account_id')->nullable(false)->change();
            $table->foreignId('sales_account_id')->nullable(false)->change();

            $table->foreign('inventory_account_id')->references('id')->on('accounts');
            $table->foreign('cogs_account_id')->references('id')->on('accounts');
            $table->foreign('sales_account_id')->references('id')->on('accounts');
        });
    }
};
