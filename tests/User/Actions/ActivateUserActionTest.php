<?php

namespace Tests\User\Actions;

use App\Domain\User\Actions\ActivateUserAction;
use App\Domain\User\Models\User;
use Database\Factories\Domain\User\Models\UserFactory;
use Tests\TestCase;

class ActivateUserActionTest extends TestCase
{
    public ActivateUserAction $activateUserAction;

    private User $inactiveUser;

    private User $activeUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncate(User::class);
        $this->activateUserAction = app(ActivateUserAction::class);
        $this->inactiveUser = UserFactory::new(['activated' => 0])->create();
        $this->activeUser = UserFactory::new(['activated' => 1])->create();
    }

    /** @test */
    public function can_activate_inactive_user(): void
    {
        $this->assertFalse($this->inactiveUser->activated);
        $this->activateUserAction->execute($this->inactiveUser);

        $this->assertTrue($this->inactiveUser->activated);
        $this->assertDatabaseHas($this->inactiveUser->getTable(), [
            'id' => $this->inactiveUser->id,
            'activated' => 1
        ]);
    }

    /** @test */
    public function can_do_nothing_with_active_user(): void
    {
        $this->assertTrue($this->activeUser->activated);
        $this->activateUserAction->execute($this->inactiveUser);

        $this->assertTrue($this->activeUser->activated);
        $this->assertDatabaseHas($this->inactiveUser->getTable(), [
            'id' => $this->inactiveUser->id,
            'activated' => 1
        ]);
    }

    public function tearDown(): void
    {
        $this->truncate(User::class);
        parent::tearDown();
    }
}
