<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'customer_id', 'invoice_no', 'invoice_date', 'due_date',
        'status', 'subtotal', 'tax_amount', 'total', 'currency', 'exchange_rate',
        'journal_entry_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
        ];
    }

    public function lines() { return $this->hasMany(SalesInvoiceLine::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }

    public function post(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'posted']);

            // Auto-generate journal entry
            $period = AccountingPeriod::where('year', $this->invoice_date->year)
                ->where('month', $this->invoice_date->month)->first();

            if (!$period) return;

            $piutangAccount = $this->customer->arAccount ?? Account::where('code', '1.1.04.01.01')->first();
            $revenueAccount = Account::where('category', 'pendapatan')->where('is_header', false)->first();
            $taxAccount = Account::where('name', 'LIKE', '%PPN KELUARAN%')->first();

            if (!$piutangAccount || !$revenueAccount) return;

            $entry = JournalEntry::create([
                'company_id' => $this->company_id,
                'accounting_period_id' => $period->id,
                'entry_date' => $this->invoice_date,
                'reference_no' => $this->invoice_no,
                'description' => 'Sales Invoice: ' . $this->customer->name,
                'status' => 'posted',
                'created_by' => $this->created_by,
                'posted_at' => now(),
            ]);

            $lines = [
                ['account_id' => $piutangAccount->id, 'debit' => $this->total, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $revenueAccount->id, 'debit' => 0, 'credit' => $this->subtotal, 'line_order' => 2],
            ];

            if ($this->tax_amount > 0 && $taxAccount) {
                $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $this->tax_amount, 'line_order' => 3];
            }

            $entry->lines()->createMany($lines);
            $entry->lines()->update(['company_id' => $this->company_id]);

            $this->update(['journal_entry_id' => $entry->id]);
        });
    }
}

class SalesInvoiceLine extends Model
{
    protected $fillable = ['sales_invoice_id', 'description', 'qty', 'unit_price', 'discount', 'tax_rate', 'line_total'];
    public $timestamps = false;
    public function invoice() { return $this->belongsTo(SalesInvoice::class); }
}
