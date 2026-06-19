<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'code', 'name', 'address', 'phone', 'npwp',
        'payment_term_days', 'ap_account_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'payment_term_days' => 'integer',
        ];
    }

    public function apAccount()
    {
        return $this->belongsTo(Account::class, 'ap_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
