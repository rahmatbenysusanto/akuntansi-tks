<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class LoanFacility extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','name','type','liability_account_id','interest_expense_account_id','principal_amount','interest_rate_per_year','tenor_months','start_date','installment_amount','calculation_method','counterparty','status'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'principal_amount' => 'decimal:2', 'interest_rate_per_year' => 'decimal:2'];
    }

    public function schedules() { return $this->hasMany(LoanInstallmentSchedule::class); }
}

class LoanInstallmentSchedule extends Model
{
    protected $fillable = ['loan_facility_id','installment_no','due_date','principal_amount','interest_amount','total_amount','status','paid_date','journal_entry_id'];
    public $timestamps = false;

    protected function casts(): array
    {
        return ['due_date' => 'date', 'paid_date' => 'date'];
    }
}
