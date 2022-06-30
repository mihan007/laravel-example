<?php

namespace App\Cabinet\Account\Controllers;

use App\Domain\Account\Models\AboutCompany;
use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountSetting;
use App\Domain\Tinkoff\Models\TinkoffSetting;
use App\Domain\YooMoney\Models\YandexSetting;
use App\Support\Controllers\Controller;
use App\Support\Rules\Bik;
use App\Support\Rules\Inn;
use App\Support\Rules\Ks;
use App\Support\Rules\Rs;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class SettingsController extends Controller
{
    public function index($accountId)
    {
        $pageAccount = Account::findOrFail($accountId);

        $yandexSetting = $pageAccount->yandexSetting ?: new YandexSetting();
        $tinkoffSetting = $pageAccount->tinkoffSetting ?: new TinkoffSetting();
        $accountSettings = $pageAccount->accountSetting ?: new AccountSetting();
        $aboutCompany = $pageAccount->aboutCompany ?: new AboutCompany();

        return view(
            'pages.settings',
            [
                'yandexSetting' => $yandexSetting,
                'tinkoffSetting' => $tinkoffSetting,
                'accountSettings' => $accountSettings,
                'aboutCompany' => $aboutCompany,
                'accountId' => $accountId,
            ]
        );
    }

    public function saveYandexMoney(Request $request, $accountId): MessageBag
    {
        $isActive = $request->yandex_is_active ?? false;
        $messageBag = new MessageBag();

        if ($isActive) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'wallet_number' => 'numeric',
                ]
            );

            if ($validator->fails()) {
                $messageBag->merge($validator->errors());
            }
        }

        if ($messageBag->isNotEmpty()) {
            return $messageBag;
        }

        $result = YandexSetting::updateOrCreate(
            ['account_id' => $accountId],
            $request->all() + ['is_active' => $isActive]
        );

        if (! $result) {
            $messageBag->add('yandex_setting_save_issue', 'Ошибка сохранения настроек яндекса');
        }

        return $messageBag;
    }

    public function saveTinkoff(Request $request, $accountId): MessageBag
    {
        $isActive = $request->tinkoff_is_active ?? 0;
        $messageBag = new MessageBag();

        $requestData = $request->all();
        if (empty($requestData['inn'])) {
            $requestData['inn'] = $requestData['a_inn'] ?? null;
        }
        if (empty($requestData['account'])) {
            $requestData['account'] = $requestData['r_account'] ?? null;
        }

        if ($isActive) {
            $validator = \Validator::make(
                $requestData,
                [
                    'account' => 'required|numeric',
                    'token' => 'required',
                    'inn' => ['required', 'numeric', new Inn()],
                ]
            );

            if ($validator->fails()) {
                $messageBag->merge($validator->errors());
            }
        }

        if ($messageBag->isNotEmpty()) {
            return $messageBag;
        }

        $result = TinkoffSetting::updateOrCreate(
            ['account_id' => $accountId],
            $request->all() + ['is_active' => $isActive]
        );

        if (! $result) {
            $messageBag->add('tinkoff_setting_save_issue', 'Ошибка сохранения настроек Тинькофф');
        }

        return $messageBag;
    }

    public function saveAccount(Request $request, $accountId): MessageBag
    {
        $isActive = $request->account_is_active;
        $messageBag = new MessageBag();

        if ($isActive) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank' => 'required|max:255',
                    'bik' => ['required', 'numeric', new Bik()],
                    'k_account' => ['required', 'numeric', new Ks()],
                    'r_account' => ['required', 'numeric', new Rs()],
                    'name' => 'required|max:255',
                    'u_name' => 'required|max:255',
                    'a_inn' => ['required', 'numeric', new Inn()],
                    'city' => 'required|max:255',
                    'address' => 'required|max:255',
                    'head' => 'required|max:255',
                    'index' => 'nullable|numeric',
                    'seal_img_raw' => 'max:100kb|Mimes:png',
                    'head_sign_raw' => 'max:100kb|Mimes:png',
                    'accountant_sign_raw' => 'max:100kb|Mimes:png',
                ]
            );
            $attributeNames = [
                'seal_img_raw' => 'Печать',
                'head_sign_raw' => 'Подпись руководителя',
                'accountant_sign_raw' => 'Подпись бухгалтера',
            ];
            $validator->setAttributeNames($attributeNames);

            if ($validator->fails()) {
                $messageBag->merge($validator->errors());
            }
        }

        /** @var \App\Domain\Account\Models\Account $currentAccount */
        $currentAccount = Account::findOrFail($accountId);
        $aboutCompany = $currentAccount->aboutCompany;

        if (! isset($aboutCompany) && $isActive) {
            $validatorImages = [
                'seal_img_raw' => 'required|max:100kb|Mimes:png',
                'head_sign_raw' => 'required|max:100kb|Mimes:png',
                'accountant_sign_raw' => 'required|max:100kb|Mimes:png',
            ];
            $validator = \Validator::make(
                $request->all(),
                $validatorImages
            );
            $validator->setAttributeNames($attributeNames);
            if ($validator->fails()) {
                $messageBag->merge($validator->errors());
            }
        }

        if ($messageBag->isNotEmpty()) {
            return $messageBag;
        }

        $result = AccountSetting::updateOrCreate(
            ['account_id' => $accountId],
            $request->all() + ['is_active' => $isActive]
        );

        if (! $result) {
            $messageBag->add('account_setting_save_issue', 'Ошибка сохранения настроек расчетного счета');
        }

        $params = $request->all();
        $params['inn'] = $request->a_inn;
        $result = AboutCompany::updateOrCreate(
            ['account_id' => $accountId],
            $params
        );

        if (! $result) {
            $messageBag->add('account_setting_save_issue', 'Ошибка сохранения настроек расчетного счета');
        }

        $aboutCompany = $currentAccount->aboutCompany ?? new AboutCompany();
        $aboutCompany->account_id = $currentAccount->id;
        if ($request->file('seal_img_raw')) {
            $result = $aboutCompany->saveImage(
                $aboutCompany->seal_img,
                $request->file('seal_img_raw')->path(),
                $request->file('seal_img_raw')->extension()
            );
            if (! $result) {
                $messageBag->merge(['Формат картинки должен быть PNG']);
            }
            $aboutCompany->seal_img = $result;
        }
        if ($request->file('head_sign_raw')) {
            $result = $aboutCompany->saveImage(
                $aboutCompany->head_sign,
                $request->file('head_sign_raw')->path(),
                $request->file('head_sign_raw')->extension()
            );
            if (! $result) {
                $messageBag->merge(['Формат картинки должен быть PNG']);
            }
            $aboutCompany->head_sign = $result;
        }
        if ($request->file('accountant_sign_raw')) {
            $result = $aboutCompany->saveImage(
                $aboutCompany->accountant_sign,
                $request->file('accountant_sign_raw')->path(),
                $request->file('accountant_sign_raw')->extension()
            );
            if (! $result) {
                $messageBag->merge(['Формат картинки должен быть PNG']);
            }
            $aboutCompany->accountant_sign = $result;
        }
        $aboutCompany->save();

        return $messageBag;
    }

    /**
     * @param Request $request
     * @param $accountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, $accountId)
    {
        $messagesBag = new MessageBag();
        $savingYandexMoneyResult = $this->saveYandexMoney($request, $accountId);
        $messagesBag->merge($savingYandexMoneyResult);

        $savingTinkoffResult = $this->saveTinkoff($request, $accountId);
        $messagesBag->merge($savingTinkoffResult);

        $savingAccountResult = $this->saveAccount($request, $accountId);
        $messagesBag->merge($savingAccountResult);

        return redirect()->back()->withInput()->withErrors($messagesBag);
    }
}
