<?php

namespace Tests\Factories;

use App\Domain\Account\DataTransferObjects\AccountData;
use App\Support\TestFactories\Factory;

class AccountDataFactory extends Factory
{
    private ?string $name;

    public static function new(): self
    {
        return new self();
    }

    public function create(array $extra = []): AccountData
    {
        return new AccountData(
            $extra + [
                'name' => \Faker\Factory::create()->word,
            ]
        );
    }

    public function withName($name)
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }
}
