<?php

namespace Database\Seeders;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Illuminate\Database\Seeder;

class FinanceReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // with status no orders
        create(Company::class, [], 5)->each(function (Company $company) {
            /** @var RoistatCompanyConfig $roistatConfig */
            $roistatConfig = $company->roistatConfig()
                    ->create(make(RoistatCompanyConfig::class, ['company_id' => $company->id])->toArray());

            $roistatConfig->approvedReports()->create(['for_date' => now()->subMonth()->startOfMonth()->toDateString()]);
            $roistatConfig->approvedReports()->create(['for_date' => now()->subMonth(2)->startOfMonth()->toDateString()]);
            $roistatConfig->approvedReports()->create(['for_date' => now()->subMonth(3)->startOfMonth()->toDateString()]);
        });

        // with status waiting for payment
        create(Company::class, [], 5)->each(function (Company $company) {
            /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig */
            $roistatConfig = $company->roistatConfig()
                ->create(make(RoistatCompanyConfig::class, ['company_id' => $company->id])->toArray());

            $period = now()->subMonth()->startOfMonth();

            $roistatConfig->approvedReports()->create(['for_date' => $period->toDateString()]);

            $startPeriod = $period;
            $endPeriod = now()->subMonth()->endOfMonth();

            while ($startPeriod->lte($endPeriod)) {
                create(\App\Domain\ProxyLead\Models\ProxyLeadGoalCounter::class, ['company_id' => $company->id, 'for_date' => $startPeriod->toDateString()]);

                $startPeriod->addDay();
            }
        });

        Artisan::call('finance:generate');
        Artisan::call('finance:generate', ['--period' => now()->subMonth(2)->startOfMonth()->toDateString()]);
    }
}
