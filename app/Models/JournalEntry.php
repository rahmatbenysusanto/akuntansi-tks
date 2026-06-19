<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JournalEntry extends Model
{
    use LogsActivity;

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);

        static::creating(function ($entry) {
            if (empty($entry->company_id)) {
                if (auth()->check() && auth()->user()->current_company_id) {
                    $entry->company_id = auth()->user()->current_company_id;
                }
            }
        });

        static::created(function ($entry) {
            // Propagate company_id ke lines
            if ($entry->company_id) {
                $entry->lines()->update(['company_id' => $entry->company_id]);
            }
        });

        static::saving(function ($entry) {
            if (auth()->check()) {
                $entry->updated_by = auth()->id();
            }
        });
    }

    protected $fillable = [
        'company_id',
        'accounting_period_id',
        'entry_date',
        'reference_no',
        'description',
        'status',
        'created_by',
        'updated_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function hasBalance(): bool
    {
        $totalDebit = $this->lines()->sum('debit');
        $totalCredit = $this->lines()->sum('credit');
        return abs($totalDebit - $totalCredit) < 0.01;
    }

    public function post(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Only draft entries can be posted.');
        }

        if (!$this->hasBalance()) {
            throw new \Exception('Journal entry is not balanced. Debit must equal Credit.');
        }

        DB::transaction(function () {
            $this->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);
        });
    }
}
