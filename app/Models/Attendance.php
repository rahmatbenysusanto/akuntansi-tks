<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Label status untuk tampilan UI
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'hadir'      => 'Hadir',
            'sakit'      => 'Sakit',
            'izin'       => 'Izin',
            'cuti'       => 'Cuti',
            'dinas_luar' => 'Dinas Luar',
            'alpha'      => 'Alpha',
            default      => ucfirst($this->status),
        };
    }

    /**
     * Warna badge status untuk Tailwind CSS
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            'hadir'      => 'bg-emerald-50 text-emerald-700',
            'sakit'      => 'bg-yellow-50 text-yellow-700',
            'izin'       => 'bg-blue-50 text-blue-700',
            'cuti'       => 'bg-purple-50 text-purple-700',
            'dinas_luar' => 'bg-indigo-50 text-indigo-700',
            'alpha'      => 'bg-red-50 text-red-700',
            default      => 'bg-slate-100 text-slate-600',
        };
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
