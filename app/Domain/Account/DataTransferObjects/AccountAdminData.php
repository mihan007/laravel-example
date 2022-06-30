<?php

namespace App\Domain\Account\DataTransferObjects;

use App\Cabinet\Account\Requests\AccountFormRequest;
use App\Domain\User\DataTransferObjects\UserData;
use App\Domain\User\Models\User;
use App\Http\Livewire\Account\AccountForm;
use Spatie\DataTransferObject\DataTransferObject;

class AccountAdminData extends DataTransferObject
{
    public bool $isNew;

    public UserData $userData;

    public ?int $existingAdminId;

    public static function fromRequest(AccountFormRequest $request): self
    {
        return new self(
            [
                'isNew' => $request->input('account_admin') === 'new',
                'userData' => UserData::fromAccountFormRequest($request),
                'existingAdminId' => $request->input('existing_admin_id')
            ]
        );
    }

    public static function fromLivewire(AccountForm $form): self
    {
        return new self(
            [
                'isNew' => $form->account_admin === 'new',
                'userData' => UserData::fromAccountFormLivewire($form),
                'existingAdminId' => (int)$form->existing_admin_id
            ]
        );
    }

    public function withAdmin(User $admin): AccountData
    {
        $clone = clone $this;
        $clone->admin = $admin;

        return $clone;
    }
}
