<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.10.2017
 * Time: 12:08.
 */

namespace App\Domain\Roistat;

class RoistatProjectAnalyticsData extends RoistatApi
{
    protected $from = '';
    protected $to = '';

    protected $filters = [];

    public function __construct($projectId, $apiKey)
    {
        $this->setRoistatRequest(
            'project',
            'analytics',
            'data',
            '',
            [
                'is_new' => '1',
            ]
        );

        $yesterday = strtotime('-1 day');
        $this->from = date('Y-m-d', $yesterday).'T00:00:00'.'+0200';
        $this->to = date('Y-m-d', $yesterday).'T23:59:59'.'+0200';

        parent::__construct($projectId, $apiKey);
    }

    /**
     * Simple set dimensions values.
     *
     * @param $values
     * @return $this
     */
    public function setDimensionsValues($values)
    {
        $this->filters[] = [
            'field' => 'marker_level_1',
            'operation' => 'in',
            'value' => $values,
        ];

        return $this;
    }

    /**
     * Set period.
     *
     * @param $from
     * @param $to
     * @return RoistatProjectAnalyticsData
     */
    public function setPeriod($from, $to)
    {
        $this->from = $from;
        $this->to = $to;

        return $this;
    }

    /**
     * Get data that we need to send.
     *
     * @return mixed
     */
    protected function getData()
    {
        $data = [];

        $data = [
            'dimensions' => ['marker_level_1'],
            'metrics' => ['visitCount', 'visits2leads', 'leadCount', 'visitsCost', 'costPerClick', 'costPerLead'],
            'period' => [
                'from' => $this->from,
                'to' => $this->to,
            ],
        ];

        if (! empty($this->filters)) {
            $data['filters'] = $this->filters;
        }

        return $data;
    }
}
