<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplenishBalanceRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'operation' => 'required',
            'amount' => 'required',
            'payment_type' => 'required',
            'company_name' => 'required_if:payment_type,==,invoice_tinkoff',
            'company_inn' => 'required_if:payment_type,==,invoice_tinkoff',
        ];
    }
}
