<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 12.10.2018
 * Time: 16:36.
 */

namespace App\Domain\YandexDirect;

use App\Domain\Company\Models\Company;
use App\Domain\YandexDirect\Api\BaseApi;
use Illuminate\Support\Facades\Log;

class CompanyBalanceGetter
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;
    /**
     * @var BaseApi
     */
    private $api;

    public function __construct(Company $company, BaseApi $api)
    {
        $this->company = $company;
        $this->api = $api;
    }

    public function get()
    {
        return $this->parseResponce($this->makeRequest());
    }

    private function makeRequest()
    {
        return $this->api->makeAccountManagementRequest($this->company->yandexDirectConfig->yandex_auth_key);
    }

    private function parseResponce($response)
    {
        if (
            isset($response['error_code']) ||
            empty($response['data']) ||
            empty($response['data']['Accounts']) ||
            empty($response['data']['Accounts'][0]) ||
            ! isset($response['data']['Accounts'][0]['Amount'])
        ) {
            Log::warning(
                "Fail to get balance from yandex direct for company '{$this->company->name}'",
                ['company' => $this->company->toArray(), 'response' => $response]
            );

            return false;
        }

        return (float) $response['data']['Accounts'][0]['Amount'];
    }
}
