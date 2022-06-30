<?php

namespace Database\Factories\Domain\User\Models;

use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'display_name' => $this->faker->name,
            'name' => $this->faker->unique()->lexify()
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => User::ROLE_ACCOUNT_ADMIN_ID,
                'name' => User::ROLE_ACCOUNT_ADMIN_NAME
            ];
        });
    }

    public function manager()
    {
        return $this->state(function (array $attributes) {
            return [
                'id' => User::ROLE_ACCOUNT_MANAGER_ID,
                'name' => User::ROLE_ACCOUNT_MANAGER_NAME
            ];
        });
    }
}
