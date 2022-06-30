<?php
/**
 * For local.troiza.net.
 * User: ttt
 * Date: 12.06.2019
 * Time: 16:00.
 */

namespace App\Api\ProxyLead\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Mail\ZadarmaError;
use App\Domain\ProxyLead\Balance;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\ProxyLead\DuplicateChecker;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Events\WrongProxyLeadPayloadEvent;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Exception;
use Illuminate\Http\Request;
use Mail;
use Zadarma_API\Client;

class WebHookControllerCommon extends WebHookController
{
    protected $zadarmaStatusTranslate = [
        'answered' => 'ANSWER',
        'busy' => 'BUSY',
        'cancel' => 'CANCEL',
        'no answer' => 'NOANSWER',
        'failed' => 'FAILED',
        'no money' => 'NOMONEY',
        'unallocated number' => 'UNALLOCATED_NUMBER',
        'no limit' => 'NO_LIMIT',
        'no day limit' => 'NO_DAY_LIMIT',
        'line limit' => 'LINE_LIMIT',
        'no money, no limit' => 'NO_MONEY_NO_LIMIT',
    ];
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSettings;

    public function store(Request $request)
    {
        if ($request->has('zd_echo')) {
            return response($request->get('zd_echo'));
        }

        $key = $request->key ?? $request->api_key;
        if (empty($key)) {
            abort(400);
        }

        $this->proxyLeadSettings = ProxyLeadSetting::where('public_key', $key)->first();
        if (!$this->proxyLeadSettings || !$this->proxyLeadSettings->company) {
            abort(404);
        }

        logger()->info("New web-hook common key={$key};", $request->all());

        if (($response = $this->zadarma($request, $key)) !== false) {
            return $response;
        }

        if (($response = $this->mrqz($request, $key)) !== false) {
            return $response;
        }

        if (($response = $this->gudok($request, $key)) !== false) {
            return $response;
        }

        $requestData = $this->getWebhookJsonPayload($request);
        if (($requestData !== false) && $this->isTestFromRoistat($requestData)) {
            return $this->storeTestRoistatWebhook($requestData);
        }

        return parent::store($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function zadarma(Request $request, $key)
    {
        $company = $this->proxyLeadSettings->company;
        $zadarmaConfig = $company->zadarmaConfig;

        if (! $request->has('event')) {
            return false;
        }

        $event = $request->get('event');
        $zadarmaEvents = [
            'NOTIFY_START',
            'NOTIFY_OUT_START',
            'NOTIFY_INTERNAL',
            'NOTIFY_ANSWER',
            'NOTIFY_END',
            'NOTIFY_RECORD',
        ];
        if (in_array($event, $zadarmaEvents) && ! $zadarmaConfig) {
            $this->notifyAboutZadaramaIssue($company, 'Конфигурация Zadarma для компании некорректна');
            throw new Exception('Module: zadarma not configured');
        }

        switch ($event) {
            case 'NOTIFY_START':
            case 'NOTIFY_OUT_START':
                /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSettings */
                $company = $this->proxyLeadSettings->company;
                /** @var ProxyLead $proxyLead */
                $proxyLead = $this->proxyLeadSettings->proxyLeads()->create(
                    array_merge(
                        [
                            'title' => 'Звонок',
                            'phone' => $request->get('phone'),
                            'is_free' => $company->free_period,
                        ],
                        $this->zadarmaTransformData($request)
                    )
                );
                $proxyLead->restore();
                $hasDuplicates = (new DuplicateChecker($proxyLead))->check();
                if ($company) {
                    new \App\Domain\ProxyLead\Balance($company, $proxyLead, $hasDuplicates);
                    new BalanceNotifier($company);
                }

                return response()->json(
                    [
                        'status' => 'success',
                        'data' => $proxyLead,
                    ]
                );
                break;

            case 'NOTIFY_INTERNAL':
            case 'NOTIFY_ANSWER':
                $data = $this->zadarmaTransformData($request);
                $this->updateProxyLead(
                    $key,
                    ['service_id' => $data['service_id'] ?? $request->get('service_id')],
                    $data
                );
                break;

            case 'NOTIFY_END':
                $data = $this->zadarmaTransformData($request);
                $proxyLead = $this->updateProxyLead(
                    $key,
                    ['service_id' => $data['service_id'] ?? $request->get('service_id')],
                    $data
                );
                if (! $proxyLead) {
                    break;
                }
                (new DuplicateChecker($proxyLead))->check();
                if (array_key_exists(
                        'call_id_with_rec',
                        $data['extra']
                    ) && (((int) $data['extra']['is_recorded']) > 0)) {
                    try {
                        $linkRecord = $this->zadarmaGetRecordLink(
                            $proxyLead->proxyLeadSetting,
                            $data['extra']['pbx_call_id'],
                            $data['extra']['call_id_with_rec']
                        );
                        $this->updateProxyLead(
                            $key,
                            ['service_id' => $data['service_id'] ?? $request->get('service_id')],
                            ['extra' => ['link' => $linkRecord]]
                        );
                    } catch (Exception $e) {
                        logger()->error($e->getMessage());
                    }
                }

                event(new CreateProxyLeadEvent($this->proxyLeadSettings, $proxyLead));

                return response()->json(
                    [
                        'status' => 'success',
                        'data' => $proxyLead,
                    ]
                );
                break;

            case 'NOTIFY_RECORD':
                $data = $this->zadarmaTransformData($request);
                $link = $this->zadarmaGetRecordLink(
                    $this->proxyLeadSettings,
                    $data['extra']['pbx_call_id'],
                    $data['extra']['call_id_with_rec']
                );

                $this->updateProxyLead(
                    $key,
                    ['service_id' => $data['service_id'] ?? $request->get('service_id')],
                    ['extra' => ['link' => $link]]
                );
                break;
            default:
                return false;
        }
    }

    protected function updateProxyLead($key, $where, $data = [])
    {
        /** @var ProxyLead $proxyLead */
        $proxyLead = $this->proxyLeadSettings->proxyLeads()->where($where)->orderBy('id', 'desc')->first();
        if (! $proxyLead) {
            return false;
        }

        collect($data)->each(
            function ($item, $key) use ($proxyLead) {
                if ($key !== 'extra') {
                    $proxyLead->{$key} = $item;
                } else {
                    $extra = $proxyLead->extra;
                    $proxyLead->extra = array_merge($extra, $item);
                }
            }
        );

        $proxyLead->save();

        return $proxyLead;
    }

    protected function zadarmaTransformData(Request $request)
    {
        $data = ['extra' => $request->merge(['source' => 'zadarma-web-hook'])->all()];
        $map = [
            'caller_id' => 'phone',
            'pbx_call_id' => 'service_id',
        ];
        foreach ($map as $source => $translated) {
            if (array_key_exists($source, $data['extra'])) {
                $data[$translated] = $data['extra'][$source];
            }
        }

        if (array_key_exists('disposition', $data['extra'])) {
            $data['extra']['status'] = $this->zadarmaStatusTranslate[$data['extra']['disposition']];
        }

        return $data;
    }

    protected function zadarmaGetRecordLink(ProxyLeadSetting $proxyLeadSettings, $pbx_call_id, $call_id_with_rec)
    {
        $company = $proxyLeadSettings->company;
        $zadarmaConfig = $company->zadarmaConfig;

        if (! $company || ! $zadarmaConfig) {
            $this->notifyAboutZadaramaIssue($company, 'Конфигурация Zadarma для компании некорректна');
            throw new Exception('Module: zadarma not configured');
        }

        $zd = new Client($zadarmaConfig->key, $zadarmaConfig->secret);
        $result = $zd->call(
            '/v1/pbx/record/request/',
            [
                'call_id' => $call_id_with_rec,
                'pbx_call_id' => $pbx_call_id,
                'lifetime' => 5184000 / 2,
            ]
        );

        $result = json_decode($result);
        if ($result->status === 'success') {
            $link = $result->links[0];
        } else {
            $this->notifyAboutZadaramaIssue($company, 'Не смогли получить ссылку на запись разговора. Ошибка: '.json_encode($result));
            throw new Exception(
                "Module: zadarma, cannot get link record for {$company->name}, message: ".$result->message
            );
        }

        return $link;
    }

    private function mrqz(Request $request, $key)
    {
        $proxyLeadData = $this->collectDataFromMrqzRequest($request);
        if ($proxyLeadData === false) {
            return false;
        }
        try {
            $company = $this->proxyLeadSettings->company;
            $proxyLeadData['is_free'] = ($company) ? $company->free_period : false;
            /** @var ProxyLead $proxyLead */
            $proxyLead = $this->proxyLeadSettings->proxyLeads()->create($proxyLeadData);
            $hasDuplicates = (new DuplicateChecker($proxyLead))->check();
            if ($company) {
                new \App\Domain\ProxyLead\Balance($company, $proxyLead, $hasDuplicates);
                new BalanceNotifier($company);
            }

            event(new CreateProxyLeadEvent($this->proxyLeadSettings, $proxyLead));
        } catch (Exception $e) {
            $this->handleUnexpectedMrqzResult($request);

            return response()->json('', 200);
        }

        return response()->json('', 200);
    }

    private function collectDataFromMrqzRequest(Request $request)
    {
        $requestData = $this->getWebhookJsonPayload($request);
        if ($requestData === false) {
            return false;
        }
        if ($this->isItRoistat($requestData)) {
            //it is roistat
            return false;
        }
        if (! $this->checkIfValidMrqzPayload($requestData)) {
            return false;
        }

        return [
            'title' => $requestData['quiz']['name'],
            'phone' => $requestData['contacts']['phone'],
            'name' => $requestData['contacts']['name'],
            'comment' => $this->buildCommentFromMrqzPayload($requestData),
            'advertising_platform' => $requestData['extra']['href'],
            'extra' => $requestData
        ];
    }

    private function checkIfValidMrqzPayload(array $requestData)
    {
        $isValidAnswers = isset($requestData['answers']);
        $isValidContactsName = $isValidAnswers && isset($requestData['contacts']['name']);
        $isValidContactsPhone = $isValidContactsName && isset($requestData['contacts']['phone']);
        $isValidQuizName = $isValidContactsPhone && isset($requestData['quiz']['name']);

        return $isValidQuizName && isset($requestData['extra']['href']);
    }

    private function checkIfValidGudokPayload(array $requestData)
    {
        $isValidStatus = isset($requestData['callstatus']);
        $isValidBillSec = $isValidStatus && isset($requestData['billsec']);
        $isValidContactsPhone = $isValidBillSec && isset($requestData['src']);

        return $isValidContactsPhone;
    }

    /**
     * @param array $requestData
     * @return string
     */
    private function buildCommentFromMrqzPayload(array $requestData)
    {
        $result = [];
        foreach ($requestData['answers'] as $qa) {
            $question = $qa['q'];
            $answers = $qa['a'];
            $result[] = "Вопрос: $question";
            if (is_array($answers)) {
                $result[] = 'Ответы: '.implode(', ', $answers);
            } else {
                $result[] = "Ответ: $answers";
            }
            $result[] = '';
        }

        if (isset($requestData['extra']['discount'])) {
            $result[] = "Скидка: {$requestData['extra']['discount']}";
        }

        $name = $requestData['contacts']['name'] ?? '';
        $result[] = $this->makeHumanReadableField('Имя клиента', $name);

        $phone = $requestData['contacts']['phone'] ?? '';
        $result[] = $this->makeHumanReadableField('Телефон клиента', $phone);

        $emailValue = $requestData['contacts']['email'] ?? '';
        $result[] = $this->makeHumanReadableField('Email клиента', $emailValue);

        return implode(PHP_EOL, $result);
    }

    private function makeHumanReadableField($fieldName, $fieldValue)
    {
        $fieldValue = trim($fieldValue);
        $value = strlen($fieldValue) > 0 ? $fieldValue : 'не указан';

        return "$fieldName: $value";
    }

    /**
     * @param Request $request
     */
    private function handleUnexpectedMrqzResult(Request $request): void
    {
        $company = $this->proxyLeadSettings->company;

        /** @var ProxyLead $proxyLead */
        $proxyLead = $this->proxyLeadSettings->proxyLeads()->create(
            [
                'title' => 'http://mrqz.me',
                'phone' => 'не определен',
                'deleted_at' => date('Y-m-d H:i:s'),
                'extra' => json_decode($request->getContent(), true),
                'is_free' => $company->free_period,
            ]
        );
        event(new WrongProxyLeadPayloadEvent($this->proxyLeadSettings, $proxyLead));
    }

    private function gudok(Request $request, $key)
    {
        $proxyLeadData = $this->collectDataFromGudokRequest($request);
        if ($proxyLeadData === false) {
            return false;
        }
        try {
            $company = $this->proxyLeadSettings->company;
            $proxyLeadData['is_free'] = ($company) ? $company->free_period : false;
            /** @var ProxyLead $proxyLead */
            $proxyLead = $this->proxyLeadSettings->proxyLeads()->create($proxyLeadData);
            $hasDuplicates = (new DuplicateChecker($proxyLead))->check();
            if ($company) {
                new Balance($company, $proxyLead, $hasDuplicates);
                new BalanceNotifier($company);
            }

            event(new CreateProxyLeadEvent($this->proxyLeadSettings, $proxyLead));
        } catch (Exception $e) {
            $this->handleUnexpectedGudokResult($request, $e->getMessage());

            return response()->json('', 200);
        }

        return response()->json('', 200);
    }

    private function collectDataFromGudokRequest(Request $request)
    {
        $requestData = $request->toArray();
        if ($requestData === false) {
            return false;
        }
        if ($this->isItRoistat($requestData)) {
            //it is roistat
            return false;
        }
        if (! $this->checkIfValidGudokPayload($requestData)) {
            return false;
        }

        $extra = [];
        if ($requestData['audio']) {
            $extra['link'] = $requestData['audio'];
        }
        $extra['status'] = 'GOODOK_'.$requestData['callstatus'];
        $extra['duration'] = $requestData['billsec'];

        return [
            'title' => 'Звонок',
            'phone' => $requestData['src'],
            'extra' => $extra,
        ];
    }

    /**
     * @param Request $request
     */
    private function handleUnexpectedGudokResult(Request $request, $errorMessage): void
    {
        $company = $this->proxyLeadSettings->company;

        /** @var ProxyLead $proxyLead */
        $proxyLead = $this->proxyLeadSettings->proxyLeads()->create(
            [
                'title' => 'https://gudok.tel',
                'phone' => 'не определен',
                'deleted_at' => date('Y-m-d H:i:s'),
                'extra' => json_decode($request->getContent(), true),
                'is_free' => $company->free_period,
                'comment' => $errorMessage,
            ]
        );
        event(new WrongProxyLeadPayloadEvent($this->proxyLeadSettings, $proxyLead));
    }

    /**
     * @param $requestData
     * @return bool
     */
    private function isItRoistat($requestData): bool
    {
        return isset($requestData['roistat'])
            || isset($requestData['city'])
            || isset($requestData['marker'])
            || $this->isTestFromRoistat($requestData);
    }

    private function isTestFromRoistat($requestData)
    {
        return (isset($requestData['marker']) && $requestData['marker'] === 'TEST_DATA')
            || (isset($requestData['callee']) && $requestData['callee'] === '74951234567');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getWebhookJsonPayload(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $requestData;
        }

        return false;
    }

    private function storeTestRoistatWebhook(array $requestData)
    {
        $caller = $requestData['caller'] ?? 'неизвестно';
        $callee = $requestData['callee'] ?? 'неизвестно';
        $testRoistatWebhookData = [
            'title' => 'Тестовый вебхук от Ройстата',
            'phone' => $caller,
            'name' => ProxyLead::TEST_ROISTAT_NAME,
            'comment' => "Ройстат сымитировал звонок с номера $caller на номера $callee",
            'advertising_platform' => 'http://help.roistat.com/pages/viewpage.action?pageId=4587904',
            'extra' => $requestData,
            'is_free' => 1,
        ];

        /** @var ProxyLead $proxyLead */
        $proxyLead = $this->proxyLeadSettings->proxyLeads()->create($testRoistatWebhookData);
        $proxyLead->reportLead->company_confirmed = PlReportLead::STATUS_DISAGREE;
        $proxyLead->reportLead->admin_confirmed = PlReportLead::STATUS_AGREE;
        $proxyLead->reportLead->reasons_of_rejection_id = ReasonsOfRejection::NOT_LEAD;
        $proxyLead->reportLead->admin_comment = $proxyLead->comment;
        $proxyLead->reportLead->company_comment = 'Заявка не подлежит обработке';
        $proxyLead->reportLead->save();

        return response()->json('', 200);
    }

    protected function notifyAboutZadaramaIssue(Company $company, $message)
    {
        $companyName = "{$company->name}({$company->id})";
        $accountAdmin = $company->account->admin;

        if ($accountAdmin){
            $data = [
                'companyName' => $companyName,
                'message' => $message,
            ];
            Mail::to($accountAdmin->email)->send(new ZadarmaError($data));
        }

    }
}
