<?php

namespace App\Domain\YooMoney\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\PaymentTransaction;

class YooMoney
{
    /** @var \App\Domain\Account\Models\Account */
    public $account;

    public $company;

    public $payment_type;

    public $amount;

    public $payment_transaction_id;

    public function setup(PaymentTransaction $paymentTransaction)
    {
        $this->company = $paymentTransaction->company;
        $paymentTransaction->information = ($paymentTransaction->payment_type === 'yandex_money_pc')
            ? 'Яндекс деньги'
            : 'Банковская карта';
        $paymentTransaction->saveQuietly();

        $this->payment_transaction_id = $paymentTransaction->id;
        $this->payment_type = $paymentTransaction->payment_type;

        return $this;
    }

    public function setAccount($account_id)
    {
        $this->account = Account::findOrFail($account_id);

        return $this;
    }

    public function pay($amount)
    {
        $this->amount = $amount;

        $query = 'https://money.yandex.ru/quickpay/confirm.xml';

        $params = [
            'receiver' => $this->account->yandexSetting->wallet_number,
            'quickpay-form' => 'shop',
            'targets' => 'Оплата рекламной деятельности. '.$this->company->name,
            'paymentType' => $this->payment_type == 'yandex_money_pc' ? 'PC' : 'AC',
            'sum' => $this->amount,
            'label' => $this->payment_transaction_id,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $query);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return $redirectURL;
    }
}
