<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'code', 'name', 'address', 'phone', 'npwp',
        'payment_term_days', 'credit_limit', 'ar_account_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
            'payment_term_days' => 'integer',
        ];
    }

    public function arAccount()
    {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
