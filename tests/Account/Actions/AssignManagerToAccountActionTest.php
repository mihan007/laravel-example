<?php

namespace Tests\Account\Actions;

use App\Domain\Account\Actions\AssignManagerToAccountAction;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Database\Factories\Domain\Account\Models\AccountFactory;
use Database\Factories\Domain\User\Models\ManagerFactory;
use Database\Factories\Domain\User\Models\RoleFactory;
use Database\Factories\Domain\User\Models\UserFactory;
use Tests\TestCase;

class AssignManagerToAccountActionTest extends TestCase
{
    private Account $account;

    private AssignManagerToAccountAction $assignManagerToAccountAction;

    private User $user;
    /**
     * @var Role
     */
    private $adminRole;
    /**
     * @var User
     */
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Role::class);
        $this->truncate(Account::class);
        $this->truncate(User::class);
        $this->truncate(AccountUser::class);
        $this->truncate(CompanyRoleUser::class, false);

        $this->adminRole = RoleFactory::new()->admin()->create();
        $this->account = AccountFactory::new()->create();
        $this->user = UserFactory::new()->create();
        $this->admin = ManagerFactory::new()->make();
        $this->assignManagerToAccountAction = app(AssignManagerToAccountAction::class);
    }

    /** @test */
    public function can_assign_manager_to_account()
    {
        $this->assignManagerToAccountAction->execute($this->user, $this->account);

        $this->assertDatabaseHas(
            'account_users',
            [
                'user_id' => $this->user->id,
                'role' => User::ROLE_ACCOUNT_MANAGER_NAME,
                'account_id' => $this->account->id
            ]
        );
    }

    /** @test */
    public function can_assign_manager_to_account_and_he_get_role_manager()
    {
        $this->assignManagerToAccountAction->execute($this->user, $this->account);

        $this->assertTrue($this->user->hasRole(User::ROLE_ACCOUNT_MANAGER_NAME));
    }

    /** @test */
    public function can_assign_manager_to_account_and_he_has_only_one_role()
    {
        $this->assignManagerToAccountAction->execute($this->user, $this->account);

        $this->assertCount(1, $this->user->roles);
    }

    /** @test */
    public function can_reassign_admin_to_manager_of_account()
    {
        $this->assignManagerToAccountAction->execute($this->admin, $this->account);

        $this->assertDatabaseHas(
            'account_users',
            [
                'user_id' => $this->admin->id,
                'role' => User::ROLE_ACCOUNT_MANAGER_NAME,
                'account_id' => $this->account->id
            ]
        );
    }

    /** @test */
    public function can_reassign_admin_to_manager_of_account_and_he_get_role_manager()
    {
        $this->assignManagerToAccountAction->execute($this->admin, $this->account);

        $this->assertTrue($this->admin->hasRole(User::ROLE_ACCOUNT_MANAGER_NAME));
    }

    /** @test */
    public function can_reassign_admin_to_manager_of_account_and_he_has_only_one_role()
    {
        $this->assignManagerToAccountAction->execute($this->admin, $this->account);

        $this->assertCount(1, $this->admin->roles);
    }

    public function tearDown(): void
    {
        $this->truncate(Role::class);
        $this->truncate(Account::class);
        $this->truncate(AccountUser::class);
        $this->truncate(CompanyRoleUser::class, false);
        $this->truncate(User::class);
    }
}
