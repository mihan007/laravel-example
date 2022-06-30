<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 05.10.2017
 * Time: 21:18.
 */

namespace App\Domain\Roistat;

use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class GetCompanyAnalytic
{
    /**
     * Id of roistat project.
     *
     * @var int
     */
    private $projectId;

    /**
     * Project api key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * CheckCompanyAnalytic constructor.
     *
     * @param $projectId
     * @param $apiKey
     */
    public function __construct($projectId, $apiKey)
    {
        $this->projectId = $projectId;
        $this->apiKey = $apiKey;
    }

    /**
     * Get company analytic.
     *
     * @param $period
     * @param $filters
     * @return bool|array
     */
    public function get($period, $filters)
    {
        $respond = $this->responseParser($this->sendRequest($period, $filters));

        if (false === $respond) {
            return false;
        }

        return $this->convertMetricsResult($respond['metrics']);
    }

    private function convertMetricsResult($respondPart)
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
     * @param $response
     * @return array|bool
     */
    private function responseParser($response)
    {
        if (empty($response) || empty($response['status'])) {
            Log::warning(
                'Roistat request response is empty',
                ['project_id' => $this->projectId, 'api_key' => $this->apiKey, 'response' => $response]
            );

            return false;
        }

        if ($response['status'] !== 'success') {
            Log::warning(
                'Roistat request is not successfuly finished',
                ['project_id' => $this->projectId, 'api_key' => $this->apiKey, 'response' => $response]
            );

            return false;
        }

        if (! isset($response['data'])) {
            Log::warning(
                'Roistat request response is not valid',
                ['project_id' => $this->projectId, 'api_key' => $this->apiKey, 'response' => $response]
            );

            return false;
        }

        return $response['data'][0]['mean'];
    }

    /**
     * Send data to the server.
     *
     * @param $period
     * @param $filters
     * @return mixed
     */
    private function sendRequest($period, $filters)
    {
        $query = "https://cloud.roistat.com/api/v1/project/analytics/data?project={$this->projectId}&key={$this->apiKey}&is_new=1";

        $data = [
            'dimensions' => ['marker_level_1'],
            'metrics' => ['visitCount', 'visits2leads', 'leadCount', 'visitsCost', 'costPerClick', 'costPerLead'],
            'period' => [
                'from' => $period['from'],
                'to' => $period['to'],
            ],
        ];

        if (! empty($filters)) {
            $data['filters'][] = [
                'field' => 'marker_level_1',
                'operation' => 'in',
                'value' => $filters,
            ];
        }

        return Curl::to($query)
            ->withData($data)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
