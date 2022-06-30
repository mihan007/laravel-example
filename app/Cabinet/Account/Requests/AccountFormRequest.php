<?php

namespace App\Cabinet\Account\Requests;

use App\Support\Traits\CommonRules;
use Illuminate\Foundation\Http\FormRequest;

class AccountFormRequest extends FormRequest
{
    use CommonRules;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function commonRules($source)
    {
        $rules = [
            'name' => 'required',
            'account_admin' => 'required',
            'account_admin_name' => 'required_if:account_admin,new',
            'account_admin_password' => 'required_if:account_admin,new',
            'existing_admin_id' => 'required_if:account_admin,existing',
        ];
        if ($source->account_admin === 'new') {
            $rules['account_admin_email'] = 'email|unique:users,email';
        }

        return $rules;
    }
}
