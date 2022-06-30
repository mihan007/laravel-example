<?php

namespace App\Cabinet\ProxyLead\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Company\Repositories\EmailableReportRepository;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\TargetCounterFactory;
use App\Support\Constants\CompanyLeadStatus;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * EmailableController class
 * @author Horai Dmytro <horaiwork4@gmail.com>
 */
class EmailableController extends Controller
{
    /**
     * Get company Report
     *
     * @param Request $request
     * @param integer $accountId
     * @param \App\Domain\Company\Models\Company $company
     * @return \Illuminate\Support\Collection
     */
    public function index(Request $request , int $accountId, Company $company)
    {
        return (new EmailableReportRepository($request, $accountId, $company))->getAndPaginate();
    }

    /**
     * Get current active timezone for company.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @return mixed|null
     */
    private function getTimezone(Company $company)
    {
        $roistatCompanyConfigs = $company->roistatConfig()->first();

        if ($roistatCompanyConfigs === null) {
            return null;
        }

        return $roistatCompanyConfigs->php_timezone;
    }

    public function getData(Request $request, $proxyLeadSettings, $period, $timezone = null)
    {
        $sort = $request->has('sort') ? $request->get('sort', 'id') : 'id';
        $order = $request->has('order') ? $request->get('order', 'asc') : 'asc';
        $limit = $request->has('limit') ? $request->get('limit', 10) : 10;
        $filter = $request->has('filter') ? $request->get('filter', '') : '';
        $ids = $request->has('ids') ? explode(',', $request->get('ids', '')) : '';

        if (! $proxyLeadSettings) {
            return collect([]);
        }

        if ($ids) {
            $reportLeadsBuilder =
                $this->getProxyLeadBuilderQueryNotPeriod($proxyLeadSettings, $period)
                    ->with(
                        [
                            'reportLead' => function ($query) {
                                $query->withTrashed();
                            },
                            'reportLead.reason',
                        ]
                    );
        } else {
            $reportLeadsBuilder =
                $this->getProxyLeadBuilderQuery($proxyLeadSettings, $period)
                    ->with(
                        [
                            'reportLead' => function ($query) {
                                $query->withTrashed();
                            },
                            'reportLead.reason',
                        ]
                    );
        }

        $reportLeadsBuilder->orderBy($sort, $order);

        if ($request->has('filter')) {
            $reportLeadsBuilder->where(
                function (Builder $query) use ($filter) {
                    $normalizedFilterValue = strtolower(trim($filter));
                    $query->whereRaw('LOWER(`title`) LIKE ? ', ['%'.$normalizedFilterValue.'%'])
                        ->orWhereRaw('LOWER(`name`) LIKE ? ', ['%'.$normalizedFilterValue.'%'])
                        ->orWhereRaw('id LIKE ? ', ['%'.$normalizedFilterValue.'%'])
                        ->orWhereRaw('LOWER(`comment`) LIKE ? ', ['%'.$normalizedFilterValue.'%'])
                        ->orWhereRaw('LOWER(`phone`) LIKE ? ', ['%'.$normalizedFilterValue.'%']);
                }
            );
        }

        if ($request->has('ids')) {
            $reportLeadsBuilder->where(
                function (Builder $query) use ($ids) {
                    $query->whereIn('id', $ids);
                }
            );
        }

        if (! empty($request->get('status', ''))) {
            $reportLeadsBuilder->whereHas(
                'reportLead',
                function ($query) {
                    /* @var Builder $query */
                    switch (request()->get('status', '')) {
                        case 'is_target':
                            $query->where('company_confirmed', PlReportLead::STATUS_AGREE);
                            break;
                        case 'is_not_targeted':
                            $query->where('company_confirmed', '!=', 1);
                            break;
                        case 'is_not_confirmed':
                            $query->where('company_confirmed', PlReportLead::STATUS_NOT_CONFIRMED)
                                ->orWhere('admin_confirmed', PlReportLead::STATUS_NOT_CONFIRMED);
                            break;
                        default:
                            break;
                    }
                }
            );
        }

        $allReportsBuilder = $reportLeadsBuilder->get();
        $reportLeadsBuilder->paginate($limit);
        $allReports = $reportLeadsBuilder->get();

        if ($timezone !== null) {
            $allReportsBuilder->each(
                function ($lead, $key) use ($timezone) {
                    $lead->created_at = $lead->created_at->setTimezone($timezone);
                    $lead->updated_at = $lead->updated_at->setTimezone($timezone);
                }
            );
        }

        $undeletedReports = $allReportsBuilder->filter(
            function ($proxyLead) {
                return ! $proxyLead->trashed();
            }
        );

        $targetCounter = (new TargetCounterFactory($proxyLeadSettings->company()->first()))->get();

        $proxy_lead = new ProxyLead();

        $data = [
            'items' => $reportLeadsBuilder->paginate($limit),
            'confirmed_amount' => $targetCounter->getTargetCount($undeletedReports),
            'missed_amount' => $targetCounter->getNonTargetCount($undeletedReports),
            'unconfirmed_amount' => $targetCounter->getNotConfirmedCount($undeletedReports),
            'unconfirmed_admin_amount' => $targetCounter->getNotConfirmedAdminCount($undeletedReports),
            'unconfirmed_user_amount' => $targetCounter->getNotConfirmedUserCount($undeletedReports),
            'all_reports' => $allReports,
            'statuses' => CompanyLeadStatus::STATUSES,
        ];

        return $data;
    }

