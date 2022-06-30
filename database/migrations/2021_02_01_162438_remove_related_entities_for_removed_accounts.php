<?php

use App\Domain\Account\Actions\DeleteAccountAction;
use App\Domain\Account\Models\Account;
use Illuminate\Database\Migrations\Migration;

class RemoveRelatedEntitiesForRemovedAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Account $account */
        $accounts = Account::withTrashed()->whereNotNull('deleted_at')->get();
        $accounts->each(
            function ($account) {
                (new DeleteAccountAction())->execute($account);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
