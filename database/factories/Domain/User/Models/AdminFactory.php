<?php

namespace Database\Factories\Domain\User\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $adminRole = RoleFactory::new()->admin()->create();
        $user = User::factory()->create();

        \App\Domain\Account\Models\AccountUser::create(
            [
                'role' => $adminRole->name,
                'user_id' => $user->id
            ],
        );
        $user->detachRoles($user->roles);
        $user->attachRole(User::ROLE_ACCOUNT_ADMIN_NAME);

        return $user->getAttributes();
    }
}
