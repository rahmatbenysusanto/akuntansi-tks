<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\BalanceSheetService;
use App\Services\IncomeStatementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AccountingPeriod $period;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Test Company', 'default_currency' => 'IDR']);

        $accSeeder = app()->make(\Database\Seeders\AccountSeeder::class);
        $accSeeder->companyId = $this->company->id;
        $accSeeder->run();

        $periodSeeder = app()->make(\Database\Seeders\AccountingPeriodSeeder::class);
        $periodSeeder->companyId = $this->company->id;
        $periodSeeder->run();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'current_company_id' => $this->company->id,
        ]);

        $this->period = AccountingPeriod::where('status', 'open')->first();
    }

    /** @test */
    public function jurnal_dengan_debit_tidak_sama_kredit_harus_gagal()
    {
        $this->actingAs($this->admin);

        $akun1 = Account::where('is_header', false)->first();
        $akun2 = Account::where('is_header', false)->where('id', '!=', $akun1->id)->first();

        $response = $this->post(route('journal-entries.store'), [
            'accounting_period_id' => $this->period->id,
            'entry_date' => now()->format('Y-m-d'),
            'reference_no' => 'TEST-001',
            'description' => 'Test - not balanced',
            'status' => 'posted',
            'lines' => [
                ['account_id' => $akun1->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $akun2->id, 'debit' => 0, 'credit' => 50000],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('journal_entries', ['reference_no' => 'TEST-001']);
    }

    /** @test */
    public function jurnal_dengan_akun_header_harus_gagal()
    {
        $this->actingAs($this->admin);

        $headerAccount = Account::where('is_header', true)->first();
        $akun2 = Account::where('is_header', false)->where('id', '!=', $headerAccount?->id)->first();

        if (!$headerAccount || !$akun2) {
            $this->markTestSkipped('Butuh minimal 1 akun header dan 1 akun non-header');
        }

        $response = $this->post(route('journal-entries.store'), [
            'accounting_period_id' => $this->period->id,
            'entry_date' => now()->format('Y-m-d'),
            'reference_no' => 'TEST-002',
            'description' => 'Test - header account',
            'status' => 'draft',
            'lines' => [
                ['account_id' => $headerAccount->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $akun2->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $response->assertSessionHas('error');
    }

    /** @test */
    public function jurnal_di_periode_closed_harus_ditolak()
    {
        $this->actingAs($this->admin);

        $periodToClose = AccountingPeriod::where('status', 'open')->first();
        if ($periodToClose) {
            $periodToClose->update(['status' => 'closed', 'closed_at' => now(), 'closed_by' => $this->admin->id]);
        }

        $closedPeriod = AccountingPeriod::where('status', 'closed')->first();
        if (!$closedPeriod) {
            $this->markTestSkipped('Butuh periode yang sudah di-close');
        }

        $akun1 = Account::where('is_header', false)->first();
        $akun2 = Account::where('is_header', false)->where('id', '!=', $akun1->id)->first();

        $response = $this->post(route('journal-entries.store'), [
            'accounting_period_id' => $closedPeriod->id,
            'entry_date' => now()->format('Y-m-d'),
            'reference_no' => 'TEST-003',
            'description' => 'Test - closed period',
            'status' => 'draft',
            'lines' => [
                ['account_id' => $akun1->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $akun2->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $response->assertSessionHas('error');
    }

    /** @test */
    public function neraca_harus_balance_setelah_transaksi()
    {
        $this->actingAs($this->admin);

        $kas = Account::where('code', '1.1.01.01.01')->first();
        $revenue = Account::where('category', 'pendapatan')->where('is_header', false)->first();

        if (!$kas || !$revenue) {
            $this->markTestSkipped('Butuh akun kas dan pendapatan');
        }

        $entry = JournalEntry::create([
            'company_id' => $this->company->id,
            'accounting_period_id' => $this->period->id,
            'entry_date' => now()->format('Y-m-d'),
            'reference_no' => 'TEST-BALANCE-001',
            'description' => 'Test balance',
            'status' => 'posted',
            'created_by' => $this->admin->id,
            'posted_at' => now(),
        ]);

        $entry->lines()->createMany([
            ['company_id' => $this->company->id, 'account_id' => $kas->id, 'debit' => 1000000, 'credit' => 0, 'line_order' => 1],
            ['company_id' => $this->company->id, 'account_id' => $revenue->id, 'debit' => 0, 'credit' => 1000000, 'line_order' => 2],
        ]);

        $balanceService = app(BalanceSheetService::class);
        $balance = $balanceService->generate($this->period->id);

        fwrite(STDERR, "\n=== DEBUG BALANCE ===\n");
        fwrite(STDERR, "total_aktiva=" . ($balance['total_aktiva'] ?? '?') . "\n");
        fwrite(STDERR, "total_kewajiban=" . ($balance['total_kewajiban'] ?? '?') . "\n");
        fwrite(STDERR, "total_modal=" . ($balance['total_modal'] ?? '?') . "\n");
        fwrite(STDERR, "total_kewajiban_modal=" . ($balance['total_kewajiban_modal'] ?? '?') . "\n");
        fwrite(STDERR, "net_income=" . ($balance['net_income'] ?? '?') . "\n");
        fwrite(STDERR, "is_balanced=" . ($balance['is_balanced'] ? 'yes' : 'no') . "\n");
        fwrite(STDERR, "difference=" . ($balance['difference'] ?? '?') . "\n");
        fwrite(STDERR, "aktiva details count: " . count($balance['aktiva']['details'] ?? []) . "\n");
        fwrite(STDERR, "kewajiban details count: " . count($balance['kewajiban']['details'] ?? []) . "\n");
        fwrite(STDERR, "modal details count: " . count($balance['modal']['details'] ?? []) . "\n");

        $this->assertTrue($balance['is_balanced'], 'Neraca harus balance');
        $this->assertEquals($balance['total_aktiva'], $balance['total_kewajiban_modal'],
            'Total Aktiva harus sama dengan Total Kewajiban & Modal');
    }

    /** @test */
    public function laba_bersih_konsisten_antara_laba_rugi_dan_neraca()
    {
        $this->actingAs($this->admin);

        $kas = Account::where('code', '1.1.01.01.01')->first();
        $revenue = Account::where('category', 'pendapatan')->where('is_header', false)->first();

        if (!$kas || !$revenue) {
            $this->markTestSkipped('Butuh akun kas dan pendapatan');
        }

        $entry = JournalEntry::create([
            'company_id' => $this->company->id,
            'accounting_period_id' => $this->period->id,
            'entry_date' => now()->format('Y-m-d'),
            'reference_no' => 'TEST-CONSIST-001',
            'description' => 'Test consistency',
            'status' => 'posted',
            'created_by' => $this->admin->id,
            'posted_at' => now(),
        ]);

        $entry->lines()->createMany([
            ['company_id' => $this->company->id, 'account_id' => $kas->id, 'debit' => 5000000, 'credit' => 0, 'line_order' => 1],
            ['company_id' => $this->company->id, 'account_id' => $revenue->id, 'debit' => 0, 'credit' => 5000000, 'line_order' => 2],
        ]);

        $incomeService = app(IncomeStatementService::class);
        $income = $incomeService->generate($this->period->id);

        $balanceService = app(BalanceSheetService::class);
        $balance = $balanceService->generate($this->period->id);

        $this->assertGreaterThan(0, $income['net_income'], 'Laba bersih harus positif');
        $this->assertTrue($balance['is_balanced'], 'Neraca harus balance setelah transaksi');
    }
}
