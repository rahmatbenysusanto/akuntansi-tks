<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','sku','name','unit','category','costing_method','inventory_account_id','cogs_account_id','sales_account_id','min_stock'];
}

class StockMovement extends Model
{
    protected $fillable = ['company_id','item_id','movement_date','type','qty','unit_cost','reference_type','reference_id','journal_entry_id'];
    public $timestamps = false;
}
