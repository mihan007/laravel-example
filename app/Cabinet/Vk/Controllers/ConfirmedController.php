<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 23.07.2018
 * Time: 12:02.
 */

namespace App\Cabinet\Vk\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

class ConfirmedController
{
    public function index(Request $request)
    {
        $data['code'] = $request->get('code', '');

        return view('pages.vk.key-generator.confirmed.index')->with('data', $data);
    }

    public function store(Request $request)
    {
        $code = $request->get('code', '');

        $response = Curl::to('https://oauth.vk.com/access_token')
            ->withData([
                'client_id' => env('VK_GENERATOR_CLIENT_ID'),
                'client_secret' => env('VK_GENERATOR_CLIENT_SECRET_KEY'),
                'redirect_uri' => env('VK_GENERATOR_REDIRECT_URL'),
                'code' => $code,
            ])
            ->asJson(true)
            ->get();

        return back()->with('access_token', $response['access_token']);
    }
}
