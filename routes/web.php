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
    Route::post('journal-entries/ai-suggest', [JournalEntryController::class, 'suggest'])
        ->name('journal-entries.ai-suggest');
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
    Route::post('cash-advances/{cashAdvance}/settle', [\App\Http\Controllers\CashAdvanceController::class, 'settle'])
        ->name('cash-advances.settle');
    Route::resource('cash-advances', \App\Http\Controllers\CashAdvanceController::class)->only(['index', 'create', 'store']);

    // Employees
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);

    // Setup Gaji (Employee Salaries)
    Route::get('employee-salaries', [\App\Http\Controllers\EmployeeSalaryController::class, 'index'])->name('employee-salaries.index');
    Route::get('employee-salaries/{employee}/edit', [\App\Http\Controllers\EmployeeSalaryController::class, 'edit'])->name('employee-salaries.edit');
    Route::put('employee-salaries/{employee}', [\App\Http\Controllers\EmployeeSalaryController::class, 'update'])->name('employee-salaries.update');

    // Payroll
    Route::post('payroll/{payroll}/post', [\App\Http\Controllers\PayrollController::class, 'post'])->name('payroll.post');
    Route::resource('payroll', \App\Http\Controllers\PayrollController::class)->except(['destroy']);

    // Absensi (HR Module)
    // Catatan: clock-in & clock-out harus didefinisikan SEBELUM resource agar tidak bertabrakan dengan route {attendance}
    Route::post('attendances/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendances.clock-in');
    Route::post('attendances/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendances.clock-out');
    Route::resource('attendances', \App\Http\Controllers\AttendanceController::class)->except(['show']);

    // Tax
    Route::get('tax', [\App\Http\Controllers\TaxController::class, 'index'])->name('tax.index');
    Route::get('tax/ppn', [\App\Http\Controllers\TaxController::class, 'reportPpn'])->name('tax.ppn');
    Route::get('tax/ppn/pdf', [\App\Http\Controllers\TaxController::class, 'ppnPdf'])->name('tax.ppn.pdf');
    Route::get('tax/ppn/excel', [\App\Http\Controllers\TaxController::class, 'ppnExcel'])->name('tax.ppn.excel');

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

        Route::get('general-ledger/pdf', [ReportController::class, 'generalLedgerPdf'])->name('general-ledger.pdf');
        Route::get('general-ledger/excel', [ReportController::class, 'generalLedgerExcel'])->name('general-ledger.excel');
        Route::get('trial-balance/pdf', [ReportController::class, 'trialBalancePdf'])->name('trial-balance.pdf');
        Route::get('trial-balance/excel', [ReportController::class, 'trialBalanceExcel'])->name('trial-balance.excel');
        Route::get('financial-notes/pdf', [ReportController::class, 'financialNotesPdf'])->name('financial-notes.pdf');
        Route::get('financial-notes/excel', [ReportController::class, 'financialNotesExcel'])->name('financial-notes.excel');
        Route::get('financial-highlight/pdf', [ReportController::class, 'financialHighlightPdf'])->name('financial-highlight.pdf');
        Route::get('financial-highlight/excel', [ReportController::class, 'financialHighlightExcel'])->name('financial-highlight.excel');
    });

    // User Management (admin only)
    Route::resource('users', UserController::class)->middleware('can:admin');

    // Audit Trail
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('activity-logs.index')
        ->middleware('can:admin');
});

require __DIR__ . '/auth.php';
