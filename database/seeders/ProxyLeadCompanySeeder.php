<?php

namespace Database\Seeders;

use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\Finance\Models\TotalCompanyCost;
use App\Domain\ProxyLead\Models\PlApprovedReport;
use App\Domain\ProxyLead\Models\PlEmailRecipients;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\Roistat\Models\RoistatAnalytic;
use App\Domain\Roistat\Models\RoistatCompanyConfig;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProxyLeadCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channelId = $this->getChannelId();

        $company = create(Company::class, ['channel_id' => $channelId]);

        $proxyLeadSetting = create(ProxyLeadSetting::class, ['company_id' => $company->id]);

        $this->createRecipients($proxyLeadSetting);

        $startPeriod = now()->subMonth(6)->startOfMonth();

        $this->createLeads($startPeriod, $proxyLeadSetting);

        $this->approveSomePeriods($startPeriod, $proxyLeadSetting);

        $this->addTotalCompanyCosts($startPeriod, $company);

        $roistatConfig = create(RoistatCompanyConfig::class, ['company_id' => $company->id]);

        $this->addRoistatAnalytic($startPeriod, $roistatConfig);
    }

    private function addRoistatAnalytic(Carbon $startPeriod, RoistatCompanyConfig $roistatCompanyConfig)
    {
        $endPeriod = now()->startOfDay();
        $iteratorPeriod = clone $startPeriod;

        while ($iteratorPeriod->lte($endPeriod)) {
            create(
                RoistatAnalytic::class,
                ['roistat_company_config_id' => $roistatCompanyConfig->id, 'for_date' => $iteratorPeriod->toDateString()]
            );

            $iteratorPeriod->addDay();
        }
    }

    private function getChannelId(): int
    {
        static $channelId = 0;

        if ($channelId > 0) {
            return $channelId;
        }

        $channel = create(Channel::class);

        $channelId = $channel->id;

        return $channelId;
    }

    /**
     * @param Carbon $startPeriod
     * @param $endPeriod
     * @param $proxyLeadSetting
     */
    private function createLeads(Carbon $startPeriod, ProxyLeadSetting $proxyLeadSetting): void
    {
        $endPeriod = now();
        $iteratorPeriod = clone $startPeriod;

        ProxyLead::flushEventListeners();

        while ($iteratorPeriod->lte($endPeriod)) {
            $cratedAtDate = (clone $iteratorPeriod)->addDay(random_int(0, (clone $iteratorPeriod)->endOfMonth()->day));

            create(
                ProxyLead::class,
                [
                    'proxy_lead_setting_id' => $proxyLeadSetting->id,
                    'created_at' => $cratedAtDate,
                    'updated_at' => $cratedAtDate,
                ],
                5
            )->each(function (ProxyLead $proxyLead) use ($cratedAtDate) {
                create(
                    PlReportLead::class,
                    ['proxy_lead_id' => $proxyLead->id, 'created_at' => $cratedAtDate, 'updated_at' => $cratedAtDate]
                );
            });

            $iteratorPeriod->addDay();
        }
    }

    /**
     * @param $startPeriod
     * @param $endPeriod
     * @param $proxyLeadSetting
     */
    private function approveSomePeriods(Carbon $startPeriod, ProxyLeadSetting $proxyLeadSetting): void
    {
        $endPeriod = now()->startOfMonth()->subMonth(2);
        $iteratorPeriod = clone $startPeriod;

        while ($iteratorPeriod->lte($endPeriod)) {
            create(
                PlApprovedReport::class,
                ['proxy_lead_setting_id' => $proxyLeadSetting->id, 'for_date' => $iteratorPeriod->toDateString()]
            );

            $iteratorPeriod->addMonth();
        }
    }

    private function createRecipients(ProxyLeadSetting $proxyLeadSetting): void
    {
        create(PlEmailRecipients::class, ['proxy_lead_setting_id' => $proxyLeadSetting, 'type' => PlEmailRecipients::TYPE_ADMIN], 2);
        create(PlEmailRecipients::class, ['proxy_lead_setting_id' => $proxyLeadSetting, 'type' => PlEmailRecipients::TYPE_RECEIVER], 2);
    }

    /**
     * @param $startPeriod
     * @param $endPeriod
     * @param $company
     */
    private function addTotalCompanyCosts(Carbon $startPeriod, Company $company): void
    {
        $endPeriod = now();
        $iteratorPeriod = clone $startPeriod;

        while ($iteratorPeriod->lte($endPeriod)) {
            $forDate = $iteratorPeriod->toDateString();

            create(
                TotalCompanyCost::class,
                ['company_id' => $company->id, 'created_at' => $forDate, 'updated_at' => $forDate]
            );

            $iteratorPeriod->addDay();
        }
    }
}
