<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePaymentAllocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_payment_id', 'purchase_invoice_id', 'amount',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function payment()
    {
        return $this->belongsTo(PurchasePayment::class, 'purchase_payment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
