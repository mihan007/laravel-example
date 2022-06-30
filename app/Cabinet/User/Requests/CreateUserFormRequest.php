<?php

namespace App\Cabinet\User\Requests;

use App\Domain\User\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->is_super_admin
            || (int)request()->role !== User::ROLE_SUPER_ADMIN_ID;
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('Недостаточно прав для добавления пользователя.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Пользователь с email ' . $this->email . ' уже существует. Введите другой email пользователя.',
        ];
    }
}
