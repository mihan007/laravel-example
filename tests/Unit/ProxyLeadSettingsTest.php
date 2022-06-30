<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\Models\PlEmailRecipients;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use Tests\TestCase;

class ProxyLeadSettingsTest extends TestCase
{
    /** @test */
    public function it_has_proxy_leads()
    {
        $proxyLeadSettings = ProxyLeadSetting::factory()->create();
        $lead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $proxyLeadSettings->id]);

        $this->assertTrue($proxyLeadSettings->proxyLeads()->get()->contains($lead));
    }

    /** @test */
    public function it_has_many_email_recipients()
    {
        $proxyLeadSettings = ProxyLeadSetting::factory()->create();

        $recipient1 = PlEmailRecipients::factory()->create(['proxy_lead_setting_id' => $proxyLeadSettings->id]);
        $recipient2 = PlEmailRecipients::factory()->create(['proxy_lead_setting_id' => $proxyLeadSettings->id]);

        $this->assertTrue($proxyLeadSettings->emailRecipients()->get()->contains($recipient1));
        $this->assertTrue($proxyLeadSettings->emailRecipients()->get()->contains($recipient2));
    }

    /** @test */
    public function it_belongs_to_company()
    {
        $company = Company::factory()->create();
        $proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($proxyLeadSetting->company()->get()->contains($company));
    }
}
