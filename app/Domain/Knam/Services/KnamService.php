<?php

namespace App\Domain\Knam\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;

class KnamService
{
    public const API_URL = 'https://panel.knam.pro';

    private const LOGIN = 'login';
    private const PHONES = 'phones';
    public const HTTP_OK = 200;

    private $authorizationToken;

    public function getPhonesByPeriod($startDate = null, $endDate = null): ?array
    {
        if (! $this->authorizationToken) {
            $this->authorizationToken = $this->getAuthorizationToken();
        }
        if ($startDate) {
            $startDate = Carbon::parse($startDate);
        } else {
            $startDate = $this->getDefaultStartDate();
        }
        if ($endDate) {
            $endDate = Carbon::parse($endDate);
        } else {
            $endDate = $this->getDefaultEndDate();
        }

        $result = $this->get(self::PHONES, [
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
        ]);

        return json_decode($result, true);
    }

    private function getAuthorizationToken()
    {
        if (! config('knam.login') || ! config('knam.password')) {
            throw new \Exception('Please setup knam login and password');
        }
        $tokenInformation = $this->post(self::LOGIN, [
            'username' => config('knam.login'),
            'password' => config('knam.password'),
        ]);

        return json_decode($tokenInformation, true)['access_token'];
    }

    private function post($url, $queryParams = [], $formParams = []): string
    {
        $headers = [];
        if ($url !== self::LOGIN) {
            $headers = $this->getHeaders();
        }

        try {
            $response = $this->getGuzzleClient()->request(
                'POST',
                '/api/'.$url,
                [
                    'headers' => $headers,
                    'query' => $queryParams,
                    'form_params' => $formParams,
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode !== self::HTTP_OK) {
                throw new \Exception("Knam POST $url. Wrong http code received: ".$statusCode);
            }

            return (string) $response->getBody();
        } catch (\Exception $e) {
            logger()->error('Knam service request error: '.$e->getMessage(), $e->getTrace());
            throw new \Exception('Knam service request error: '.$e->getMessage());
        }
    }

    private function get($url, $queryParams = []): ?string
    {
        $headers = [];
        if ($url !== self::LOGIN) {
            $headers = $this->getHeaders();
        }

        try {
            $response = $this->getGuzzleClient()->request(
                'GET',
                '/api/'.$url,
                [
                    'headers' => $headers,
                    'query' => $queryParams,
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode !== self::HTTP_OK) {
                throw new \Exception("Knam POST $url. Wrong http code received: ".$statusCode);
            }

            return (string) $response->getBody();
        } catch (\Exception $e) {
            logger()->error('Knam service request error: '.$e->getMessage(), $e->getTrace());

            return null;
        }
    }

    private function getGuzzleClient(): Client
    {
        return new Client([
            // Base URI is used with relative requests
            'base_uri' => self::API_URL,
            // You can set any number of default request options.
            'timeout' => 15.0,
        ]);
    }

    private function getDefaultStartDate(): Carbon
    {
        return Carbon::now()->startOfMonth();
    }

    private function getDefaultEndDate(): Carbon
    {
        return Carbon::now();
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '.$this->authorizationToken,
        ];
    }
}
