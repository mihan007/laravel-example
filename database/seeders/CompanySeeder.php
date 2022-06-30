<?php

namespace Database\Seeders;

use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        create(Company::class, ['channel_id' => null], 5);

        // with channel 1
        $channel1 = create(Channel::class);
        create(Company::class, ['channel_id' => $channel1->id], 5);

        // with channel 2
        $channel2 = create(Channel::class);
        create(Company::class, ['channel_id' => $channel2->id], 5);

        // with roistat company config
        for ($i = 0; $i < 5; $i++) {
            create(RoistatCompanyConfig::class, ['company_id' => create(Company::class)->id]);
        }
    }
}
