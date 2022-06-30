<?php

namespace Tests\Account\Actions;

use App\Domain\Account\Actions\CreateAccountAction;
use App\Domain\Account\DataTransferObjects\AccountData;
use App\Domain\Account\Models\Account;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Exceptions\DomainException;
use Database\Factories\Domain\User\Models\RoleFactory;
use Database\Factories\Domain\User\Models\UserFactory;
use Tests\Factories\AccountDataFactory;
use Tests\TestCase;

class CreateAccountActionTest extends TestCase
{
    private AccountData $accountData;

    private CreateAccountAction $createAccountAction;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(Account::class);
        $this->truncate(User::class);
        $this->truncate(Role::class);

        $this->accountData = AccountDataFactory::new()->create();
        $this->user = UserFactory::new()->create();

        $this->createAccountAction = app(CreateAccountAction::class);
        RoleFactory::new()->admin()->create();
    }

    /** @test */
    public function account_without_admin_can_not_be_saved_at_database()
    {
        $this->expectException(DomainException::class);
        $account = $this->createAccountAction->execute($this->accountData);
    }

    /** @test */
    public function account_with_admin_is_saved_at_database()
    {
        $account = $this->createAccountAction->execute($this->accountData->withAdmin($this->user));

        $this->assertInstanceOf(User::class, $account->admin);
        $this->assertEquals($this->user->id, $account->admin->id);
    }

    public function tearDown(): void
    {
        $this->truncate(Account::class);
        $this->truncate(User::class);
        $this->truncate(Role::class);
    }
}
