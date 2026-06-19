<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use TenantScoped;
    protected $fillable = ['company_id','currency_code','rate_date','rate_to_idr'];
    public $timestamps = false;
    protected function casts(): array { return ['rate_date' => 'date', 'rate_to_idr' => 'decimal:4']; }
}
