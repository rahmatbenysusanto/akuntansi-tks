<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->date('date');
            $table->time('clock_in')->nullable();   // Jam masuk
            $table->time('clock_out')->nullable();  // Jam pulang
            $table->enum('status', [
                'hadir', 'sakit', 'izin', 'cuti', 'dinas_luar', 'alpha'
            ])->default('hadir');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Satu karyawan hanya boleh punya 1 record absensi per hari per perusahaan
            $table->unique(['company_id', 'employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
