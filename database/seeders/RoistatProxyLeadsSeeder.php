<?php

namespace Database\Seeders;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Illuminate\Database\Seeder;

class RoistatProxyLeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::has('roistatConfig')->get()->each(function (Company $company) {
            create(RoistatProxyLead::class, ['company_id' => $company->id], 50);
        });
    }
}
