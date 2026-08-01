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
    Route::middleware('menu:accounts.index')->group(function () {
        Route::resource('accounts', AccountController::class)->except(['show']);
    });

    // Master Data: Accounting Periods
    Route::middleware('menu:accounting-periods.index')->group(function () {
        Route::resource('accounting-periods', AccountingPeriodController::class)->except(['create', 'edit', 'show', 'update', 'destroy']);
        Route::patch('accounting-periods/{accountingPeriod}/close', [AccountingPeriodController::class, 'close'])
            ->name('accounting-periods.close');
    });

    // Master Data: Opening Balances
    Route::middleware('menu:opening-balances.index')->group(function () {
        Route::resource('opening-balances', OpeningBalanceController::class)->only(['index', 'store']);
    });

    // Master Data: Customer & Vendor
    Route::middleware('menu:customers.index')->group(function () {
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    });
    Route::middleware('menu:vendors.index')->group(function () {
        Route::resource('vendors', \App\Http\Controllers\VendorController::class);
    });

    // Transaksi: Jurnal Umum
    Route::middleware('menu:journal-entries.index')->group(function () {
        Route::post('journal-entries/ai-suggest', [JournalEntryController::class, 'suggest'])
            ->name('journal-entries.ai-suggest');
        Route::resource('journal-entries', JournalEntryController::class)->except(['show']);
        Route::patch('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])
            ->name('journal-entries.post');
    });

    // Transaksi: Sales & Purchase
    Route::middleware('menu:sales.index')->group(function () {
        Route::resource('sales', \App\Http\Controllers\SalesInvoiceController::class)->only(['index', 'create', 'store']);
    });
    Route::middleware('menu:purchases.index')->group(function () {
        Route::resource('purchases', \App\Http\Controllers\PurchaseInvoiceController::class)->only(['index', 'create', 'store']);
    });

    // AR/AP Ledger
    Route::middleware('menu:arap.kartu-piutang')->prefix('arap')->name('arap.')->group(function () {
        Route::get('kartu-piutang', [\App\Http\Controllers\ARAPController::class, 'kartuPiutang'])->name('kartu-piutang');
        Route::get('kartu-hutang', [\App\Http\Controllers\ARAPController::class, 'kartuHutang'])->name('kartu-hutang');
        Route::get('aging-piutang', [\App\Http\Controllers\ARAPController::class, 'agingPiutang'])->name('aging-piutang');
        Route::get('aging-hutang', [\App\Http\Controllers\ARAPController::class, 'agingHutang'])->name('aging-hutang');
    });

    // Aset Tetap
    Route::middleware('menu:fixed-assets.index')->group(function () {
        Route::resource('fixed-assets', \App\Http\Controllers\FixedAssetController::class)->except(['edit', 'update', 'destroy']);
        Route::post('fixed-assets/post-depreciation', [\App\Http\Controllers\FixedAssetController::class, 'postDepreciation'])->name('fixed-assets.post-depreciation');
    });

    // Inventory
    Route::middleware('menu:items.index')->group(function () {
        Route::resource('items', \App\Http\Controllers\ItemController::class)->except(['show', 'destroy']);
    });

    // Multi-Currency
    Route::middleware('menu:exchange-rates.index')->group(function () {
        Route::resource('exchange-rates', \App\Http\Controllers\ExchangeRateController::class)->only(['index', 'store']);
    });

    // Loans
    Route::middleware('menu:loans.index')->group(function () {
        Route::resource('loans', \App\Http\Controllers\LoanController::class)->except(['edit', 'update', 'destroy']);
        Route::post('loans/{loan}/pay-installment', [\App\Http\Controllers\LoanController::class, 'payInstallment'])->name('loans.pay-installment');
    });

    // Overtime Requests (WH)
    Route::middleware('menu:overtime-requests.index')->group(function () {
        Route::get('overtime-requests/{overtimeRequest}/pdf', [\App\Http\Controllers\OvertimeRequestController::class, 'pdf'])
            ->name('overtime-requests.pdf');
        Route::patch('overtime-requests/{overtimeRequest}/send', [\App\Http\Controllers\OvertimeRequestController::class, 'send'])
            ->name('overtime-requests.send');
        Route::patch('overtime-requests/{overtimeRequest}/sign', [\App\Http\Controllers\OvertimeRequestController::class, 'sign'])
            ->name('overtime-requests.sign');
        Route::resource('overtime-requests', \App\Http\Controllers\OvertimeRequestController::class);
    });

    // Cash Advances
    Route::middleware('menu:cash-advances.index')->group(function () {
        Route::post('cash-advances/{cashAdvance}/settle', [\App\Http\Controllers\CashAdvanceController::class, 'settle'])
            ->name('cash-advances.settle');
        Route::resource('cash-advances', \App\Http\Controllers\CashAdvanceController::class)->only(['index', 'create', 'store']);
    });

    // Employees
    Route::middleware('menu:employees.index')->group(function () {
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);
    });

    // Setup Gaji (Employee Salaries)
    Route::middleware('menu:employee-salaries.index')->group(function () {
        Route::get('employee-salaries', [\App\Http\Controllers\EmployeeSalaryController::class, 'index'])->name('employee-salaries.index');
        Route::get('employee-salaries/{employee}/edit', [\App\Http\Controllers\EmployeeSalaryController::class, 'edit'])->name('employee-salaries.edit');
        Route::put('employee-salaries/{employee}', [\App\Http\Controllers\EmployeeSalaryController::class, 'update'])->name('employee-salaries.update');
    });

    // Payroll
    Route::middleware('menu:payroll.index')->group(function () {
        Route::post('payroll/{payroll}/post', [\App\Http\Controllers\PayrollController::class, 'post'])->name('payroll.post');
        Route::resource('payroll', \App\Http\Controllers\PayrollController::class)->except(['destroy']);
    });

    // Absensi (HR Module)
    Route::middleware('menu:attendances.index')->group(function () {
        Route::post('attendances/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendances.clock-in');
        Route::post('attendances/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendances.clock-out');
        Route::resource('attendances', \App\Http\Controllers\AttendanceController::class)->except(['show']);
    });

    // Tax
    Route::middleware('menu:tax.index')->group(function () {
        Route::get('tax', [\App\Http\Controllers\TaxController::class, 'index'])->name('tax.index');
    });
    Route::middleware('menu:tax.ppn')->group(function () {
        Route::get('tax/ppn', [\App\Http\Controllers\TaxController::class, 'reportPpn'])->name('tax.ppn');
        Route::get('tax/ppn/pdf', [\App\Http\Controllers\TaxController::class, 'ppnPdf'])->name('tax.ppn.pdf');
        Route::get('tax/ppn/excel', [\App\Http\Controllers\TaxController::class, 'ppnExcel'])->name('tax.ppn.excel');
    });

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::middleware('menu:reports.general-ledger')->group(function () {
            Route::get('general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
            Route::get('general-ledger/pdf', [ReportController::class, 'generalLedgerPdf'])->name('general-ledger.pdf');
            Route::get('general-ledger/excel', [ReportController::class, 'generalLedgerExcel'])->name('general-ledger.excel');
        });
        Route::middleware('menu:reports.trial-balance')->group(function () {
            Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
            Route::get('trial-balance/pdf', [ReportController::class, 'trialBalancePdf'])->name('trial-balance.pdf');
            Route::get('trial-balance/excel', [ReportController::class, 'trialBalanceExcel'])->name('trial-balance.excel');
        });
        Route::middleware('menu:reports.income-statement')->group(function () {
            Route::get('income-statement', [ReportController::class, 'incomeStatement'])->name('income-statement');
            Route::get('income-statement/pdf', [ReportController::class, 'incomeStatementPdf'])->name('income-statement.pdf');
            Route::get('income-statement/excel', [ReportController::class, 'incomeStatementExcel'])->name('income-statement.excel');
        });
        Route::middleware('menu:reports.balance-sheet')->group(function () {
            Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('balance-sheet/pdf', [ReportController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');
            Route::get('balance-sheet/excel', [ReportController::class, 'balanceSheetExcel'])->name('balance-sheet.excel');
        });
        Route::middleware('menu:reports.financial-highlight')->group(function () {
            Route::get('financial-highlight', [ReportController::class, 'financialHighlight'])->name('financial-highlight');
            Route::get('financial-highlight/pdf', [ReportController::class, 'financialHighlightPdf'])->name('financial-highlight.pdf');
            Route::get('financial-highlight/excel', [ReportController::class, 'financialHighlightExcel'])->name('financial-highlight.excel');
        });
        Route::get('financial-notes', [ReportController::class, 'financialNotes'])->name('financial-notes');
        Route::get('financial-notes/pdf', [ReportController::class, 'financialNotesPdf'])->name('financial-notes.pdf');
        Route::get('financial-notes/excel', [ReportController::class, 'financialNotesExcel'])->name('financial-notes.excel');
    });

    // User Management (admin only)
    Route::resource('users', UserController::class)->middleware('can:admin');

    // Audit Trail
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('activity-logs.index')
        ->middleware('can:admin');

    // Reset Data (admin only)
    Route::prefix('data-reset')->name('data-reset.')->middleware('can:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\DataResetController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DataResetController::class, 'reset'])->name('reset');
    });
});

require __DIR__ . '/auth.php';