    /**
     * Update proxy lead.
     *
     * @param Request $request
     * @param \App\Domain\Company\Models\Company $company
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $accountId, Company $company, ProxyLead $proxyLead)
    {
        if ($request->has('admin_confirmed')) {
            $proxyLead->reportLead->adminConfirmation($request->get('admin_confirmed'));
        }

        if ($request->exists('admin_comment')) {
            $proxyLead->reportLead->admin_comment = $request->get('admin_comment');
        }
        if ($request->get('admin_confirmed') === 0) {
            $proxyLead->reportLead->moderation_status = 1;
        }
        $proxyLead->reportLead->save();

        if ($request->get('admin_confirmed') === 0 && $proxyLead->is_free != true && $company->prepayment) {
            $this->transactionBalance($company, 'subtract', $proxyLead, false);
        }

        return response()->json(
            [
                'status' => 'success',
                'data' => ['message' => 'Данные обновлены'],
            ]
        );
    }

    public function destroy($accountId, Company $company, $proxyLead)
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $lead */
        $lead = ProxyLead::withTrashed()->findOrFail($proxyLead);
        $company = Company::findOrFail($lead->proxyLeadSetting->company_id);
        $last_transaction = $this->checkCompanyProxyLeadPaymentTransaction($company->id, $lead->id);
        if ($lead->trashed()) {
            $lead->restore();
            if ($last_transaction
                && $last_transaction->operation = 'replenishment'
                    && $lead->reportLead->company_confirmed == PlReportLead::STATUS_AGREE) {
                $this->transactionBalance($company, 'subtract', $lead);
            }

            return response()->json(
                [
                    'status' => 'success',
                    'data' => ['message' => 'Заявка восстановлена'],
                ]
            );
        }
        $last_transaction = $this->checkCompanyProxyLeadPaymentTransaction($company->id, $lead->id);

        if ($last_transaction && $last_transaction->operation != 'replenishment') {
            $this->transactionBalance($company, 'addition', $lead);
        }
        $leadId = $lead->id;
        $lead->delete();

        return response()->json(
            [
                'status' => 'success',
                'data' => ['message' => 'Заявка удалена №'.$leadId],
            ]
        );
    }

    public function transactionBalance(Company $company, $operation, ProxyLead $lead, $removal_and_recovery = true)
    {
        $last_transaction = $this->checkCompanyProxyLeadPaymentTransaction($company->id, $lead->id);
        if (! $company->free_period && $company->prepayment) {
            if ($operation == 'subtract') {
                $company->paymentWriteOff(($last_transaction) ? $last_transaction->amount : $company->lead_cost);
                $operation = 'write-off';
                $information = $removal_and_recovery ? 'Заявка восстановлена №'.$lead->id : 'Целевая заявка №'.$lead->id;
                $status = 'write-off';
            } else {
                $company->paymentRefund(($last_transaction) ? $last_transaction->amount : $company->lead_cost);
                $operation = 'replenishment';
                $information = $removal_and_recovery ? 'Заявка удалена №'.$lead->id : 'Нецелевая заявка №'.$lead->id;
                $status = 'replenishment';
            }

            if ($company->date_stop_leads && $company->balance >= $company->amount_limit) {
                $company->date_stop_leads = null;
            } else {
                if (! $company->date_stop_leads && $company->balance < $company->amount_limit) {
                    $company->date_stop_leads = $company->getDateWhenLastLeadCreated();
                }
            }

            $payment_transaction = new PaymentTransaction();
            $payment_transaction->company_id = $company->id;
            $payment_transaction->amount = ($last_transaction) ? $last_transaction->amount : $company->lead_cost;
            $payment_transaction->operation = $operation;
            $payment_transaction->information = $information;
            $payment_transaction->status = $status;
            $payment_transaction->payment_type = 'inside';
            $payment_transaction->proxy_leads_id = $lead->id;
            if ($payment_transaction->save()) {
                $company->save();
            }
        }
    }

    /**
     * Get proxy leads query.
     *
     * @param ProxyLeadSetting $proxyLeadSettings
     * @param Carbon $period
     * @return Builder
     */
    public function getProxyLeadBuilderQuery(ProxyLeadSetting $proxyLeadSettings, Carbon $period)
    {
        return $proxyLeadSettings
            ->proxyLeads()
            ->period((clone $period)->startOfMonth(), (clone $period)->endOfMonth())
            ->withTrashed();
    }

    public function getProxyLeadBuilderQueryNotPeriod(ProxyLeadSetting $proxyLeadSettings, Carbon $period)
    {
        return $proxyLeadSettings
            ->proxyLeads()
            ->withTrashed();
    }

    private function checkCompanyProxyLeadPaymentTransaction($company_id, $proxy_lead_id)
    {
        $last_payment_teansaction = PaymentTransaction::query()
            ->where('company_id', '=', $company_id)
            ->where('proxy_leads_id', '=', $proxy_lead_id)
            ->orderBy('id', 'DESC')
            ->first();

        return $last_payment_teansaction;
    }
}
