<?php

namespace App\Models;

use App\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseInvoice extends Model
{
    use TenantScoped;

    protected $fillable = [
        'company_id', 'vendor_id', 'invoice_no', 'invoice_date', 'due_date',
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
        ];
    }

    public function lines() { return $this->hasMany(PurchaseInvoiceLine::class); }
    public function vendor() { return $this->belongsTo(Vendor::class); }

    public function post(): void
    {
        DB::transaction(function () {
            $this->update(['status' => 'posted']);

            $period = AccountingPeriod::where('year', $this->invoice_date->year)
                ->where('month', $this->invoice_date->month)->first();
            if (!$period) {
                throw new \Exception('Periode akuntansi untuk ' . $this->invoice_date->format('F Y') . ' tidak ditemukan. Buat periode terlebih dahulu.');
            }

            $hutangAccount = $this->vendor->apAccount ?? Account::where('code', '2.1.01.01.00')->first();
            $expenseAccount = Account::where('category', 'hpp')->where('is_header', false)->first();
            $ppnMasukan = Account::where('name', 'LIKE', '%PPN MASUKAN%')->first();

            if (!$hutangAccount) {
                throw new \Exception('Akun Hutang Usaha tidak ditemukan. Set AP Account di data vendor atau pastikan akun dengan kode 2.1.01.01.00 ada.');
            }
            if (!$expenseAccount) {
                throw new \Exception('Akun HPP tidak ditemukan.');
            }

            $entry = JournalEntry::create([
                'company_id' => $this->company_id,
                'accounting_period_id' => $period->id,
                'entry_date' => $this->invoice_date,
                'reference_no' => $this->invoice_no,
                'description' => 'Purchase Invoice: ' . $this->vendor->name,
                'status' => 'posted',
                'created_by' => $this->created_by,
                'posted_at' => now(),
            ]);

            $lines = [
                ['account_id' => $expenseAccount->id, 'debit' => $this->subtotal, 'credit' => 0, 'line_order' => 1],
                ['account_id' => $hutangAccount->id, 'debit' => 0, 'credit' => $this->total, 'line_order' => 2],
            ];

            if ($this->tax_amount > 0 && $ppnMasukan) {
                $lines[] = ['account_id' => $ppnMasukan->id, 'debit' => $this->tax_amount, 'credit' => 0, 'line_order' => 3];
            }

            $entry->lines()->createMany($lines);
            $this->update(['journal_entry_id' => $entry->id]);
        });
    }
}

class PurchaseInvoiceLine extends Model
{
    protected $fillable = ['purchase_invoice_id', 'description', 'qty', 'unit_price', 'discount', 'tax_rate', 'line_total'];
    public $timestamps = false;
    public function invoice() { return $this->belongsTo(PurchaseInvoice::class); }
}
