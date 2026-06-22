<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TaxTransaction extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','tax_type','transaction_date','reference_type','reference_id','counterparty_name','counterparty_npwp','dpp','tax_rate','tax_amount','document_no','period_month','period_year','status','reported_at'];
}
