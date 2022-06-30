<?php

namespace Tests\User\Actions;

use App\Domain\User\Actions\CreateUserAction;
use App\Domain\User\Models\User;
use Hash;
use Tests\Factories\UserDataFactory;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    private CreateUserAction $createUserAction;

    public function setUp(): void
    {
        parent::setUp();
        $this->truncate(User::class);
        $this->createUserAction = app(CreateUserAction::class);
    }

    /** @test */
    public function user_is_saved_in_the_database()
    {
        $userData = UserDataFactory::new()->create();
        $user = $this->createUserAction->execute($userData);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas($user->getTable(), [
            'id' => $user->id,
            'name' => $userData->name,
            'email' => $userData->email,
            'activated' => (int)$userData->activated
        ]);

        $this->assertTrue(Hash::check($userData->password, $user->password));
    }

    public function tearDown(): void
    {
        $this->truncate(User::class);
        parent::tearDown();
    }
}
