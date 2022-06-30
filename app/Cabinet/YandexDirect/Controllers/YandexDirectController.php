<?php

namespace App\Cabinet\YandexDirect\Controllers;

use App\Domain\Company\Models\Company;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YandexDirectController extends Controller
{
    /**
     * Show page with yandex url fragment parser.
     */
    public function create()
    {
        return view('pages.yandex-direct-parser');
    }

    public function store(Request $request)
    {
        $status = false;

        if ($request->get('error')) {
            Log::error('Fail to get yandex direct token', $request->all());

            $message = 'Произошла непредвиденная ошибка. Попробуйте повторить запрос позже или свяжитесь с администратором.';

            return redirect()
                ->route('account.yandex_direct-confirmation')
                ->with('status', $status)
                ->with('message', $message);
        }

        if (empty($request->get('state'))) {
            Log::error('Fail to get state from yandex direct token request', $request->all());

            $message = 'Произошла непредвиденная ошибка. Попробуйте повторить запрос позже или свяжитесь с администратором.';

            return redirect()
                ->route('account.yandex_direct-confirmation')
                ->with('status', $status)
                ->with('message', $message);
        }

        $status = true;

        $company = Company::findOrFail($request->get('state'));
        $yandexDirectConfig = $company->yandexDirectConfig()->first();

        $yandexDirectConfig->yandex_auth_key = $request->get('access_token');
        $yandexDirectConfig->token_life_time = $request->get('expires_in');
        $yandexDirectConfig->token_added_on = Carbon::now();
        $yandexDirectConfig->save();

        $message = 'Токен успешно добавлен.';

        return redirect()
            ->route('account.yandex_direct-confirmation', [$company])
            ->with('status', $status)
            ->with('message', $message);
    }

    public function show($id = null)
    {
        if (! empty($id)) {
            $company = Company::findOrFail($id);

            return view('pages.yandex-direct-confirmation', ['company' => $company]);
        }

        return view('pages.yandex-direct-confirmation');
    }
}
