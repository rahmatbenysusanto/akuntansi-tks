<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class OpeningBalance extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id',
        'accounting_period_id',
        'account_id',
        'debit',
        'credit',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getBalanceAttribute(): float
    {
        return $this->debit - $this->credit;
    }
}
