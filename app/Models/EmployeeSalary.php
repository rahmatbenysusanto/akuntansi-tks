<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'employee_id',
        'base_salary', 'allowance_transport', 'allowance_meal', 'allowance_other',
        'bpjs_kesehatan_pct', 'bpjs_tk_pct',
    ];

    protected function casts(): array
    {
        return [
            'base_salary'          => 'decimal:2',
            'allowance_transport'  => 'decimal:2',
            'allowance_meal'       => 'decimal:2',
            'allowance_other'      => 'decimal:2',
            'bpjs_kesehatan_pct'   => 'decimal:2',
            'bpjs_tk_pct'          => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Total tunjangan tetap */
    public function totalAllowance(): float
    {
        return (float) $this->allowance_transport
            + (float) $this->allowance_meal
            + (float) $this->allowance_other;
    }
}
