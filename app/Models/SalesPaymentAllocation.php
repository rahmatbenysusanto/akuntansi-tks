<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPaymentAllocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sales_payment_id', 'sales_invoice_id', 'amount',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function payment()
    {
        return $this->belongsTo(SalesPayment::class, 'sales_payment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
