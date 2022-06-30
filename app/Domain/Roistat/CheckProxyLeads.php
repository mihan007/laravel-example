<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 02.05.2017
 * Time: 12:14.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class CheckProxyLeads
{
    public function __construct()
    {
    }

    /**
     * Check report of all companies and store it into database.
     *
     * @return bool
     */
    public function check()
    {
        $companies = Company::with('roistatConfig', 'proxyLeadSettings')->get();

        /** @var Collection $companies */
        $companies = $companies->filter(function (Company $company) {
            return null === $company->proxyLeadSettings;
        });

        foreach ($companies as $company) {
            $roistatConfig = $company->roistatConfig;

            // it's not error, just there is no setting for roistat for this company
            if (empty($roistatConfig)) {
                continue;
            }

            // there is no error, just not set roistat settings
            if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key) || $roistatConfig->api_key === '-') {
                continue;
            }

            $roistatInformation = $this->getDailyInformation($roistatConfig);

            // it's error but we already logged error data
            if (false === $roistatInformation) {
                continue;
            }

            $this->storeDailyInformation($company, $roistatInformation);
        }

        return true;
    }

    protected function getDailyInformation($roistatConfig)
    {
        $yesterday = strtotime('-1 day');
        $formatedYesterday = date('Y-m-d', $yesterday);
        $period = $formatedYesterday.'-'.$formatedYesterday;

        $response = $this->request($roistatConfig->roistat_project_id, $roistatConfig->api_key, $period);

        return $this->responseParser($roistatConfig, $response);
    }

    /**
     * Add statistic information into database.
     *
     * @param $company
     * @param $data
     * @return bool
     */
    protected function storeDailyInformation($company, $data)
    {
        $yesterday = strtotime('-1 day');
        $formatedYesterday = date('Y-m-d', $yesterday);

        foreach ($data as $item) {
            /* @var \App\Domain\Company\Models\Company @company */
            $company->roistatProxyLeads()->create(
                array_merge(
                    $item,
                    [
                        'for_date' => $formatedYesterday,
                        'roistat_id' => $item['id'] ?? null,
                    ]
                )
            );
        }

        return true;
    }

    /**
     * Parse response from roistat server.
     *
     * @param $roistatConfig
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

        if (! isset($response['ProxyLeads'])) {
            Log::warning('Roistat request response is not valid', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        return $response['ProxyLeads'];
    }

    /**
     * Send request to roistat server.
     *
     * @param $projectId
     * @param $key
     * @param $period
     * @return mixed
     */
    protected function request($projectId, $key, $period)
    {
        $query = "https://cloud.roistat.com/api/v1/project/proxy-leads?project=$projectId&key=$key&period=$period";

        return Curl::to($query)
            ->withContentType('application/json')
            ->asJson(true)
            ->get();
    }
}
