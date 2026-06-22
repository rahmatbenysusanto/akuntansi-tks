<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'asset_code', 'name', 'account_id',
        'accumulated_depreciation_account_id', 'depreciation_expense_account_id',
        'acquisition_date', 'acquisition_cost', 'useful_life_months',
        'depreciation_method', 'salvage_value', 'status',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'salvage_value' => 'decimal:2',
        ];
    }

    public function account() { return $this->belongsTo(Account::class, 'account_id'); }
    public function accumulatedDepreciationAccount() { return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id'); }
    public function depreciationExpenseAccount() { return $this->belongsTo(Account::class, 'depreciation_expense_account_id'); }
    public function schedules() { return $this->hasMany(AssetDepreciationSchedule::class); }
}

class AssetDepreciationSchedule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'fixed_asset_id', 'period_no', 'schedule_date',
        'depreciation_amount', 'accumulated_amount', 'book_value',
        'is_posted', 'journal_entry_id',
    ];

    protected $table = 'asset_depreciation_schedules';

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_amount' => 'decimal:2',
            'book_value' => 'decimal:2',
            'is_posted' => 'boolean',
        ];
    }

    public function fixedAsset() { return $this->belongsTo(FixedAsset::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
}
