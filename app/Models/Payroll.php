<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'accounting_period_id', 'reference_no', 'description',
        'status', 'created_by', 'posted_at', 'journal_entry_id',
        'salary_expense_account_id', 'salary_payable_account_id',
        'bpjs_payable_account_id', 'pph21_payable_account_id',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function lines()
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaryExpenseAccount()
    {
        return $this->belongsTo(Account::class, 'salary_expense_account_id');
    }

    public function salaryPayableAccount()
    {
        return $this->belongsTo(Account::class, 'salary_payable_account_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function totalGross(): float
    {
        return (float) $this->lines()->sum('gross_salary');
    }

    public function totalNet(): float
    {
        return (float) $this->lines()->sum('net_salary');
    }

    public function totalDeduction(): float
    {
        return (float) $this->lines()->sum('total_deduction');
    }
}
