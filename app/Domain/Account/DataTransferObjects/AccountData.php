<?php

namespace App\Domain\Account\DataTransferObjects;

use App\Cabinet\Account\Requests\AccountFormRequest;
use App\Domain\User\Models\User;
use App\Http\Livewire\Account\AccountForm;
use Spatie\DataTransferObject\DataTransferObject;

class AccountData extends DataTransferObject
{
    public ?string $name;

    public ?User $admin;

    public static function fromRequest(AccountFormRequest $request): AccountData
    {
        return new self(
            [
                'name' => $request->input('name'),
            ]
        );
    }

    public static function fromLivewire(AccountForm $form): AccountData
    {
        return new self(
            [
                'name' => $form->name,
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
