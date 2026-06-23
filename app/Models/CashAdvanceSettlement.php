<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceSettlement extends Model
{
    protected $fillable = [
        'cash_advance_id', 'settlement_date', 'amount', 'method', 'journal_entry_id',
    ];

    public $timestamps = false;
}
