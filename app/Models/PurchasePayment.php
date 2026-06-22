<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'vendor_id', 'payment_date', 'amount',
        'payment_method', 'reference_no', 'journal_entry_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function allocations()
    {
        return $this->hasMany(PurchasePaymentAllocation::class);
    }
}
