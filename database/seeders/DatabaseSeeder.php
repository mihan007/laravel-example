<?php

namespace Database\Seeders;

use Database\Seeders\CompanySeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);

        for ($i = 0; $i < 5; $i++) {
            $this->call(ProxyLeadCompanySeeder::class);
        }
    }
}
