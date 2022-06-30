<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 11.10.2017
 * Time: 10:25.
 */

namespace App\Domain\Company;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\RoistatProjectAnalyticsData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyRoistatAvitoAnalyticUpdater
{
    /**
     * Date for analytic.
     *
     * @var Carbon
     */
    protected $date = null;

    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;

    public function __construct($company, $date = 'yesterday')
    {
        $this->date = new Carbon($date);
        $this->company = $company;
    }

    public function update()
    {
        /** @var RoistatCompanyConfig $roistatConfig */
        $roistatConfig = $this->company->roistatConfig()->first();

        // it's not error, just there is no setting for roistat for this company
        if (empty($roistatConfig)) {
            return false;
        }

        // there is no error, just not set roistat settings
        if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
            return false;
        }

        $roistatApi = new RoistatProjectAnalyticsData($roistatConfig->roistat_project_id, $roistatConfig->api_key);

        $from = $this->date->format('Y-m-d').'T00:00:00'.$roistatConfig->timezone;
        $to = $this->date->format('Y-m-d').'T23:59:59'.$roistatConfig->timezone;

        $roistatApi->setPeriod($from, $to);

        //@TODO We can check company for existing avitocontext dimension value, for not spamming empty data in database

        // avitocontext use this dimension value
        $roistatApi->setDimensionsValues([':utm:avitocontext']);

        $respond = $roistatApi->get();

        $analyticData = $this->responseParser($roistatConfig, $respond);

        // it's error but we already logged error data
        if (false === $analyticData) {
            return false;
        }

        $convertedAnalyticData = $this->convertMetricsResult($analyticData['metrics']);

        /** @var \App\Domain\Roistat\Models\RoistatAnalytic $databaseAnalytic */
        $databaseAnalytic = $roistatConfig->avitoAnalytics()->where('for_date', '=', $this->date->format('Y-m-d'))->first();

        if (empty($databaseAnalytic)) {
            $roistatConfig->avitoAnalytics()->create(array_merge($convertedAnalyticData, ['for_date' => $this->date->format('Y-m-d')]));
        } else {
            $databaseAnalytic->update($convertedAnalyticData);
        }

        return true;
    }

    /**
     * Convert roistat respond metric information.
     *
     * @param $respondPart
     * @return array
     */
    protected function convertMetricsResult($respondPart)
    {
        $result = [];

        $aliases = [
            'visitCount' => 'visit_count',
            'visits2leads' => 'visits_to_leads',
            'leadCount' => 'lead_count',
            'visitsCost' => 'visits_cost',
            'costPerClick' => 'cost_per_click',
            'costPerLead' => 'cost_per_lead',
        ];

        $aliasesKeys = array_keys($aliases);

        foreach ($respondPart as $key => $metric) {
            $aliasesKey = array_search($metric['metric_name'], $aliasesKeys);

            if (false === $aliasesKey) {
                $result[$metric['metric_name']] = $metric['value'];
            } else {
                // set correct name from aliases
                $result[$aliases[$aliasesKeys[$aliasesKey]]] = $metric['value'];
            }
        }

        return $result;
    }

    /**
     * Parse response from roistat server.
     *
     * @param \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig
     * @param $response
     * @return bool
     */
    protected function responseParser($roistatConfig, $response)
    {
        if (empty($response) || empty($response['status'])) {
            Log::warning('Roistat request response is empty', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        if ($response['status'] !== 'success') {
            Log::warning('Roistat request is not successfuly finished', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        if (! isset($response['data'])) {
            Log::warning('Roistat request response is not valid', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        return $response['data'][0]['mean'];
    }
}
