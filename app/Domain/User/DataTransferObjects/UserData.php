<?php

namespace App\Domain\User\DataTransferObjects;

use App\Cabinet\Account\Requests\AccountFormRequest;
use App\Http\Livewire\Account\AccountForm;
use Spatie\DataTransferObject\DataTransferObject;

class UserData extends DataTransferObject
{
    public ?string $name;
    public ?string $password;
    public ?string $email;
    public bool $activated = false;

    public static function fromAccountFormRequest(AccountFormRequest $request): UserData
    {
        return new self(
            [
                'name' => $request->input('account_admin_name'),
                'password' => $request->input('account_admin_password'),
                'email' => $request->input('account_admin_email'),
            ]
        );
    }

    public static function fromAccountFormLivewire(AccountForm $form): UserData
    {
        return new self(
            [
                'name' => $form->account_admin_name,
                'password' => $form->account_admin_password,
                'email' => $form->account_admin_email,
            ]
        );
    }

    public function withActivated(bool $activated): UserData
    {
        $clone = clone $this;
        $clone->activated = $activated;

        return $clone;
    }
}
