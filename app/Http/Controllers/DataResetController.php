<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class DataResetController extends Controller
{
    /**
     * Tabel yang akan direset (data transaksional & master selain User & COA).
     * Urutan tidak terlalu penting karena FOREIGN_KEY_CHECKS akan dimatikan sementara.
     */
    private array $tablesToReset = [
        // Jurnal & Saldo Awal
        'journal_entry_attachments',
        'journal_entry_lines',
        'journal_entries',
        'opening_balances',
        'accounting_periods',

        // Sales
        'sales_payment_allocations',
        'sales_payments',
        'sales_invoice_lines',
        'sales_invoices',

        // Purchase
        'purchase_payment_allocations',
        'purchase_payments',
        'purchase_invoice_lines',
        'purchase_invoices',

        // Fixed Assets & Depresiasi
        'asset_depreciation_schedules',
        'fixed_assets',

        // Inventory
        'stock_movements',
        'items',

        // Bank & Rekonsiliasi
        'bank_statement_lines',
        'bank_statement_imports',
        'bank_accounts',

        // Pajak
        'tax_transactions',

        // Multi-Currency
        'exchange_rates',

        // Loan / Cicilan
        'loan_installment_schedules',
        'loan_facilities',

        // Karyawan, Kasbon, Payroll, Absensi
        'payroll_lines',
        'payrolls',
        'employee_salaries',
        'cash_advance_settlements',
        'cash_advances',
        'attendances',
        'employees',

        // Customer & Vendor
        'customers',
        'vendors',
    ];

    /**
     * Tampilkan halaman konfirmasi reset data.
     */
    public function index()
    {
        // Hitung perkiraan jumlah record yang akan dihapus
        $recordCounts = [];
        $totalRecords = 0;

        foreach ($this->tablesToReset as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $recordCounts[$table] = $count;
                    $totalRecords += $count;
                }
            }
        }

        return view('data-reset.index', compact('recordCounts', 'totalRecords'));
    }

    /**
     * Proses reset data setelah validasi konfirmasi.
     */
    public function reset(Request $request)
    {
        // Validasi: user harus mengetik "reset data" persis
        $request->validate([
            'confirmation' => 'required|string|in:reset data',
        ], [
            'confirmation.required' => 'Silakan ketik "reset data" untuk konfirmasi.',
            'confirmation.in' => 'Ketik persis "reset data" (tanpa tanda kutip) untuk konfirmasi.',
        ]);

        try {
            DB::beginTransaction();

            // Catat jumlah record yang akan dihapus sebelum dihapus
            $deletedCounts = [];
            $totalDeleted = 0;

            // Matikan foreign key checks agar tidak error constraint
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($this->tablesToReset as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        DB::table($table)->delete();
                        $deletedCounts[$table] = $count;
                        $totalDeleted += $count;
                    }
                }
            }

            // Reset auto-increment untuk tabel yang sudah kosong
            foreach ($this->tablesToReset as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    if ($count === 0) {
                        try {
                            DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                        } catch (\Exception $e) {
                            // Abaikan jika tidak bisa reset auto-increment (misal tabel view)
                        }
                    }
                }
            }

            // Nyalakan kembali foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Catat di activity log
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'deleted_counts' => $deletedCounts,
                    'total_records_deleted' => $totalDeleted,
                    'ip_address' => $request->ip(),
                ])
                ->log('Reset Data: semua data transaksional berhasil dihapus (kecuali User & COA).');

            DB::commit();

            return redirect()->route('data-reset.index')
                ->with('success', "Reset data berhasil! {$totalDeleted} record dari " . count($deletedCounts) . " tabel telah dihapus. User, COA, dan data perusahaan tetap aman.");

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['error' => $e->getMessage()])
                ->log('Reset Data GAGAL.');

            return redirect()->route('data-reset.index')
                ->with('error', 'Reset data gagal: ' . $e->getMessage());
        }
    }
}