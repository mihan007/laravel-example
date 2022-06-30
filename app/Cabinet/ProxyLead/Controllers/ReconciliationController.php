<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 20.07.2018
 * Time: 14:14.
 */

namespace App\Cabinet\ProxyLead\Controllers;

use App\Domain\Company\Events\StoreReconciliationEvent;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\Reconclication;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function show($accountId, Company $company, $period)
    {
        $company->load('proxyLeadSettings');

        $period = Carbon::parse($period);

        if (null === $company->proxyLeadSettings) {
            return response()->json(
                [
                    'status' => 'error',
                    'data' => ['message' => 'У компании "'.$company->name.'" не активно прокси лидирование.'],
                ],
                422
            );
        }

        if (! Carbon::now()->startOfMonth()->gt($period->startOfMonth())) {
            return response()->json([
                'status' => 'success',
                'data' => [
                        'allowed' => false,
                        'reason' => 'Доступ может быть предоставлен не раньше окончания месяца.',
                    ],
            ]);
        }

        $lastReconclication = Reconclication::where([
                ['period', $period->toDateString()],
                ['proxy_lead_setting_id', $company->proxyLeadSettings->id],
            ])
            ->latest()
            ->first();

        if (null === $lastReconclication) {
            return response()->json([
                'status' => 'success',
                'data' => ['allowed' => true],
            ]);
        }

        if ('user' === $lastReconclication->type) {
            return response()->json([
                'status' => 'success',
                'data' => ['allowed' => true],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                    'allowed' => false,
                    'reason' => 'Администратор уже отправлял отчет на согласование '.$lastReconclication->created_at->toDateTimeString().'.',
                ],
        ]);
    }

    public function store(Request $request, $accountId, Company $company)
    {
        $company->loadMissing(['emailNotifications', 'proxyLeadSettings']);

        if (null === $company->emailNotifications || $company->emailNotifications->isEmpty()) {
            return response()->json(['status' => 'error', 'data' => ['message' => 'Не указаны получатели письма. Необходимо зайти в раздел "Управление письмами" и добавить получателей.']]);
        }

        $period = $request->has('period') ? new Carbon($request->period) : Carbon::now()->startOfMonth();

        event(new StoreReconciliationEvent($company, $period));

        return response()->json(['status' => 'success', 'data' => ['message' => 'Отчет успешно отправлен на согласование']]);
    }
}
