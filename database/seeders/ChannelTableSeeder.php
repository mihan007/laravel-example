<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channel = new \App\Domain\Channel\Models\Channel();
        $channel->name = 'Компании менеджеров';
        $channel->slug = 'company_managers';
        $channel->save();
    }
}
