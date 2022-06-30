<?php

namespace Tests\Factories;

use App\Domain\User\DataTransferObjects\UserData;
use App\Support\TestFactories\Factory;

class UserDataFactory extends Factory
{
    private ?bool $activated;

    public static function new(): self
    {
        return new self();
    }

    public function create(array $extra = []): UserData
    {
        return new UserData(
            $extra + [
                'name' => faker()->word,
                'password' => faker()->password,
                'email' => faker()->unique()->email,
                'activated' => faker()->boolean
            ]
        );
    }

    public function withActivated(bool $activated)
    {
        $clone = clone $this;
        $clone->activated = $activated;

        return $clone;
    }
}
