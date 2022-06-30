<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 12.10.2018
 * Time: 16:01.
 */

namespace App\Domain\YandexDirect\Api;

class ApiStrategy
{
    public function get($isTest = false)
    {
        if ($isTest) {
            return new TestApi();
        }

        return new PublicApi();
    }
}
