<?php

namespace Database\Factories\Domain\Account\Models;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $account_id = Account::current()->id
            ?? (Account::exists() ? Account::all()->random()->id : Account::factory()->create()->id);

        return [
            'user_id' => User::factory()->create()->id,
            'account_id' => $account_id,
        ];
    }

    public function role($role)
    {
        return $this->state(function (array $attributes) use ($role) {
            return [
                'role' => $role
            ];
        });
    }
}
