<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','item_id','movement_date','type','qty','unit_cost','reference_type','reference_id','journal_entry_id'];
    public $timestamps = false;
}
