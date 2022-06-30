<?php

namespace App\Cabinet\Amocrm\Controllers;

use App\Domain\Amocrm\RequestResolver;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AmocrmWebhook extends Controller
{
    public function index(Request $request)
    {
        Log::info('new webhook', $request->toArray());
        $resolver = new RequestResolver($request);

        $resolver->resolve();
    }
}
