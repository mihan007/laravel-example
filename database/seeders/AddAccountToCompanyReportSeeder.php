<?php

namespace Database\Seeders;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyReport;
use App\Domain\Finance\Models\PaymentTransaction;
use Illuminate\Database\Seeder;

class AddAccountToCompanyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->getOutput()->progressStart(CompanyReport::count());
        CompanyReport::chunk(
            5000,
            function ($companyReports) {
                foreach ($companyReports as $companyReport) {
                    $this->command->getOutput()->progressAdvance();

                    $companyReport->account_id = optional($companyReport->company)->account_id;
                    $companyReport->save();
                }
            }
        );
        $this->command->getOutput()->progressFinish();
    }
}
