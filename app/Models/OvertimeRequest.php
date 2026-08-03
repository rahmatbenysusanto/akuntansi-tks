<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id',
        'request_no',
        'request_date',
        'client_name',
        'client_address',
        'client_phone',
        'pic_name',
        'pic_phone',
        'activity_type',
        'activity_description',
        'overtime_date',
        'overtime_start_time',
        'overtime_end_time',
        'hourly_rate',
        'total_cost',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'overtime_date' => 'date',
            'hourly_rate' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Label untuk activity_type dalam Bahasa Indonesia.
     */
    public function getActivityTypeLabelAttribute(): string
    {
        return match ($this->activity_type) {
            'staging_perangkat' => 'Staging Perangkat',
            'lab_testing'        => 'Lab Testing',
            'software_upgrade'   => 'Software Upgrade',
            'other'              => 'Other' . ($this->activity_description ? ' — ' . $this->activity_description : ''),
            default              => $this->activity_type,
        };
    }

    /**
     * Hitung total durasi overtime (dalam jam, sebagai float).
     */
    public function getDurationHoursAttribute(): float
    {
        if (!$this->overtime_start_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->overtime_start_time);

        // Jika end_time kosong, asumsikan belum selesai — tetap hitung berdasarkan end_time jika ada
        if ($this->overtime_end_time) {
            $end = \Carbon\Carbon::parse($this->overtime_end_time);

            // Jika end_time lebih kecil dari start_time, anggap melewati tengah malam
            if ($end->lt($start)) {
                $end->addDay();
            }
        } else {
            // default ke start + 0 jam jika end kosong
            return 0;
        }

        return round($start->floatDiffInHours($end), 2);
    }

    /**
     * Label durasi dalam format jam:menit.
     */
    public function getDurationLabelAttribute(): string
    {
        $hours = $this->duration_hours;
        if ($hours <= 0) {
            return '-';
        }
        $h = floor($hours);
        $m = round(($hours - $h) * 60);
        if ($m == 60) { $h++; $m = 0; }
        return "{$h} jam" . ($m > 0 ? " {$m} menit" : '');
    }

    /**
     * Status badge label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'Draft',
            'sent'   => 'Terkirim',
            'signed' => 'Tertanda Tangan',
            default  => $this->status,
        };
    }

    /**
     * Total biaya otomatis berdasarkan durasi × tarif per jam.
     * Hanya sebagai referensi — actual total_cost bisa diinput manual.
     */
    public function getCalculatedCostAttribute(): float
    {
        return round($this->duration_hours * ($this->hourly_rate ?? 0), 2);
    }

    /**
     * Format Rupiah untuk tarif per jam.
     */
    public function getHourlyRateFormattedAttribute(): string
    {
        return formatRupiah($this->hourly_rate ?? 0);
    }

    /**
     * Format Rupiah untuk total biaya.
     */
    public function getTotalCostFormattedAttribute(): string
    {
        return formatRupiah($this->total_cost ?? 0);
    }
}
