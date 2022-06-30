<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 12.10.2018
 * Time: 15:54.
 */

namespace App\Domain\YandexDirect\Api;

use Ixudra\Curl\Facades\Curl;

abstract class BaseApi
{
    protected $url = '';

    public function makeAccountManagementRequest($authKey)
    {
        return Curl::to($this->url)
            ->withData([
                'method' => 'AccountManagement',
                'locale' => 'ru',
                'token' => $authKey,
                'param' => [
                    'Action' => 'Get',
                ],
            ])
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
