<?php

namespace Tests\Feature\Company\Report;

use App\Domain\Company\Models\Company;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
    }

    /** @test */
    public function it_should_return_error_if_roistat_company_config_is_not_set() :void
    {
        $this->signInAsSuperAdmin();

        $this->makeRequest()->assertStatus(302)->assertSessionHas('message');
    }

    private function makeRequest(): TestResponse
    {
        return $this->get(route('account.company.report.index', [
            'id' => $this->company->id,
            'accountId' => $this->company->account_id,
        ]));
    }
}
