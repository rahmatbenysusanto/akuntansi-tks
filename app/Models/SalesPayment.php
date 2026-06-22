<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class SalesPayment extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'customer_id', 'payment_date', 'amount',
        'payment_method', 'reference_no', 'journal_entry_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations()
    {
        return $this->hasMany(SalesPaymentAllocation::class);
    }
}
