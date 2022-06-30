<?php

namespace App\Domain\ProxyLead;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckProxyleadPayment
{
    private $date;
    private $lead_cost = 200;
    private $proxy_lead_for_check_ids = [
        17774,
        17771,
        17764,
        17761,
        17757,
    ];

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function check(Command $command)
    {
        $proxy_leads = $this->getProxyLeads();
        foreach ($proxy_leads as $proxy_lead) {
            $this->checkOrUpdateProxyLead($proxy_lead, $command);
        }
        $this->checkProxyLeadReturnTransaction();
    }

    private function checkProxyLeadReturnTransaction()
    {
        foreach ($this->proxy_lead_for_check_ids as $proxy_lead_id) {
            $proxy_lead = $this->getProxyLead($proxy_lead_id);
            if ($proxy_lead) {
                /** @var \App\Domain\Company\Models\Company $proxy_lead_company */
                $proxy_lead_company = $proxy_lead->proxyLeadSetting->company;
                $last_transaction = $this->getLastTransaction($proxy_lead_company->id, $proxy_lead->id);
                if ($last_transaction && $last_transaction->operation != 'replenishment') {
                    $payment_transaction = new PaymentTransaction();
                    $payment_transaction->company_id = $proxy_lead_company->id;
                    $payment_transaction->amount = $this->lead_cost;
                    $payment_transaction->operation = 'replenishment';
                    $payment_transaction->status = 'replenishment';
                    $payment_transaction->information = 'Возврат средств за заявку:'.$proxy_lead->id;
                    $payment_transaction->payment_type = 'inside';
                    $payment_transaction->proxy_leads_id = $proxy_lead->id;

                    if ($payment_transaction->save()) {
                        \DB::table('companies')
                            ->where('id', $proxy_lead_company->id)
                            ->update(['balance' => \DB::raw('companies.balance +'.$this->lead_cost)]);
                        echo "Вернули {$this->lead_cost} компании {$proxy_lead_company->name}({$proxy_lead_company->id}) за заявку {$proxy_lead_id}\n";
                    }
                }
            }
        }
    }

    private function checkOrUpdateProxyLead(ProxyLead $proxy_lead, Command $command)
    {
        $proxy_lead_status = $proxy_lead->reportLead->company_confirmed;
        $has_duplicates = (new DuplicateChecker($proxy_lead))->check();
        $this->checkOrUpdateProxyLeadStatus($proxy_lead, $has_duplicates, $command, $proxy_lead_status);
    }

    private function checkOrUpdateProxyLeadStatus(
        ProxyLead $proxy_lead,
        $has_duplicates,
        Command $command,
        $proxy_lead_status
    ) {
        $proxy_lead_company = $this->getProxyLeadCompany($proxy_lead);
        if ($has_duplicates) {
            if ($proxy_lead->reportLead->company_confirmed != $proxy_lead_status) {
                $command->line(
                    "Для компании ({$proxy_lead_company->name}) был изменен слатус прокси-лида(id={$proxy_lead->id}) на 'Дубль заявки'",
                    'bg=green'
                );
            }
            if (! $proxy_lead_company->free_period && $proxy_lead_company->prepayment) {
                $this->checkOrUpdateProxyLeadPayment(
                    $proxy_lead,
                    $proxy_lead_company,
                    $command,
                    PlReportLead::STATUS_DOUBLE_APPLICATION
                );
            }
        } else {
            if ($proxy_lead->reportLead->company_confirmed != PlReportLead::STATUS_AGREE) {
                $proxy_lead->reportLead()
                    ->update(
                        [
                            'company_confirmed' => PlReportLead::STATUS_AGREE,
                            'admin_confirmed' => PlReportLead::STATUS_AGREE,
                        ]
                    );
                $command->line(
                    "Для компании ({$proxy_lead_company->name}) был изменен слатус прокси-лида(id={$proxy_lead->id}) на 'Целевая заявка'",
                    'bg=green'
                );
            }
            if (! $proxy_lead_company->free_period && $proxy_lead_company->prepayment) {
                $this->checkOrUpdateProxyLeadPayment(
                    $proxy_lead,
                    $proxy_lead_company,
                    $command,
                    PlReportLead::STATUS_AGREE
                );
            }
        }
    }

    private function checkOrUpdateProxyLeadPayment(
        ProxyLead $proxy_lead,
        Company $proxy_lead_company,
        Command $command,
        $status
    ) {
        $last_transaction = $this->getLastTransaction($proxy_lead_company->id, $proxy_lead->id);
        $amount = ($last_transaction) ? $last_transaction->amount : $proxy_lead_company->lead_cost;
        if ($status == PlReportLead::STATUS_DOUBLE_APPLICATION) {
            if ($last_transaction && $last_transaction->operation != 'replenishment') {
                $payment_transaction = new PaymentTransaction();
                $payment_transaction->company_id = $proxy_lead_company->id;
                $payment_transaction->amount = $amount;
                $payment_transaction->operation = 'replenishment';
                $payment_transaction->status = 'replenishment';
                $payment_transaction->information = 'Нецелевая заявка №'.$proxy_lead->id;
                $payment_transaction->payment_type = 'inside';
                $payment_transaction->proxy_leads_id = $proxy_lead->id;
                if ($payment_transaction->save()) {
                    \DB::table('companies')
                        ->where('id', $proxy_lead_company->id)
                        ->update(['balance' => \DB::raw('companies.balance +'.$amount)]);
                    $command->line(
                        "Для компании ({$proxy_lead_company->name}) были начислены средства({$amount})",
                        'bg=green'
                    );
                }
            }
        } else {
            if ($status == PlReportLead::STATUS_AGREE) {
                if (! $last_transaction || $last_transaction->operation != 'write-off') {
                    $payment_transaction = new PaymentTransaction();
                    $payment_transaction->company_id = $proxy_lead_company->id;
                    $payment_transaction->amount = $amount;
                    $payment_transaction->operation = 'write-off';
                    $payment_transaction->status = 'write-off';
                    $payment_transaction->information = 'Целевая заявка №'.$proxy_lead->id;
                    $payment_transaction->payment_type = 'inside';
                    $payment_transaction->proxy_leads_id = $proxy_lead->id;
                    if ($payment_transaction->save()) {
                        \DB::table('companies')
                            ->where('id', $proxy_lead_company->id)
                            ->update(['balance' => \DB::raw('companies.balance -'.$amount)]);
                        $command->line(
                            "Для компании ({$proxy_lead_company->name}) были спысаны средства({$amount}) за прокси-лид({$proxy_lead->id})",
                            'bg=green'
                        );
                    }
                }
            }
        }
    }

    private function getProxyLead($id)
    {
        return ProxyLead::find($id);
    }

    private function getProxyLeads()
    {
        $date_from = Carbon::createFromFormat('d/m/Y', $this->date)->startOfDay();
        $date_to = Carbon::createFromFormat('d/m/Y', $this->date)->endOfDay();

        return ProxyLead::where('created_at', '>=', $date_from)
            ->where('created_at', '<=', $date_to)
            ->orderBy('created_at', 'decs')
            ->get();
    }

    private function getLastTransaction($company_id, $proxy_lead_id)
    {
        return PaymentTransaction::query()
            ->where('company_id', '=', $company_id)
            ->where('proxy_leads_id', '=', $proxy_lead_id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    private function getProxyLeadCompany(ProxyLead $proxy_lead)
    {
        return $proxy_lead->proxyLeadSetting->company;
    }
}
