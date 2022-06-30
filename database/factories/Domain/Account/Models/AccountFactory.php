<?php

namespace Database\Factories\Domain\Account\Models;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;
    private static $accountAdmin;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        self::$accountAdmin = null;
        return $this->afterCreating(function (Account $account) {
            if (self::$accountAdmin) {
                \App\Domain\Account\Models\AccountUser::create(
                    [
                        'account_id' => $account->id,
                        'user_id' => self::$accountAdmin->id,
                        'role' => User::ROLE_ACCOUNT_ADMIN_NAME
                    ]
                );
            }
        });
    }

    public function admin($admin)
    {
        return $this->state(function (array $attributes) use ($admin) {
            self::$accountAdmin = $admin;

            return $attributes;
        });
    }
}
