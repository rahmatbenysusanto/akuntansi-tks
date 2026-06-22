<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountingPeriodController;
use App\Http\Controllers\OpeningBalanceController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Master Data: Chart of Account
    Route::resource('accounts', AccountController::class)->except(['show']);

    // Master Data: Accounting Periods
    Route::resource('accounting-periods', AccountingPeriodController::class)->except(['create', 'edit', 'show', 'update', 'destroy']);
    Route::patch('accounting-periods/{accountingPeriod}/close', [AccountingPeriodController::class, 'close'])
        ->name('accounting-periods.close');

    // Master Data: Opening Balances
    Route::resource('opening-balances', OpeningBalanceController::class)->only(['index', 'store']);

    // Master Data: Customer & Vendor
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('vendors', \App\Http\Controllers\VendorController::class);

    // Transaksi: Jurnal Umum
    Route::resource('journal-entries', JournalEntryController::class)->except(['show']);

    // Transaksi: Sales & Purchase
    Route::resource('sales', \App\Http\Controllers\SalesInvoiceController::class)->only(['index', 'create', 'store']);
    Route::resource('purchases', \App\Http\Controllers\PurchaseInvoiceController::class)->only(['index', 'create', 'store']);

    // AR/AP Ledger
    Route::prefix('arap')->name('arap.')->group(function () {
        Route::get('kartu-piutang', [\App\Http\Controllers\ARAPController::class, 'kartuPiutang'])->name('kartu-piutang');
        Route::get('kartu-hutang', [\App\Http\Controllers\ARAPController::class, 'kartuHutang'])->name('kartu-hutang');
        Route::get('aging-piutang', [\App\Http\Controllers\ARAPController::class, 'agingPiutang'])->name('aging-piutang');
        Route::get('aging-hutang', [\App\Http\Controllers\ARAPController::class, 'agingHutang'])->name('aging-hutang');
    });

    // Aset Tetap
    Route::resource('fixed-assets', \App\Http\Controllers\FixedAssetController::class)->except(['edit', 'update', 'destroy']);
    Route::post('fixed-assets/post-depreciation', [\App\Http\Controllers\FixedAssetController::class, 'postDepreciation'])->name('fixed-assets.post-depreciation');

    // Inventory
    Route::resource('items', \App\Http\Controllers\ItemController::class)->except(['show', 'destroy']);

    // Multi-Currency
    Route::resource('exchange-rates', \App\Http\Controllers\ExchangeRateController::class)->only(['index', 'store']);

    // Loans
    Route::resource('loans', \App\Http\Controllers\LoanController::class)->except(['edit', 'update', 'destroy']);
    Route::post('loans/{loan}/pay-installment', [\App\Http\Controllers\LoanController::class, 'payInstallment'])->name('loans.pay-installment');

    // Cash Advances
    Route::resource('cash-advances', \App\Http\Controllers\CashAdvanceController::class)->only(['index', 'create', 'store']);

    // Employees
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);

    // Tax
    Route::get('tax', [\App\Http\Controllers\TaxController::class, 'index'])->name('tax.index');
    Route::get('tax/ppn', [\App\Http\Controllers\TaxController::class, 'reportPpn'])->name('tax.ppn');

    Route::patch('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])
        ->name('journal-entries.post');

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('financial-notes', [ReportController::class, 'financialNotes'])->name('financial-notes');
        Route::get('financial-highlight', [ReportController::class, 'financialHighlight'])->name('financial-highlight');

        // Export
        Route::get('income-statement/pdf', [ReportController::class, 'incomeStatementPdf'])->name('income-statement.pdf');
        Route::get('balance-sheet/pdf', [ReportController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');
        Route::get('income-statement/excel', [ReportController::class, 'incomeStatementExcel'])->name('income-statement.excel');
        Route::get('balance-sheet/excel', [ReportController::class, 'balanceSheetExcel'])->name('balance-sheet.excel');
    });

    // User Management (admin only)
    Route::resource('users', UserController::class)->middleware('can:admin');

    // Audit Trail
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('activity-logs.index')
        ->middleware('can:admin');
});

require __DIR__ . '/auth.php';
