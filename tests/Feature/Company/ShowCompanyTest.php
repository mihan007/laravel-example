<?php

namespace Tests\Unit;

use App\Domain\Account\Models\Account;
use App\Domain\Company\Models\Company;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Class ShowCompanyTest.
 */
class ShowCompanyTest extends TestCase
{
    /** @var \App\Domain\Company\Models\Company */
    protected $company;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->company = Company::factory()->create(['account_id' => $this->account->id]);
    }

    /** @test */
    public function guest_must_not_access_to_company() :void
    {
        $this->withExceptionHandling()->makeRequest()->assertStatus(302);
    }

    /** @test */
    public function auth_user_can_access_company() :void
    {
        $this->signIn(null, 'super-admin', $this->account)->makeRequest()->assertStatus(200);
    }

    private function makeRequest($data = []): TestResponse
    {
        return $this->get(route('account.company.show', [
            'accountId' => $this->company->account_id,
            'id' => $this->company,
        ]));
    }
}
