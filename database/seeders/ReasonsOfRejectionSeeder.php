<?php

namespace Database\Seeders;

use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Illuminate\Database\Seeder;

class ReasonsOfRejectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons = [
            'Дубль заявки',
            'Не отвечают на звонок более 5 раз',
            'Неправильный номер телефона',
            'Не оставляли заявку',
            'Отказались от услуги на совсем при первом звонке',
            'Другой город',
        ];

        foreach ($reasons as $reason) {
            create(ReasonsOfRejection::class, ['name' => $reason]);
        }
    }
}
