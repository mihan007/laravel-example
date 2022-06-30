<?php

namespace Tests\Feature\User\Finance;

use App\Domain\Account\Models\Account;
use App\Domain\Account\Models\AccountUser;
use App\Domain\Company\Models\Company;
use App\Domain\Company\Models\CompanyRoleUser;
use App\Domain\User\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function it_should_redirect_guest_to_login_page(): void
    {
        $account = Account::factory()->create();
        $company = Company::factory()->account($account)->create();
        $loginPage = route('login');

        $this->withExceptionHandling()->makeRequest($account, $company)->assertRedirect($loginPage);
    }

    /** @test */
    public function it_should_access_to_page_for_superadmin(): void
    {
        $account = Account::factory()->create();
        $this->signIn(null, User::ROLE_SUPER_ADMIN_NAME, $account);
        $company = Company::factory()->create(['account_id' => $account->id]);

        $this->makeRequest($account, $company)->assertOk();
    }

    /** @test */
    public function it_should_access_to_page_for_account_admin(): void
    {
        $account = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        $accountUser = AccountUser::factory()->create(['user_id' => $user->id, 'account_id' => $account->id]);
        $this->signIn($user, User::ROLE_ACCOUNT_ADMIN_NAME, $account, $accountUser);

        $this->makeRequest($account, $company)->assertOk();
    }

    /** @test */
    public function it_should_not_access_to_page_for_another_account_admin(): void
    {
        $accountOk = Account::factory()->create();
        $accountFail = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $accountOk->id]);
        $user = User::factory()->create();
        $accountUser = AccountUser::factory()->create(['user_id' => $user->id, 'account_id' => $accountFail->id]);
        $this->signIn($user, User::ROLE_ACCOUNT_ADMIN_NAME, $accountFail, $accountUser);

        $this->withExceptionHandling()->makeRequest($accountFail, $company)->assertForbidden();
    }

    /** @test */
    public function it_should_access_to_page_for_company_manager(): void
    {
        $account = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        CompanyRoleUser::factory()->user($user)->company($company)->create();
        $this->signIn($user, User::ROLE_ACCOUNT_MANAGER_NAME, $account);

        $this->makeRequest($account, $company)->assertOk();
    }

    /** @test */
    public function it_should_not_access_to_page_for_another_company_manager(): void
    {
        $account = Account::factory()->create();
        $company1 = Company::factory()->create(['account_id' => $account->id]);
        $company2 = Company::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        CompanyRoleUser::factory()->user($user)->company($company2)->create();
        $this->signIn($user, User::ROLE_ACCOUNT_MANAGER_NAME, $account);

        $this->withExceptionHandling()->makeRequest($account, $company1)->assertNotFound();
    }

    /** @test */
    public function it_should_access_to_page_for_company_client(): void
    {
        $account = Account::factory()->create();
        $company = Company::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        CompanyRoleUser::factory()->user($user)->company($company)->create();
        $this->signIn($user, User::ROLE_ACCOUNT_CLIENT_NAME, $account);

        $this->makeRequest($account, $company)->assertOk();
    }

    private function makeRequest(Account $account, Company $company): TestResponse
    {
        $route = $this->getFinancePageRoute($company, $account);

        return $this->get($route);
    }

    /** @test */
    public function it_should_not_access_to_page_for_another_company_client(): void
    {
        $account = Account::factory()->create();
        $company1 = Company::factory()->create(['account_id' => $account->id]);
        $company2 = Company::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        CompanyRoleUser::factory()->user($user)->company($company2)->create();
        $this->signIn($user, User::ROLE_ACCOUNT_CLIENT_NAME, $account);

        $this->withExceptionHandling()->makeRequest($account, $company1)->assertNotFound();
    }

    /**
     * @param Company $company
     * @param Account $account
     * @return string
     */
    private function getFinancePageRoute(Company $company, Account $account): string
    {
        return route('account.company.finance', ['company' => $company, 'accountId' => $account->id]);
    }
}
