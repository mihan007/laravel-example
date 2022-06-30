<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 05.05.2017
 * Time: 16:32.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatAnalytic;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class CheckAnalytics
{
    public function __construct()
    {
    }

    /**
     * Check analytics information.
     */
    public function check()
    {
        $companies = Company::where(['deleted_at' => null])->all();

        foreach ($companies as $company) {
            /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig */
            $roistatConfig = $company->roistatConfig()->first();

            // it's not error, just there is no setting for roistat for this company
            if (empty($roistatConfig)) {
                continue;
            }

            // there is no error, just not set roistat settings
            if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
                continue;
            }

            $analyticData = $this->getRoistatAnalytic($roistatConfig);

            // it's error but we already logged error data
            if (false === $analyticData) {
                continue;
            }

            $convertedAnalyticData = $this->convertMetricsResult($analyticData['metrics']);

            /** @var RoistatAnalytic $mostRecentAnalytic */
            $mostRecentAnalytic = $roistatConfig->mostRecentAnalytic()->first();

            if (empty($mostRecentAnalytic)) {
                $roistatConfig->analytics()->create($convertedAnalyticData);
            } else {
                $mostRecentAnalytic->update($convertedAnalyticData);
            }
        }

        return true;
    }

    protected function getRoistatAnalytic($roistatConfig)
    {
        $params = [];

        $yesterday = strtotime('-1 day');
        $formatYesterdayBeginDate = date('Y-m-d', $yesterday).'T00:00:00'.$roistatConfig->timezone;
        $formatYesterdayEndDate = date('Y-m-d', $yesterday).'T23:59:59'.$roistatConfig->timezone;

        $params['from'] = $formatYesterdayBeginDate;
        $params['to'] = $formatYesterdayEndDate;

        $dimensionsValues = [];
        $databaseDimensionsValues = $roistatConfig->dimensionsValues()->where('is_active', '=', '1')->get();

        foreach ($databaseDimensionsValues as $key => $databaseDimensionsValue) {
            $dimensionsValues[] = $databaseDimensionsValue->value;
        }

        $params['dimensionsValues'] = $dimensionsValues;

        return $this->responseParser($roistatConfig, $this->roistatAnalyticsRequest($roistatConfig->roistat_project_id, $roistatConfig->api_key, $params));
    }

    protected function convertMetricsResult($respondPart)
    {
        $result = [];

        foreach ($respondPart as $key => $metric) {
            $result[$metric['metric_name']] = $metric['value'];
        }

        return $result;
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

        if (! isset($response['data'])) {
            Log::warning('Roistat request response is not valid', ['roistatConfig' => $roistatConfig->toArray(), 'response' => $response]);

            return false;
        }

        return $response['data'][0]['mean'];
    }

    protected function roistatAnalyticsRequest($projectId, $key, $params)
    {
        $query = "https://cloud.roistat.com/api/v1/project/analytics/data?project=$projectId&key=$key&is_new=1";

        $data = [
            'dimensions' => ['marker_level_1'],
            'metrics' => ['visitCount', 'visits2leads', 'leadCount', 'visitsCost', 'costPerClick', 'costPerLead'],
            'period' => [
                'from' => $params['from'],
                'to' => $params['to'],
            ],
        ];

        if ($params['dimensionsValues']) {
            $data['filters'][] = [
                'field' => 'marker_level_1',
                'operation' => 'in',
                'value' => $params['dimensionsValues'],
            ];
        }

        return Curl::to($query)
            ->withData($data)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
