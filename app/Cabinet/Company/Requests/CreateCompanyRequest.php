<?php

namespace App\Cabinet\Company\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompanyRequest extends FormRequest
{
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
     * @return array|\string[][]
     */
    public function messages()
    {
        return [
            'name.min' => 'Количество символов должно быть не меньше 3',
            'name.max' => 'Количество символов должно быть не больше 255',
            'channel_id.required' => 'Поле обязательно для заполнения',
            'application_moderation_period.min'=>'Значение должно быть больше или равно 0.'
        ];
    }

    /**
     * Все имена полей делаем пустыми, чтобы не занимать в форме место
     *
     * @return array|string[]
     */
    public function attributes()
    {
        return [
            'name' => '',
            'approve_description' => '',
            'lead_cost' => '',
            'channel_id' => '',
            'application_moderation_period' => '',
            'balance_limit' => '',
            'roistat_config.max_lead_price' => '',
            'amount_limit' => '',
            'role_users.*' => ''
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \Validator::extend(
            'empty',
            function ($attribute, $value, $parameters, $validator) {
                return empty($value);
            }
        );

        return [
            'name' => 'required|min:3|max:255',
            'approve_description' => 'max:500',
            'lead_cost' => 'nullable|numeric|min:0',
            'channel_id' => 'required|exists:channels,id',
            'application_moderation_period' => 'nullable|numeric|min:0|max:45',
            'balance_limit' => 'nullable|numeric|min:0',
            'roistat_config.max_lead_price' => 'nullable|numeric|min:0',
            'amount_limit' => 'nullable|numeric',
            'role_users.*' => (\Auth::user()->is_super_admin||\Auth::user()->is_admin)?'nullable|numeric':'empty',
        ];
    }
}
