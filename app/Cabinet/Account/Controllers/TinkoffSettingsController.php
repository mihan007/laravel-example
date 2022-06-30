<?php

namespace App\Cabinet\Account\Controllers;

use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

class TinkoffSettingsController extends Controller
{
    public function check(Request $request)
    {
        $token = $request->token;

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
        ];

        $query = http_build_query($params);
        $url = 'https://openapi.tinkoff.ru/sso/secure/token';
        $response = \Curl::to($url)
            ->withHeader('Content-Type: application/x-www-form-urlencoded')
            ->withData($query)
            ->post();
        $response_decode = json_decode($response);
        if (isset($response_decode->access_token)) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}
