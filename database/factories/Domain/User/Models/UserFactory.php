<?php

namespace Database\Factories\Domain\User\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
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
        static $password;
        $email = $this->faker->unique()->safeEmail . random_int(1000, 9999);

        return [
            'name' => $this->faker->name,
            'email' => $email,
            'password' => $password ?: ($password = Hash::make('secret')),
            'remember_token' => Str::random(10),
            'activated' => 1
        ];
    }
}
