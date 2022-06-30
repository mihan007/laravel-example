<?php

namespace App\Cabinet\Vk\Controllers;

use App\Support\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class KeyGeneratorController extends Controller
{
    public function index()
    {
        return view('pages.vk.key-generator.index');
    }

    public function store()
    {
        $clientId = env('VK_GENERATOR_CLIENT_ID');
        $redirectUrl = env('VK_GENERATOR_REDIRECT_URL');

        $ulr = "https://oauth.vk.com/authorize?client_id=$clientId&display=page&redirect_uri=$redirectUrl&scope=ads,offline&response_type=code&v=5.80";

        return Redirect::away($ulr);
    }
}
