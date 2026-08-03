<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->string('pic_name', 255)->nullable()->after('client_phone')->comment('Nama PIC');
            $table->string('pic_phone', 30)->nullable()->after('pic_name')->comment('No. telepon PIC');
            $table->decimal('hourly_rate', 18, 2)->default(120000)->after('overtime_end_time')->comment('Tarif per jam lembur');
            $table->decimal('total_cost', 18, 2)->nullable()->after('hourly_rate')->comment('Total biaya overtime (input manual atau free text)');
        });
    }

    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'pic_phone', 'hourly_rate', 'total_cost']);
        });
    }
};