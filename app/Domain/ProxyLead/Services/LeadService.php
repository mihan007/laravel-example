<?php

namespace App\Domain\ProxyLead\Services;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Balance;
use App\Domain\ProxyLead\BalanceNotifier;
use App\Domain\ProxyLead\DuplicateChecker;
use App\Domain\ProxyLead\Events\CreateProxyLeadEvent;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class LeadService
{
    private const AD_PLATFORM_DEFAULT_LENGHT = 20;
    /**
     * @var \App\Domain\ProxyLead\Models\ProxyLeadSetting
     */
    private $proxyLeadSettings;

    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var string
     */
    private $name = '';
    /**
     * @var mixed
     */
    private $rawContent = '';
    /**
     * @var string
     */
    private $advertisingPlatform = '';
    /**
     * @var string
     */
    private $comment = '';
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var string
     */
    private $serviceId = '';
    private $leadPrice = null;

    public function initWithCompanyId($companyId): self
    {
        $this->company = Company::findOrFail($companyId);
        $this->proxyLeadSettings = $this->company->proxyLeadSettings;

        return $this;
    }

    public function initWithPublicKey($key): self
    {
        $this->proxyLeadSettings = ProxyLeadSetting::where('public_key', $key)->firstOrFail();
        $this->company = $this->proxyLeadSettings->company;

        return $this;
    }

    public function setPhone($rawPhone): self
    {
        $this->phone = $rawPhone;

        return $this;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setAdvertisingPlatform($advertisingPlatform): self
    {
        $this->advertisingPlatform = $advertisingPlatform;

        return $this;
    }

    public function setRawContent($rawContent): self
    {
        $this->rawContent = $rawContent;

        return $this;
    }

    public function setLeadPrice($leadPrice): self
    {
        $this->leadPrice = $leadPrice;

        return $this;
    }

    public function setComment($comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setServiceId($serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function create(): void
    {
        if (!$this->proxyLeadSettings) {
            throw new \RuntimeException('ProxyLeadSettings is missing. Could not create proxy lead');
        }
        try {
            $proxyLeadData = $this->buildProxyLeadData();
            /** @var \App\Domain\ProxyLead\Models\ProxyLead $proxyLead */
            $proxyLead = $this->proxyLeadSettings->proxyLeads()->create($proxyLeadData);
            $hasDuplicates = (new DuplicateChecker($proxyLead))->check();
            new Balance($this->company, $proxyLead, $hasDuplicates, $this->leadPrice);
            new BalanceNotifier($this->company);
            event(new CreateProxyLeadEvent($this->proxyLeadSettings, $proxyLead));
            logger()->info('Saved proxy lead from knam #' . $proxyLead->id, $this->rawContent);
        } catch (\Exception $e) {
            logger()->error('Error saving proxy lead: ' . $e->getMessage(), $this->rawContent);
            throw new \RuntimeException('Could not create proxy lead. Error: ' . $e->getMessage());
        }
    }

    public function createOrSkip(): void
    {
        if (!$this->proxyLeadSettings) {
            throw new \RuntimeException('ProxyLeadSettings is missing. Could not create proxy lead');
        }
        $isUnique = true;
        if ($this->advertisingPlatform && $this->serviceId) {
            $isUnique = $this->checkIfProxyLeadUnique();
        }
        if (!$isUnique) {
            logger()->info(
                "Skip non unique lead with advertisingPlatform={$this->advertisingPlatform} and serviceId={$this->serviceId}"
            );

            return;
        }
        $this->create();
    }

    /**
     * @throws \Exception
     */
    private function buildProxyLeadData(): array
    {
        $data = [
            'title' => $this->buildTitle(),
            'phone' => $this->phone,
            'name' => $this->name,
            'comment' => $this->buildComment(),
            'advertising_platform' => $this->buildAdvertisingPlatform(),
            'extra' => is_string($this->rawContent) ? $this->rawContent : json_encode($this->rawContent),
            'is_free' => $this->company->free_period ? 1 : 0,
            'service_id' => $this->buildUniqueServiceId(),
        ];

        $validateResult = $this->validate($data);
        if ($validateResult !== true) {
            throw new \Exception('Proxy lead missing important data:' . json_encode($validateResult));
        }

        return $data;
    }

    private function buildTitle(): string
    {
        return $this->title;
    }

    private function buildComment(): string
    {
        return $this->comment;
    }

    private function buildAdvertisingPlatform(): string
    {
        return $this->advertisingPlatform;
    }

    private function validate(array $data)
    {
        /** @var Validator $validator */
        $validator = Validator::make(
            $data,
            [
                'phone' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $validator->messages()->get('*');
        }

        return true;
    }

    private function checkIfProxyLeadUnique(): bool
    {
        return !ProxyLead::where(
            [
                'proxy_lead_setting_id' => $this->proxyLeadSettings->id,
                'service_id' => $this->buildUniqueServiceId(),
            ]
        )->exists();
    }

    /**
     * @return string
     */
    private function buildUniqueServiceId(): string
    {
        return substr($this->advertisingPlatform, 0, self::AD_PLATFORM_DEFAULT_LENGHT) . '_' . $this->serviceId;
    }

    /**
     * Возьмем из настроек компании правила матичнга и заменим title, phone, info.
     *
     * @param \App\Domain\ProxyLead\Models\ProxyLeadSetting $proxyLeadSetting
     * @param $data
     */
    public function autoMatchLeadData(ProxyLeadSetting $proxyLeadSetting, $data)
    {
        foreach ($data as $param_name => $param_value) {
            $normalizedKey = mb_strtolower($param_name);
            $result[$normalizedKey] = $param_value;
        }
        $data = $result;
        foreach ($data as $param_name => $param_value) {
            $matchNames = json_decode($proxyLeadSetting->match_name, 1);
            list($matchedNames, $data) = $this->tryMapParam($matchNames, $data);
            if (count($matchedNames)) {
                if (!empty($data['name'])) {
                    $result['name'] .= "\n" . trim(implode(PHP_EOL, $matchedNames));
                } else {
                    $result['name'] = trim(implode(PHP_EOL, $matchedNames));
                }
            }
            $matchPhones = json_decode($proxyLeadSetting->match_phone, 1);
            list($matchedPhones, $data) = $this->tryMapParam($matchPhones, $data);
            if (count($matchedPhones)) {
                if (!empty($data['phone'])) {
                    $result['phone'] .= "\n" . trim(implode(PHP_EOL, $matchedPhones));
                } else {
                    $result['phone'] = trim(implode(PHP_EOL, $matchedPhones));
                }
            }

            $matchInfos = json_decode($proxyLeadSetting->match_info, 1);
            list($matchedInfos, $data) = $this->tryMapParam($matchInfos, $data);
            if (count($matchedInfos)) {
                if (!empty($data['comment'])) {
                    $result['comment'] .= "\n" . trim(implode(PHP_EOL, $matchedInfos));
                } else {
                    $result['comment'] = trim(implode(PHP_EOL, $matchedInfos));
                }
            }
        }

        if (isset($result['phone'])) {
            $result['phone'] = trim($result['phone']);
        }
        if (isset($result['comment'])) {
            $result['comment'] = trim($result['comment']);
        }
        if (isset($result['name'])) {
            $result['name'] = trim($result['name']);
        }

        return $result;
    }

    /**
     * @param $matchParams
     * @param $data
     * @return array
     */
    private function tryMapParam($matchParams, $data): array
    {
        $matchedParams = [];
        if (!is_iterable($matchParams)) {
            return array($matchedParams, $data);
        }
        foreach ($matchParams as $paramMap) {
            try {
                $paramMap = mb_strtolower($paramMap);
                $mappedParam = Arr::get($data, $paramMap);
                if ($mappedParam) {
                    Arr::forget($data, $paramMap);
                    $matchedParams[] = $mappedParam;
                }
            } catch (\Exception $exception) {
                //do nothing, trying map next param
            }
        }
        return array($matchedParams, $data);
    }
}
