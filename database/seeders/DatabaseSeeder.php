<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name' => 'PT. Transkargo Solusindo',
            'npwp' => '01.234.567.8-999.000',
            'address' => 'Jakarta, Indonesia',
            'default_currency' => 'IDR',
        ]);

        $admin = User::create([
            'name' => 'Admin Akuntansi',
            'email' => 'admin@transkargo.co.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'current_company_id' => $company->id,
        ]);
        $admin->companies()->attach($company->id, ['role' => 'admin']);

        $staff = User::create([
            'name' => 'Staff Akuntansi',
            'email' => 'staff@transkargo.co.id',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'current_company_id' => $company->id,
        ]);
        $staff->companies()->attach($company->id, ['role' => 'staff']);

        $this->command->info('========================================');
        $this->command->info('  AKUN USER DEFAULT');
        $this->command->info('========================================');
        $this->command->info('  Admin: admin@transkargo.co.id / password');
        $this->command->info('  Staff: staff@transkargo.co.id / password');
        $this->command->info('  Company: PT. Transkargo Solusindo');
        $this->command->info('========================================');

        // Seed with company context via inline call
        $this->callWithCompany(AccountSeeder::class, $company->id);
        $this->callWithCompany(AccountingPeriodSeeder::class, $company->id);
        $this->callWithCompany(DummyTransactionSeeder::class, $company->id);
    }

    private function callWithCompany(string $seederClass, int $companyId): void
    {
        $seeder = app()->make($seederClass);
        $seeder->setCommand($this->command);
        $seeder->companyId = $companyId;
        $seeder->run();
    }
}
