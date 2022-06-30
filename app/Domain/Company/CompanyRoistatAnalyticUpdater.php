<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.10.2017
 * Time: 15:18.
 */

namespace App\Domain\Company;

use App\Domain\Roistat\Models\RoistatCompanyConfig;
use App\Domain\Roistat\RoistatProjectAnalyticsData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyRoistatAnalyticUpdater
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
        /** @var \App\Domain\Roistat\Models\RoistatCompanyConfig $roistatConfig */
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

        $dimensionsValues = $this->getCompanyDimensionsValues($this->company);

        $roistatApi->setDimensionsValues($dimensionsValues);

        $respond = $roistatApi->get();

        $analyticData = $this->responseParser($roistatConfig, $respond);

        // it's error but we already logged error data
        if (false === $analyticData) {
            return false;
        }

        $convertedAnalyticData = $this->convertMetricsResult($analyticData['metrics']);

        $roistatConfig->analytics()->updateOrCreate(['for_date' => $this->date->format('Y-m-d')], $convertedAnalyticData);

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

        foreach ($respondPart as $key => $metric) {
            $result[$metric['metric_name']] = $metric['value'];
        }

        return $result;
    }

    /**
     * Take company dimensions values.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @return array
     */
    private function getCompanyDimensionsValues($company)
    {
        $databaseDimensionsValues = $company->roistatConfig->dimensionsValues()->where('is_active', '=', '1')->get();

        $dimensionsValues = [];

        foreach ($databaseDimensionsValues as $key => $databaseDimensionsValue) {
            // перестаем учитывать авитоконтекс для аналитики
            if ('avitocontext' == $databaseDimensionsValue->title) {
                continue;
            }

            $dimensionsValues[] = $databaseDimensionsValue->value;
        }

        return $dimensionsValues;
    }

    /**
     * Parse response from roistat server.
     *
     * @param RoistatCompanyConfig $roistatConfig
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
