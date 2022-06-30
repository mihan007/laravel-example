<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\DataTransferObjects\AccountData;
use App\Domain\Account\Models\Account;
use App\Exceptions\DomainException;

class UpdateAccountAction
{
    /**
     * @var AssignAdminToAccountAction
     */
    private AssignAdminToAccountAction $assignAdminToAccountAction;
    /**
     * @var AssignManagerToAccountAction
     */
    private AssignManagerToAccountAction $assignManagerToAccountAction;

    public function __construct(
        AssignAdminToAccountAction $assignAdminToAccountAction,
        AssignManagerToAccountAction $assignManagerToAccountAction
    ) {
        $this->assignAdminToAccountAction = $assignAdminToAccountAction;
        $this->assignManagerToAccountAction = $assignManagerToAccountAction;
    }

    public function execute(Account $account, AccountData $data): Account
    {
        $account->fill(
            [
                'name' => $data->name,
            ]
        )->save();


        $isNewAccountAdmin = $data->admin && $account->admin && ($data->admin->id != $account->admin->id);
        if ($account->admin && $isNewAccountAdmin) {
            $account = $this->assignManagerToAccountAction->execute($account->admin, $account);
            $account = $this->assignAdminToAccountAction->execute($data->admin, $account);
        } elseif (!$account->admin) {
            if (!$data->admin) {
                throw new DomainException('You should assign admin to account');
            }
            $account = $this->assignAdminToAccountAction->execute($data->admin, $account);
        }

        return $account->refresh();
    }
}
