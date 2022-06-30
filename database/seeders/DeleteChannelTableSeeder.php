<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeleteChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channel = \App\Domain\Channel\Models\Channel::where('slug', 'company_managers')->first();

        if ($channel) {
            $channel->delete();
        }
    }
}
