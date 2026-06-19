<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','employee_id','advance_no','advance_date','amount','reason','account_id','settlement_method','status','journal_entry_id'];
    protected function casts(): array { return ['advance_date' => 'date', 'amount' => 'decimal:2']; }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function settlements() { return $this->hasMany(CashAdvanceSettlement::class); }
}

class CashAdvanceSettlement extends Model
{
    protected $fillable = ['cash_advance_id','settlement_date','amount','method','journal_entry_id'];
    public $timestamps = false;
}
