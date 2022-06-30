<?php

namespace App\Domain\Account\Actions;

use App\Domain\Account\DataTransferObjects\AccountData;
use App\Domain\Account\Models\Account;
use App\Exceptions\DomainException;

class CreateAccountAction
{
    /**
     * @var AssignAdminToAccountAction
     */
    private AssignAdminToAccountAction $assignAdminToAccountAction;

    public function __construct(
        AssignAdminToAccountAction $assignAdminToAccountAction
    )
    {
        $this->assignAdminToAccountAction = $assignAdminToAccountAction;
    }

    public function execute(AccountData $data): Account
    {
        if (!$data->admin) {
            throw new DomainException('Could not create account without admin');
        }

        $account = Account::create(
            [
                'name' => $data->name
            ]
        );

        if ($data->admin) {
            $account = $this->assignAdminToAccountAction->execute($data->admin, $account);
        }

        return $account;
    }
}
