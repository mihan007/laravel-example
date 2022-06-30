<?php

namespace Tests\User\Actions;

use App\Domain\User\Actions\UpdateUserAction;
use App\Domain\User\Models\User;
use Database\Factories\Domain\User\Models\UserFactory;
use Hash;
use Tests\Factories\UserDataFactory;
use Tests\TestCase;

class UpdateUserActionTest extends TestCase
{
    private User $user;

    private UpdateUserAction $updateUserAction;

    public function setUp(): void
    {
        parent::setUp();
        $this->truncate(User::class);
        $this->user = UserFactory::new()->create();
        $this->updateUserAction = app(UpdateUserAction::class);
    }

    /** @test */
    public function can_update_user_in_the_database()
    {
        $userData = UserDataFactory::new()->create();
        $user = $this->updateUserAction->execute($this->user, $userData);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas($user->getTable(), [
            'name' => $userData->name,
            'email' => $userData->email,
            'activated' => (int)$userData->activated
        ]);

        $this->assertTrue(Hash::check($userData->password, $user->password));
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
