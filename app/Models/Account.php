<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity, TenantScoped;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'parent_code',
        'level',
        'category',
        'normal_balance',
        'report_type',
        'is_header',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_header' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_code', 'code');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_code', 'code');
    }

    public function openingBalances()
    {
        return $this->hasMany(OpeningBalance::class);
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHeader($query)
    {
        return $query->where('is_header', true);
    }

    public function scopeLeaf($query)
    {
        return $query->where('is_header', false);
    }

    public function scopeByReportType($query, $type)
    {
        return $query->where('report_type', $type);
    }
}
