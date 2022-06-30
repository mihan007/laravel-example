<?php

namespace Tests\Unit;

use App\Domain\Channel\Models\Channel;
use App\Domain\Channel\Models\ChannelReasonsOfRejection;
use App\Domain\Company\Models\Company;
use App\Domain\ProxyLead\DuplicateChecker;
use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Database\Seeders\ReasonsOfRejectionSeeder;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * Class DuplicateCheckerTest.
 */
class DuplicateCheckerTest extends TestCase
{
    /** @var \App\Domain\ProxyLead\Models\ReasonsOfRejection */
    protected $duplicateReason;
    /** @var \App\Domain\ProxyLead\Models\ProxyLeadSetting */
    protected $proxyLeadSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncate(PlReportLead::class, false);

        Bus::fake();
        $this->seed(ReasonsOfRejectionSeeder::class);
        $this->duplicateReason = ReasonsOfRejection::where('name', 'Дубль заявки')->first();
        $channel = Channel::factory()->create();
        ChannelReasonsOfRejection::factory()->create(['channel_id' => $channel->id, 'reasons_of_rejection_id' => $this->duplicateReason->id]);
        $company = Company::factory()->create(['channel_id' => $channel->id]);
        $this->proxyLeadSetting = ProxyLeadSetting::factory()->create(['company_id' => $company->id]);
    }

    /** @test */
    public function it_should_set_same_lead_as_duplicate(): void
    {
        ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSetting->id, 'phone' => '12345']);

        /** @var \App\Domain\ProxyLead\Models\ProxyLead $duplicateLead */
        $duplicateLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSetting->id, 'phone' => '12345']);
        $this->assertTrue($duplicateLead->is_target);

        (new DuplicateChecker($duplicateLead))->check();

        $this->assertTrue($duplicateLead->is_non_targeted);
    }

    /** @test */
    public function it_should_not_set_as_duplicate_if_it_is_first_lead_with_this_phone_number(): void
    {
        /** @var \App\Domain\ProxyLead\Models\ProxyLead $duplicateLead */
        $duplicateLead = ProxyLead::factory()->create(['proxy_lead_setting_id' => $this->proxyLeadSetting->id, 'phone' => '12345']);
        $this->assertTrue($duplicateLead->is_target);

        (new DuplicateChecker($duplicateLead))->check();

        $this->assertTrue($duplicateLead->is_target);
    }
}
