<?php

namespace Database\Factories\Domain\Notification\Models;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailNotificationSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailNotificationSetting::class;

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

        return [
            'company_id' => Company::factory()->create()->id,
            'email' => $this->faker->email,
            'notification_type' => $type,
            'status' => EmailNotificationSetting::STATUS_APPROVED,
            'status_changed_at' => $this->faker->date(),
            'disable_link_key' => $this->faker->unique()->words(50)
        ];
    }
}
