<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationSchedule extends Model
{
    protected $fillable = ['fixed_asset_id','period_no','schedule_date','depreciation_amount','accumulated_amount','book_value','is_posted','journal_entry_id'];
    protected $table = 'asset_depreciation_schedules';
    public $timestamps = false;
    protected function casts(): array { return ['schedule_date' => 'date', 'is_posted' => 'boolean']; }
    public function fixedAsset() { return $this->belongsTo(FixedAsset::class); }
}
