<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanInstallmentSchedule extends Model
{
    protected $fillable = ['loan_facility_id','installment_no','due_date','principal_amount','interest_amount','total_amount','status','paid_date','journal_entry_id'];
    public $timestamps = false;
    protected function casts(): array { return ['due_date' => 'date', 'paid_date' => 'date']; }
    public function fixedAsset() { return $this->belongsTo(LoanFacility::class); }
}
