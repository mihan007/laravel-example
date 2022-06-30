<?php

namespace Database\Factories\Domain\Notification\Models;

use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailNotificationFactory extends Factory
{
    use HasFactory;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     * @throws Exception
     */
    public function definition()
    {
        $types = EmailNotification::getListOfAvailableTypes();
        $type = $types[random_int(0, count($types) - 1)];
        $notificationSetting = EmailNotificationSetting::factory()->make(['notification_type' => $type]);

        return [
            'company_id' => function () use ($notificationSetting) {
                return $notificationSetting->company_id;
            },
            'type' => $type,
            'email' => $this->faker->email
        ];
    }
}
