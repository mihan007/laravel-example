<?php

namespace Tests\Unit;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use Tests\TestCase;

/**
 * Class CompaniesChannelsTest.
 */
class CompaniesChannelsTest extends TestCase
{
    /** @test */
    public function user_can_change_company_channel()
    {
        $this->signInAsSuperAdmin();
        $account = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $account->id]);
        $channel = Channel::factory()->create(['account_id' => $account->id]);
        AccountUser::factory()->create(['user_id' => auth()->user()->id, 'account_id' => $channel->account_id]);

        $this->assertDatabaseMissing(
            'companies',
            [
                'id' => $company->id,
                'channel_id' => $channel->id,
                'account_id' => $channel->account_id
            ]
        );

        $requestData = $company->toArray();
        $requestData = array_merge($requestData, ['channel_id' => $channel->id]);
        $requestData = array_merge(
            $requestData,
            $this->defaultUpdateCompanyData()
        );

        $this->put(
            route('account.companies.update', ['accountId' => $company->account_id, 'company' => $company->id]),
            $requestData
        );

        $this->assertDatabaseHas(
            'companies',
            [
                'id' => $company->id,
                'channel_id' => $channel->id,
                'account_id' => $channel->account_id
            ]
        );
    }

    private function defaultUpdateCompanyData()
    {
        return [
            'roistat_limit' => 200,
            'roistat_config' => ['max_lead_price' => 200],
            'roistat_max_costs' => 200,
            'roistat_avito_visits_limit' => 200,
            'ya_login' => 'ya_login',
            'roistat_project_id' => 'roistat_project_id',
            'api_key' => 'api_key',
        ];
    }
}
