<?php

namespace App\Cabinet\ProxyLead\Controllers;

use App\Console\Commands\Applications;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Repositories\EmailableReportRepository;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Support\Constants\CompanyLeadStatus;
use App\Support\Controllers\Controller;
use App\Support\Helper\RouteHelper;
use App\View\Components\Layout\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProxyLeadsReportController extends Controller
{
    public function index(Request $request, $accountId, Company $company)
    {
        $data = [
            'company' => $company,
            'user_role' => current_general_role(),
            'company_approve_description' => $company->approve_description,
            'visibleMenuItems' => current_user_is_client() ? [Menu::MENU_ORDER] : null
        ];

        /** @var ProxyLeadSetting $proxyLeadSettings */
        $proxyLeadSettings = $company->proxyLeadSettings;
        [$startAt, $endAt] = RouteHelper::getDateRange($request);
        /** @var Collection $reportLeads */
        $reportLeads =
            $proxyLeadSettings === null ?
                [] :
                $proxyLeadSettings
                    ->proxyLeads()
                    ->period($startAt, $endAt)
                    ->with(
                        [
                            'reportLead' => function ($query) {
                                $query->withTrashed();
                            },
                        ]
                    )
                    ->latest()
                    ->get();

        $data['reportLeads'] = $reportLeads;

        if (request()->wantsJson()) {
            $data['company'] = $data['company']->toArray();
            $data['reportLeads'] = $data['reportLeads']->toArray();

            return $data;
        }

        return view('proxy-lead.index', $data);
    }

    public function update(Request $request, $accountId, Company $company, ProxyLead $lead)
    {
        if ($request->has('admin_confirmed')) {
            $lead->reportLead->adminConfirmation($request->get('admin_confirmed'));
        }

        if ($request->exists('admin_comment')) {
            $lead->reportLead->admin_comment = $request->get('admin_comment');
        }

        $lead->reportLead->save();

        return response()->json(['status' => 'success', 'data' => ['message' => '???????????? ??????????????????']]);
    }

    public function delete($accountId, Company $company, ProxyLead $lead)
    {
        $lead->trashed() ? $lead->restore() : $lead->delete();

        $lead->fresh();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'message' => $lead->trashed() ? '???????????? ??????????????' : '???????????? ??????????????????????????',
                ],
            ]
        );
    }

    public function excel(Request $request, $accountId, Company $company)
    {
        $data['company'] = $company;
        $plSettings = $company->proxyLeadSettings()->first();

        $period = $request->has('period') ? Carbon::parse($request->get('period')) : Carbon::now();

        $data['for'] = (clone $period)->startOfMonth()->toDateString();

        /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSettings */
        $proxyLeadSettings = $plSettings;
        [$startAt, $endAt] = RouteHelper::getDateRange($request);

        $reportLeads =
            $proxyLeadSettings === null ?
                collect([]) :
                $proxyLeadSettings
                    ->proxyLeads()
                    ->notDeleted()
                    ->period($startAt, $endAt)
                    ->with(
                        [
                            'reportLead.reason',
                        ]
                    )
                    ->latest()
                    ->get()
                    ->map(
                        function (ProxyLead $lead, $index) {
                            $hide = false;
                            if ($lead->proxyLeadSetting->company->date_stop_leads && $lead->created_at >= $lead->proxyLeadSetting->company->date_stop_leads
                                && !$lead->proxyLeadSetting->company->free_period
                            ) {
                                $hide = true;
                            }

                            return [
                                '#' => $index + 1,
                                'id' => $lead->id,
                                '????????' => $lead->created_at->toDateString() . ' ' . $lead->created_at->toTimeString(),
                                '??????????????????' => $lead->title ?? '',
                                '????????????????????' => !$hide ? $lead->formatted_info : '',
                                '??????????????' => !$hide ? $lead->phone : '',
                                '??????' => !$hide ? ($lead->name ?? '') : '',
                                '????????????' => !$hide ? CompanyLeadStatus::STATUSES[$lead->reportLead->company_confirmed] ?? '????????????????????' : '',
                                '??????????????????????' => !$hide ? $lead->reportLead->company_comment : '',
                                '??????. ??????.' => !$hide ? $lead->formatted_approve_status : '',
                                '??????. ??????.' => !$hide ? $lead->reportLead->admin_comment : '',
                            ];
                        }
                    );

        return $reportLeads->downloadExcel('report.xlsx', null, true);
    }

    //todo: ??????????????????????????
    public function updateClient(Request $request, $accountId, Company $company, ProxyLead $lead)
    {
        $lead->load('reportLead');
        $notificationMessage = '???????????? ??????????????????';

        $newLeadStatus = $request->get('company_confirmed');
        if ($newLeadStatus !== null) {
            if ($lead->is_expired) {
                abort(403, '???????????????????? ???????????????? ???????????? ????????????, ???????? ?????????????????? ??????????');
            }
            $companyLeadStatus = intval($newLeadStatus);
            $companyLeadReason = CompanyLeadStatus::STATUSES[$companyLeadStatus] ?? '????????????????????';
            if (in_array(
                    $companyLeadStatus,
                    CompanyLeadStatus::STATUSES_WITH_REASON,
                    true
                )
                && !$request->get('company_comment')
            ) {
                return response()->json(
                    [
                        'status' => 'error',
                        'data' => [
                            'message' => "???????????? ???? ??????????????????. ???????? ???? ?????????????? ?????????????? $companyLeadReason, ???? ?????????????????????? ?????????????? ???? ?? ?????????????????????? ????????",
                            'lead' => $lead,
                        ],
                    ]
                );
            }

            $lead->reportLead->userConfirmation($companyLeadStatus);
            $last_transaction = $this->getLastLeadTransaction($company->id, $lead->id);
            if (in_array($companyLeadStatus, CompanyLeadStatus::STATUSES_NOT_TARGET)) {
                if (!$company->free_period && $company->prepayment) {
                    if ($company->date_stop_leads && $company->balance >= $company->amount_limit) {
                        $company->date_stop_leads = null;
                    } else {
                        if (!$company->date_stop_leads && $company->balance < $company->amount_limit) {
                            $company->date_stop_leads = $company->getDateWhenLastLeadCreated();
                        }
                    }
                    $company->save();
                    if ($last_transaction && $last_transaction->operation !== 'replenishment') {
                        $payment_transaction = new PaymentTransaction();
                        $payment_transaction->company_id = $company->id;
                        $payment_transaction->amount = $last_transaction->amount;
                        $payment_transaction->operation = 'replenishment';
                        $payment_transaction->information = '?????????????????? ???????????? ???' . $lead->id;
                        $payment_transaction->status = 'replenishment';
                        $payment_transaction->payment_type = 'inside';
                        $payment_transaction->proxy_leads_id = $lead->id;
                        if ($payment_transaction->save()) {
                            $company->paymentRefund($last_transaction->amount);
                        }
                    }
                }

                if ($companyLeadStatus == PlReportLead::COMPANY_STATUS_COULD_NOT_REACH_BY_PHONE) {
                    $lead->reportLead->not_before_called_counter += 1;
                    $notificationMessage = '???????????? ??????????????????. ???????????????? ?????????????????????? ?????????? ' . Applications::CALL_INTERVAL_HOURS . ' ????????.';
                }

                if ($request->exists('company_comment')) {
                    $lead->reportLead->company_comment = $request->get('company_comment');
                }
            } else {
                if (!$company->free_period && $company->prepayment) {
                    $company->paymentWriteOff(($last_transaction) ? $last_transaction->amount : $company->lead_cost);
                    if ($company->date_stop_leads && $company->balance >= $company->amount_limit) {
                        $company->date_stop_leads = null;
                    } else {
                        if (!$company->date_stop_leads && $company->balance < $company->amount_limit) {
                            $company->date_stop_leads = $company->getDateWhenLastLeadCreated();
                        }
                    }

                    $company->save();
                    if ($last_transaction && $last_transaction->operation !== 'write-off') {
                        $payment_transaction = new PaymentTransaction();
                        $payment_transaction->company_id = $company->id;
                        $payment_transaction->amount = $last_transaction->amount ?? $company->lead_cost;
                        $payment_transaction->operation = 'write-off';
                        $payment_transaction->status = 'write-off';
                        $payment_transaction->information = '?????????????? ???????????? ???' . $lead->id;
                        $payment_transaction->payment_type = 'inside';
                        $payment_transaction->proxy_leads_id = $lead->id;
                        $payment_transaction->save();
                    }
                    new BalanceNotifier($company);
                }
            }
        }
        if ($request->exists('company_comment')) {
            $lead->reportLead->company_comment = $request->get('company_comment');
        }

        $lead->reportLead->save();
        $lead->load('reportLead');

        $proxyLeadsInfo = (new EmailableReportRepository($request, $accountId, $company))->getAndPaginate();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'message' => $notificationMessage,
                    'lead' => $lead,
                    'confirmed_amount' => $proxyLeadsInfo['confirmed_amount'],
                    'missed_amount' => $proxyLeadsInfo['missed_amount'],
                ],
            ]
        );
    }

    private function getLastLeadTransaction($company_id, $proxy_lead_id)
    {
        return PaymentTransaction::query()
            ->where('company_id', '=', $company_id)
            ->where('proxy_leads_id', '=', $proxy_lead_id)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
