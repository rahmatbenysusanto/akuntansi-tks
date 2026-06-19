<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\AccountingPeriod;

class CashFlowService
{
    public function generate(int $periodId): array
    {
        $period = AccountingPeriod::find($periodId);
        $kasAccounts = Account::where('code', 'LIKE', '1.1.01.%')->where('is_header', false)->pluck('id');

        $incomeService = app(IncomeStatementService::class);
        $income = $incomeService->generate($periodId);

        // Operating: net income + depreciation - change in receivables, inventory, payables
        $netIncome = $income['net_income'];

        // Investing: fixed asset purchases/sales
        $investing = 0;

        // Financing: loans, equity, dividends
        $financing = 0;

        $openingCash = 0;
        $closingCash = 0;

        return [
            'period' => $period,
            'operating' => ['net_income' => $netIncome],
            'investing' => $investing,
            'financing' => $financing,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
        ];
    }
}
