<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 08.08.2018
 * Time: 10:40.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatProxyLead;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RoistatProxyLeadsSync.
 *
 * This class was created to refactor creating roistat proxy lead report for each roistat proxy lead.
 * It has tests and it should be use once. If you will run it again, i think, nothing will happen.
 */
class RoistatProxyLeadsSync
{
    /**
     * Run synchronization.
     *
     * @return bool
     */
    public function sync()
    {
        $companies = $this->getCompanies();

        foreach ($companies as $company) {
            $this->syncCompany($company);
        }

        return true;
    }

    /**
     * Receive all companies that wasn't deleted.
     *
     * @return Collection|\Illuminate\Support\Collection|static[]
     */
    private function getCompanies()
    {
        return Company::with('roistatConfig')->get();
    }

    /**
     * Synchronize only one company.
     *
     * @param $company
     * @return bool
     */
    private function syncCompany($company)
    {
        if (null === $company->roistatConfig) {
            return true;
        }

        $offset = 0;
        $limit = 100;

        $leads = $this->getPortionOfLeads($company, $offset, $limit);
        $offset += $limit;

        while ($leads->count() > 0) {
            $this->syncLeads($company, $leads);

            $leads = $this->getPortionOfLeads($company, $offset, $limit);
            $offset += $limit;
            sleep(1);
        }

        return true;
    }

    /**
     * We getting portion of leads because we don't want to get thousands of leads in one iteration.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param $offset
     * @param $limit
     * @return Collection
     */
    private function getPortionOfLeads(Company $company, $offset, $limit)
    {
        return $company->roistatProxyLeads()->offset($offset)->limit($limit)->get();
    }

    /**
     * Synchronize leads.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param Collection $leads
     * @return bool
     */
    private function syncLeads(Company $company, Collection $leads)
    {
        foreach ($leads as $lead) {
            $this->syncLead($company, $lead);
        }

        return true;
    }

    /**
     * Set changes for each lead.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param \App\Domain\Roistat\Models\RoistatProxyLead $lead
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function syncLead(Company $company, RoistatProxyLead $lead)
    {
        $searchData = ['roistat_proxy_lead_id' => $lead->id];
        $updateData = array_merge(
            $lead->toArray(),
            [
                'roistat_company_config_id' => $company->roistatConfig->id,
                'roistat_proxy_lead_id' => $lead->id,
            ]
        );

        return $company->roistatConfig
            ->reportLeads()
            ->updateOrCreate($searchData, $updateData);
    }
}
