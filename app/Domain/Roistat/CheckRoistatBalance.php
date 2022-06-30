<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 18.09.2017
 * Time: 15:36.
 */

namespace App\Domain\Roistat;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Roistat\Models\RcBalanceConfig;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class CheckRoistatBalance
{
    public function __construct()
    {
    }

    public function check()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $roistatConfig = $company->roistatConfig()->first();

            if (empty($roistatConfig)) {
                continue;
            }

            // there is no error, just not set roistat settings
            if (empty($roistatConfig->roistat_project_id) || empty($roistatConfig->api_key)) {
                continue;
            }

            $roistatBalanceConfig = $company->roistatBalanceConfig()->first();

            if (empty($roistatBalanceConfig)) {
                continue;
            }

            $this->addNewTransactions($roistatConfig, $roistatBalanceConfig, $company);
        }

        return true;
    }

    /**
     * Add new transaction information into database.
     *
     * @param RoistatCompanyConfig $config
     * @param \App\Domain\Roistat\Models\RcBalanceConfig $configBalance
     * @return bool
     */
    protected function addNewTransactions(
        RoistatCompanyConfig $config,
        RcBalanceConfig $configBalance,
        Company $company
    ) {
        $projectId = $config->roistat_project_id;
        $apiKey = $config->api_key;

        $transactions = $this->getTransactions($projectId, $apiKey);

        if (false === $transactions) {
            return false;
        }

        if (empty($transactions)) {
            return true;
        }

        $yesterdayDate = Carbon::yesterday();

        foreach ($transactions as $transaction) {
            $transactionDate = new Carbon($transaction['date']);

            if ($transactionDate < $yesterdayDate) {
                continue;
            }

            $transaction['date'] = $transactionDate->toDateTimeString();
            $transaction['virtual_balance'] = null === $transaction['virtual_balance'] ? 0 : $transaction['virtual_balance'];

            $configBalance->transactions()->create($transaction);
            if ($transaction['balance'] > $company->roistatBalanceConfig->limit_amount) {
                $company->clearEmailNotificationLastSend(EmailNotification::ROISTAT_BALANCE_TYPE);
            }
        }

        return true;
    }

    /**
     * Get transaction information.
     *
     * @param $projectId
     * @param $apiKey
     * @return mixed
     */
    protected function getTransactions($projectId, $apiKey)
    {
        $apiParams = [];
        $apiParams['from'] = Carbon::yesterday()->toAtomString();

        $response = $this->getTransactionsFromRoistat($projectId, $apiKey, $apiParams);

        if (empty($response) || empty($response['status'])) {
            Log::warning('Roistat request response is empty', ['projectId' => $projectId, 'response' => $response]);

            return false;
        }

        if ($response['status'] !== 'success') {
            Log::warning(
                'Roistat request is not successfuly finished',
                ['projectId' => $projectId, 'response' => $response]
            );

            return false;
        }

        if (! isset($response['data'])) {
            Log::warning('Roistat request response is not valid', ['projectId' => $projectId, 'response' => $response]);

            return false;
        }

        return $response['data'];
    }

    /**
     * Get information about transactions from roistat.
     *
     * @param $projectId
     * @param $apiKey
     * @param $params
     * @return mixed
     */
    protected function getTransactionsFromRoistat($projectId, $apiKey, $params)
    {
        $query = "https://cloud.roistat.com/api/v1/user/billing/transactions/list?project=$projectId&key=$apiKey";

        return Curl::to($query)
            ->withData($params)
            ->withContentType('application/json')
            ->asJson(true)
            ->post();
    }
}
