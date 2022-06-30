<?php

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class CheckStatistics
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
        $companies = Company::all();

        foreach ($companies as $company) {
            $roistatConfig = $company->roistatConfig()->first();

            // it's not error, just there is no setting for roistat for this company
            if (empty($roistatConfig)) {
                continue;
            }

            // there is no error, just not set roistat settings
            if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
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

        $params = [
            'period' => $period,
        ];

        $response = $this->request($roistatConfig->roistat_project_id, $roistatConfig->api_key, $params);

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

        return empty($company->roistatStatistics()->create(array_merge($data, ['for_date' => $formatedYesterday])));
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

        if (empty($response['StatisticsItems']) || empty($response['StatisticsItems'][0])) {
            Log::warning('Roistat request response is not valid', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        return $response['StatisticsItems'][0];
    }

    /**
     * Send request to roistat server.
     *
     * @param $projectId
     * @param $key
     * @param $params
     * @return mixed
     */
    protected function request($projectId, $key, $params)
    {
        $query = "https://cloud.roistat.com/api/v1/project/statistics/get-daily?project=$projectId&key=$key";

        return Curl::to($query)
            ->withData($params)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
