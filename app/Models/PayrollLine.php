<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLine extends Model
{
    protected $fillable = [
        'payroll_id', 'employee_id',
        'base_salary', 'allowance_transport', 'allowance_meal', 'allowance_other', 'overtime',
        'gross_salary',
        'bpjs_kesehatan', 'bpjs_tk', 'pph21', 'kasbon_deduction', 'cash_advance_id',
        'total_deduction', 'net_salary',
    ];

    protected function casts(): array
    {
        return [
            'base_salary'         => 'decimal:2',
            'allowance_transport' => 'decimal:2',
            'allowance_meal'      => 'decimal:2',
            'allowance_other'     => 'decimal:2',
            'overtime'            => 'decimal:2',
            'gross_salary'        => 'decimal:2',
            'bpjs_kesehatan'      => 'decimal:2',
            'bpjs_tk'             => 'decimal:2',
            'pph21'               => 'decimal:2',
            'kasbon_deduction'    => 'decimal:2',
            'total_deduction'     => 'decimal:2',
            'net_salary'          => 'decimal:2',
        ];
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
