<?php

namespace Tests\Account\Actions;

use App\Domain\Account\Actions\AssignAdminToAccountAction;
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

class AssignAdminToAccountActionTest extends TestCase
{
    private Account $account;

    private AssignAdminToAccountAction $assignAdminToAccountAction;

    private User $user;
    /**
     * @var Role
     */
    private $adminRole;
    /**
     * @var User
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Role::class);
        $this->truncate(Account::class);
        $this->truncate(AccountUser::class);
        $this->truncate(CompanyRoleUser::class, false);
        $this->truncate(User::class);

        $this->adminRole = RoleFactory::new()->admin()->create();
        $this->account = AccountFactory::new()->create();
        $this->user = UserFactory::new()->create();
        $this->manager = ManagerFactory::new()->make();
        $this->assignAdminToAccountAction = app(AssignAdminToAccountAction::class);
    }

    /** @test */
    public function can_remove_assigned_admin_from_all_companies()
    {
        $this->assignAdminToAccountAction->execute($this->user, $this->account);

        $this->assertDatabaseMissing('company_role_users', ['user_id' => $this->user->id]);
    }

    /** @test */
    public function can_assign_admin_to_account()
    {
        $this->assignAdminToAccountAction->execute($this->user, $this->account);

        $this->assertDatabaseHas('account_users', [
            'user_id' => $this->user->id,
            'role' => User::ROLE_ACCOUNT_ADMIN_NAME,
            'account_id' => $this->account->id
        ]);
    }

    /** @test */
    public function can_assign_admin_to_account_and_he_get_role_admin()
    {
        $this->assignAdminToAccountAction->execute($this->user, $this->account);

        $this->assertTrue($this->user->hasRole(User::ROLE_ACCOUNT_ADMIN_NAME));
    }

    /** @test */
    public function can_assign_admin_to_account_and_he_has_only_one_role()
    {
        $this->assignAdminToAccountAction->execute($this->user, $this->account);

        $this->assertCount(1, $this->user->roles);
    }

    public function can_remove_assigned_manager_from_specific_company()
    {
        $this->assignAdminToAccountAction->execute($this->manager, $this->account);

        $this->assertDatabaseMissing('company_role_users', ['user_id' => $this->manager->id]);
    }

    /** @test */
    public function can_reassign_manager_to_admin_of_account()
    {
        $this->assignAdminToAccountAction->execute($this->manager, $this->account);

        $this->assertDatabaseHas('account_users', [
            'user_id' => $this->manager->id,
            'role' => User::ROLE_ACCOUNT_ADMIN_NAME,
            'account_id' => $this->account->id
        ]);

        $this->assertTrue($this->manager->hasRole(User::ROLE_ACCOUNT_ADMIN_NAME));
    }

    /** @test */
    public function can_reassign_manager_to_admin_of_account_and_he_get_role_admin()
    {
        $this->assignAdminToAccountAction->execute($this->manager, $this->account);

        $this->assertTrue($this->manager->hasRole(User::ROLE_ACCOUNT_ADMIN_NAME));
    }

    /** @test */
    public function can_reassign_manager_to_admin_of_account_and_he_has_only_one_role()
    {
        $this->assignAdminToAccountAction->execute($this->manager, $this->account);

        $this->assertCount(1, $this->manager->roles);
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
