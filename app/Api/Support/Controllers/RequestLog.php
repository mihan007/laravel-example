<?php
/**
 * For local.troiza.net.
 * User: ttt
 * Date: 19.06.2019
 * Time: 11:59.
 */

namespace App\Api\Support\Controllers;

use App\Models\RequestLogger;
use Illuminate\Http\Request;

class RequestLog
{
    public function log(Request $request)
    {
        $a = 1;

        $requestLog = new RequestLogger([
            'url' => $request->url(),
            'method' => $request->method(),
            'post' => $_POST,
            'get' => $_GET,
            'raw_post' => $request->getContent(),
        ]);

        $requestLog->save();
    }

    public function show(Request $request)
    {
        return view(
            'request-log.request-list',
            [
                'requests' => RequestLogger::all(),
            ]
        );
    }
}
