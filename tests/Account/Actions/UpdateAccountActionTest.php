<?php

namespace Tests\Account\Actions;

use App\Domain\Account\Actions\UpdateAccountAction;
use App\Domain\Account\DataTransferObjects\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Exceptions\DomainException;
use Database\Factories\Domain\Account\Models\AccountFactory;
use Database\Factories\Domain\User\Models\RoleFactory;
use Database\Factories\Domain\User\Models\UserFactory;
use Tests\Factories\AccountDataFactory;
use Tests\TestCase;

class UpdateAccountActionTest extends TestCase
{
    private UpdateAccountAction $updateAccountAction;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $accountWithAdmin;

    private AccountData $newAccountData;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $firstUser;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $secondUser;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $accountWithoutAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->truncate(Account::class);
        $this->truncate(User::class);
        $this->truncate(Role::class);
        $this->truncate(AccountUser::class);

        $this->firstUser = UserFactory::new()->create();
        $this->secondUser = UserFactory::new()->create();
        $adminRole = RoleFactory::new()->admin()->create();
        $managerRole = RoleFactory::new()->manager()->create();
        $this->accountWithAdmin = AccountFactory::new()->admin($this->firstUser)->create();
        $this->accountWithoutAdmin = AccountFactory::new()->create();
        $this->updateAccountAction = app(UpdateAccountAction::class);
        $this->newAccountData = AccountDataFactory::new()->create();
    }

    /** @test */
    public function fails_if_update_account_without_admin_and_no_new_admin()
    {
        $this->expectException(DomainException::class);
        $account = $this->updateAccountAction->execute($this->accountWithoutAdmin, $this->newAccountData);
    }

    /** @test */
    public function can_update_account_at_database_without_change_admin()
    {
        $account = $this->updateAccountAction->execute($this->accountWithAdmin, $this->newAccountData);

        $this->assertDatabaseHas(
            $this->accountWithAdmin->getTable(),
            [
                'id' => $account->id,
                'name' => $this->newAccountData->name
            ]
        );
    }

    /** @test */
    public function can_update_account_at_database_and_change_admin()
    {
        $data = $this->newAccountData->withAdmin($this->secondUser);
        $account = $this->updateAccountAction->execute($this->accountWithAdmin, $data);

        $this->assertDatabaseHas(
            'account_users',
            [
                'account_id' => $account->id,
                'user_id' => $this->secondUser->id,
                'role' => User::ROLE_ACCOUNT_ADMIN_NAME
            ]
        );

        $this->assertEquals(
            1,
            AccountUser::query()
                ->where('user_id', $this->secondUser->id)
                ->count()
        );
    }

    public function tearDown(): void
    {
        $this->truncate(Account::class);
        $this->truncate(User::class);
        $this->truncate(Role::class);
        $this->truncate(AccountUser::class);
    }
}
