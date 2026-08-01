<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->string('request_no', 50);
            $table->date('request_date');
            $table->string('client_name', 255);
            $table->text('client_address')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->enum('activity_type', [
                'staging_perangkat',
                'lab_testing',
                'software_upgrade',
                'other',
            ]);
            $table->string('activity_description')->nullable()->comment('Deskripsi jenis kegiatan, terutama untuk opsi "other"');
            $table->date('overtime_date');
            $table->time('overtime_start_time')->default('18:00:00');
            $table->time('overtime_end_time')->nullable();
            $table->text('description')->nullable()->comment('Deskripsi bebas kegiatan overtime');
            $table->enum('status', ['draft', 'sent', 'signed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['company_id', 'request_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
