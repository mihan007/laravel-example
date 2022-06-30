<?php

namespace App\Cabinet\Company\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\FinanceReportCreator;
use App\Domain\Notification\AdminSendReportVerification;
use App\Domain\Roistat\Models\RoistatProxyLeadsReport;
use App\Domain\Roistat\Models\RoistatReconciliation;
use App\Support\Helper\SessionReportMessage;
use App\Support\Helper\SessionReportStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CompanyReportController extends \App\Support\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($accountId, $id, Request $request)
    {
        $period = $request->has('period') ? new Carbon($request->period) : new Carbon();

        $company = (new Company())->getCompanyWithReportData($id, (clone $period)->startOfMonth(), (clone $period)->endOfMonth());

        $calendar = $this->getArchivePeriod();

        if (null === $company->roistatConfig) {
            $message = new SessionReportMessage(
                SessionReportStatus::ERROR(),
                'Ройстат не настроен. Зайдите в настройки компании и добавьте данные в разделе "Roistat"'
            );

            return back()->with($message->getReportVariableName(), $message->toArray()['text']);
        }

        $reportProxyLeads = $company->roistatConfig
            ->reportLeads()
            ->with('roistatProxyLead')
            ->period((clone $period)->startOfMonth(), (clone $period)->endOfMonth())
            ->active()
            ->orderBy('for_date')
            ->get();

        $periodReadable = $period->isCurrentMonth() ? 'текущий месяц' : $calendar[array_search($period->startOfMonth()->toDateString(), array_column($calendar, 'value'))]['text'];

        $calendar = array_reverse($calendar);

        $data = [];

        $data['confirmed_amount'] = $reportProxyLeads->where('user_confirmed', '1')->count();
        $data['missed_amount'] = $reportProxyLeads->where('user_confirmed', '0')->where('admin_confirmed', 3)->count();
        $data['unconfirmed_amount'] = $reportProxyLeads->whereIn('user_confirmed', [0, 2])->whereIn('admin_confirmed', [1, 2])->count();

        $data['is_approved'] = ! empty($company->roistatConfig->approvedReports) && $company->roistatConfig->approvedReports->count() > 0;

        return view(
            'pages.companies-report',
            [
                'company' => $company,
                'reportLeads' => $reportProxyLeads,
                'period' => $period->startOfMonth()->toDateString(),
                'calendar' => $calendar,
                'periodReadable' => $periodReadable,
                'data' => $data,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit($accountId, $id, Request $request)
    {
        $period = $request->has('period') && ! empty($request->period) ? new Carbon($request->period) : new Carbon();

        $company = Company::with(['roistatConfig.reportLeads' => function ($query) use ($period) {
            $query->with('roistatProxyLead')->where('for_date', '>=', $period->startOfMonth()->toDateString())
                    ->where('for_date', '<=', $period->endOfMonth()->toDateString())
                    ->orderBy('for_date');
        }])->with(['roistatConfig.approvedReports' => function ($query) use ($period) {
            $query->where('for_date', '=', $period->startOfMonth()->toDateString());
        }])
            ->findOrFail($id);

        if (! empty($company->roistatConfig->approvedReports) && $company->roistatConfig->approvedReports->count() > 0) {
            return back();
        }

        return view('pages.companies-edit-report', ['company' => $company, 'period' => $period->startOfMonth()->toDateString()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $accountId, $id)
    {
        $company = Company::with(['roistatConfig'])->findOrFail($id);

        if (! empty($request->get('report'))) {
            foreach ($request->get('report') as $key => $report) {
                $report['deleted'] = (int) $report['deleted'];

                if (isset($report['admin_confirmed']) && RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE === (int) $report['admin_confirmed']) {
                    $report['user_confirmed'] = RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED;
                }

                /** @var \App\Domain\Roistat\Models\RoistatProxyLeadsReport $reportLead */
                $reportLead = $company->roistatConfig
                        ->reportLeads()
                        ->find($report['id']);

                if (null === $reportLead) {
                    continue;
                }

                $reportLead->update($report);
            }
        }

        if (! empty($request->period)) {
            return redirect()->route('account.company.report.index', ['id' => $id]).'?period='.$request->period;
        }

        return redirect()->route('account.company.report.index', ['id' => $id]);
    }

    /**
     * Update reports via ajax.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxUpdate(Request $request, $accountId, $id)
    {
        $company = Company::with(['roistatConfig'])->findOrFail($id);

        if (! empty($request->get('report'))) {
            foreach ($request->get('report') as $key => $report) {
                $report['deleted'] = (int) $report['deleted'];

                if (isset($report['admin_confirmed']) && RoistatProxyLeadsReport::STATUS_ADMIN_DISAGREE === (int) $report['admin_confirmed']) {
                    $report['user_confirmed'] = RoistatProxyLeadsReport::STATUS_USER_NOT_CONFIRMED;
                }

                /** @var \App\Domain\Roistat\Models\RoistatProxyLeadsReport $reportLead */
                $reportLead = $company->roistatConfig
                    ->reportLeads()
                    ->find($report['id']);

                if (null === $reportLead) {
                    continue;
                }

                $reportLead->update($report);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($accountId, $id)
    {
        //
    }

    public function verify($accountId, $id, Request $request)
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::with('emailNotifications', 'roistatConfig')->findOrFail($id);

        $period = $request->has('period') ? new Carbon($request->period) : new Carbon();
        $period->startOfMonth();

        $company->notify(new AdminSendReportVerification($company, $period));

        $company->roistatConfig
            ->roistatReconciliations()
            ->create(['type' => RoistatReconciliation::ADMIN_TYPE, 'period' => $period->toDateString()]);

        (new FinanceReportCreator($company, $period))->create();

        return back()->with('message', [
            'status' => 'success',
            'text' => 'Отчет успешно отправлен на согласование',
        ]);
    }

    public function approve($accountId, $id, Request $request)
    {
        /** @var \App\Domain\Company\Models\Company $company */
        $company = Company::with('roistatConfig')->findOrFail($id);

        $period = $request->has('period') ? new Carbon($request->period) : new Carbon();
        $period->startOfMonth();

        $company->roistatConfig->approvedReports()->create(['for_date' => $period->toDateString()]);

        (new FinanceReportCreator($company, $period))->create();

        return back()->with('message', [
            'status' => 'success',
            'text' => 'Отчет утвержден',
        ]);
    }

    private function getArchivePeriod()
    {
        $calendar = [];

        $months = [
            '',
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь',
        ];

        $startDate = new Carbon('-1 year');
        $startDate->startOfMonth();

        $now = new Carbon('now');
        $now->startOfMonth();

        while ($startDate->lte($now)) {
            $month = [];

            $month['value'] = $startDate->toDateString();
            $month['text'] = $months[$startDate->month].' '.$startDate->year;

            $calendar[] = $month;

            $startDate->addMonth();
        }

        return $calendar;
    }
}
