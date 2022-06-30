<?php

namespace App\Domain\ProxyLead;

use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\Models\ProxyLead;

class Balance
{
    /**
     * Balance constructor.
     * @param \App\Domain\Company\Models\Company $company
     * @param \App\Domain\ProxyLead\Models\ProxyLead $proxyLead
     * @param $isSimilarLeadExists
     * @param null $forcedLeadPrice
     */
    public function __construct(Company $company, ProxyLead $proxyLead, $isSimilarLeadExists, $forcedLeadPrice = null)
    {
        if ($company->free_period || ! $company->prepayment || $isSimilarLeadExists) {
            $leadCost = 0;
        } else {
            $leadCost = $forcedLeadPrice ?: $company->lead_cost;
        }

        (new UpdateCompanyBalanceAction())->execute($company, -$leadCost);

        $payment_transaction = new PaymentTransaction();
        $payment_transaction->company_id = $company->id;
        $payment_transaction->amount = $leadCost;
        $information = 'Целевая заявка №'.$proxyLead->id;
        if ($isSimilarLeadExists) {
            $information = 'Заявка №'.$proxyLead->id.', дубликат';
        }
        $payment_transaction->information = $information;
        $payment_transaction->operation = 'write-off';
        $payment_transaction->status = 'write-off';
        $payment_transaction->payment_type = 'inside';
        $payment_transaction->proxy_leads_id = $proxyLead->id;
        if ($payment_transaction->save()) {
            $company->save();
        }
    }
}
