<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.10.2017
 * Time: 9:58.
 */

namespace App\Domain\Roistat;

use App\Domain\Roistat\Exception\EmptyApiKeyException;
use App\Domain\Roistat\Exception\EmptyProjectIdException;
use App\Domain\Roistat\Exception\EmptyRequestParamException;
use Ixudra\Curl\Facades\Curl;

abstract class RoistatApi
{
    /**
     * Set project id in roistat.
     *
     * @var int
     */
    protected $projectId;

    /**
     * Set api key in roistat.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Set name of the request context.
     *
     * @var string
     */
    protected $context = '';

    /**
     * Set name of the request service.
     *
     * @var string
     */
    protected $service = '';

    /**
     * Set name of the request resource.
     *
     * @var string
     */
    protected $resource = '';

    /**
     * Set name of the request method.
     *
     * @var string
     */
    protected $method = '';

    /**
     * Set additional url data.
     *
     * @var array
     */
    protected $additionalUrlData = [];

    /**
     * RoistatApi constructor.
     *
     * @param $projectId
     * @param $apiKey
     * @throws EmptyApiKeyException
     * @throws EmptyProjectIdException
     * @throws EmptyRequestParamException
     */
    public function __construct($projectId, $apiKey)
    {
        if (empty($projectId)) {
            throw new EmptyProjectIdException('Take empty project id in RoistatApi constructor');
        }

        $this->projectId = $projectId;

        if (empty($apiKey)) {
            throw new EmptyApiKeyException('Take empty api key in RoistatApi constructor');
        }

        $this->apiKey = $apiKey;

        if (empty($this->context)) {
            throw new EmptyRequestParamException('Context must be not empty');
        }

        if (empty($this->service)) {
            throw new EmptyRequestParamException('Service must be not empty');
        }
    }

    /**
     * Get information from roistat.
     *
     * @return mixed
     */
    public function get()
    {
        $url = $this->getUrl();
        $data = $this->getData();

        return $this->send($url, $data);
    }

    /**
     * Get data that we need to send.
     *
     * @return mixed
     */
    abstract protected function getData();

    /**
     * Set parameters of request method.
     *
     * @param $context
     * @param $service
     * @param $resource
     * @param $method
     * @param array $additional
     * @return mixed
     */
    protected function setRoistatRequest($context, $service, $resource, $method, $additional = [])
    {
        $this->context = $context;
        $this->service = $service;
        $this->resource = $resource;
        $this->method = $method;
        $this->additionalUrlData = $additional;

        return true;
    }

    /**
     * Create url by params.
     *
     * @return string
     */
    protected function getUrl()
    {
        $url = 'https://cloud.roistat.com/api/v1';

        $contextExists = ! empty($this->context);
        $serviceExists = ! empty($this->service);
        $resourceExists = ! empty($this->resource);
        $methodExists = ! empty($this->method);
        $additionalUrlDataExists = ! empty($this->additionalUrlData);

        if ($contextExists) {
            $url .= '/'.$this->context;
        }

        if ($contextExists && $serviceExists) {
            $url .= '/'.$this->service;
        }

        if ($contextExists && $serviceExists && $resourceExists) {
            $url .= '/'.$this->resource;
        }

        if ($contextExists && $serviceExists && $resourceExists && $methodExists) {
            $url .= '/'.$this->method;
        }

        $url .= '?key='.$this->apiKey;
        $url .= '&project='.$this->projectId;

        if ($additionalUrlDataExists) {
            foreach ($this->additionalUrlData as $key => $data) {
                $url .= "&$key=$data";
            }
        }

        return $url;
    }

    /**
     * Sending data ro roistat.
     *
     * @param $url
     * @param $data
     * @return mixed
     */
    protected function send($url, $data)
    {
        return Curl::to($url)
            ->withData($data)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
