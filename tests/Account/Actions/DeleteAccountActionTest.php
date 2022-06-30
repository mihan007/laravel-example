<?php

namespace Tests\Account\Actions;

use App\Domain\Account\Actions\DeleteAccountAction;
use App\Domain\Account\Models\Account;
use Database\Factories\Domain\Account\Models\AccountFactory;
use Tests\TestCase;

class DeleteAccountActionTest extends TestCase
{
    private DeleteAccountAction $deleteAccountAction;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->truncate(Account::class);

        $this->account = AccountFactory::new()->create();
        $this->deleteAccountAction = app(DeleteAccountAction::class);
    }

    /** @test */
    public function can_delete_account_from_database()
    {
        $this->assertDatabaseHas($this->account->getTable(), [
            'id' => $this->account->id,
        ]);

        $account = $this->deleteAccountAction->execute($this->account);

        $this->assertSoftDeleted($this->account->getTable(), [
            'id' => $this->account->id,
        ]);
    }

    public function tearDown(): void
    {
        $this->truncate(Account::class);
    }
}
