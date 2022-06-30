<?php
/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 16.05.2017
 * Time: 9:58.
 */

namespace App\Domain\Roistat\RoistatAnalytic;

use App\Domain\Company\Models\Company;
use App\Domain\Roistat\Models\RoistatGoogleAnalytic;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class CheckGoogleAnalytic
{
    private $startDate;
    private $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Check analytics information.
     */
    public function check(Command $command)
    {
        $this->deleteAnalytic();
        $command->line("Removed data for period {$this->startDate} - {$this->endDate}", 'bg=Cyan');
        $companies = Company::all();
        $period = CarbonPeriod::create($this->startDate, $this->endDate);

        foreach ($period as $date) {
            $command->alert("Processing date {$date->format('Y-m-d')}");
            foreach ($companies as $company) {
                $command->info("Check roistat for {$company->name}");
                /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig */
                $roistatConfig = $company->roistatConfig()->first();

                // it's not error, just there is no setting for roistat for this company
                if ($roistatConfig === null) {
                    $command->warn("No roistat config for {$company->name}, skip");
                    continue;
                }

                // there is no error, just not set roistat settings
                if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
                    continue;
                }

                $analyticData = $this->getRoistatAnalytic($roistatConfig, $date);

                // it's error but we already logged error data
                if (false === $analyticData) {
                    $command
                        ->error("Error happened while request data for {$company->name} at {$date->format('Y-m-d')}");
                    continue;
                }

                $convertedAnalyticData = $this->convertMetricsResult($analyticData['metrics']);

                $roistatGoogleAnalytic = new RoistatGoogleAnalytic;
                $roistatGoogleAnalytic->timestamps = false;
                $roistatGoogleAnalytic->roistat_company_config_id = $roistatConfig->id;
                $roistatGoogleAnalytic->visitCount = $convertedAnalyticData['visitCount'] ?? 0;
                $roistatGoogleAnalytic->visits2leads = $convertedAnalyticData['visits2leads'] ?? 0;
                $roistatGoogleAnalytic->leadCount = $convertedAnalyticData['leadCount'] ?? 0;
                $roistatGoogleAnalytic->visitsCost = $convertedAnalyticData['visitsCost'] ?? 0;
                $roistatGoogleAnalytic->costPerClick = $convertedAnalyticData['costPerClick'] ?? 0;
                $roistatGoogleAnalytic->costPerLead = $convertedAnalyticData['costPerLead'] ?? 0;
                $roistatGoogleAnalytic->created_at = $date->format('Y-m-d 01:00:00');
                $roistatGoogleAnalytic->updated_at = $date->format('Y-m-d 01:00:00');
                $roistatGoogleAnalytic->save();
                $command->info("+ Stored data for {$company->name} +");
            }
        }

        return true;
    }

    protected function getRoistatAnalytic($roistatConfig, Carbon $date)
    {
        $params = [];
        $functionStartDate = clone $date;
        $functionEndDate = clone $date;

        $formatYesterdayBeginDate = $functionStartDate
                ->subDay()
                ->format('Y-m-d').'T00:00:00'.$roistatConfig->timezone;
        $formatYesterdayEndDate = $functionEndDate
                ->subDay()
                ->format('Y-m-d').'T23:59:59'.$roistatConfig->timezone;

        $params['from'] = $formatYesterdayBeginDate;
        $params['to'] = $formatYesterdayEndDate;

        $dimensionsValues = [];
        $databaseDimensionsValues = $roistatConfig->dimensionsValues()->where('is_google_active', '=', '1')->get();

        foreach ($databaseDimensionsValues as $key => $databaseDimensionsValue) {
            $dimensionsValues[] = $databaseDimensionsValue->value;
        }

        $params['dimensionsValues'] = $dimensionsValues;

        return $this->responseParser($roistatConfig,
            $this->roistatAnalyticsRequest($roistatConfig->roistat_project_id, $roistatConfig->api_key, $params));
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
            Log::warning('Roistat request response is empty', [
                'roistatConfig' => $roistatConfig->toArray(),
                'response' => $response,
            ]);

            return false;
        }

        if ($response['status'] !== 'success') {
            Log::warning('Roistat request is not successfuly finished', [
                'roistatConfig' => $roistatConfig->toArray(),
                'response' => $response,
            ]);

            return false;
        }

        if (! isset($response['data'])) {
            Log::warning('Roistat request response is not valid', [
                'roistatConfig' => $roistatConfig->toArray(),
                'response' => $response,
            ]);

            return false;
        }

        return $response['data'][0]['mean'];
    }

    protected function roistatAnalyticsRequest($projectId, $key, $params)
    {
        $query = "https://cloud.roistat.com/api/v1/project/analytics/data?project=$projectId&key=$key&is_new=1";

        $data = [
            'dimensions' => ['marker_level_1'],
            'metrics' => [
                'visitCount',
                'visits2leads',
                'leadCount',
                'visitsCost',
                'costPerClick',
                'costPerLead',
            ],
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

    private function deleteAnalytic()
    {
        $date_from = Carbon::createFromFormat('Y-m-d', $this->startDate)->startOfDay();
        $date_to = Carbon::createFromFormat('Y-m-d', $this->endDate)->endOfDay();

        RoistatGoogleAnalytic::where('created_at', '>=', $date_from)
            ->where('created_at', '<=', $date_to)
            ->delete();

        return true;
    }
}
