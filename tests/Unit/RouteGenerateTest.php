<?php

namespace Tests\Unit;

use App\Domain\Company\Models\Company;
use Mockery;
use Tests\TestCase;

/**
 * Class UserTest.
 */
class RouteGenerateTest extends TestCase
{
    /** @test */
    public function generate_routes()
    {
        route('subscription.manage', ['key' => 'test']);
        route('subscription.subscribe.pending', ['key' => 'test']);
        route('subscription.unsubscribe.all', ['key' => 'test']);
        route('subscription.company.admin', ['key' => 'test']);
        route('account.users.index', ['accountId' => 'test']);
        route('subscription.manage', ['key' => 'test']);
        route('subscription.ajax.unsubscribe.all', ['key' => 'test']);
        route('subscription.manage', ['key' => 'test']);
        route('subscription.ajax.unsubscribe.one', ['key' => 'test']);
        route('subscription.company.admin', ['key' => 'test']);
        route('subscription.company.changeAdmin', ['key' => 'test']);
        route('ping.index');
        route('user.activate', 'test');
        route('account.companies.index', ['accountId' => 'test']);
        route('account.company.proxy-leads', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.show', ['accountId' => 1, 'id' => 'companyId']);
        route('account.channels.edit', ['accountId' => 1, 'channel' => '_id_']);
        route('account.channels.destroy', ['accountId' => 1, 'channel' => '_id_']);
        route('account.company.report.index', ['accountId' => 'test', 'id' => 'test']);
        route('account.companies.index', ['accountId' => 'test']);
        route('account.company.report.update', ['accountId' => 'test', 'id' => 'test']);
        route('account.companies.update', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.crm-integration.index', ['accountId' => 'test', 'company' => 'test']);
        route('account.companies.show', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.replacement.update', ['accountId' => 'test', 'id' => 'test']);
        route('account.company.report.index', ['accountId' => 'test', 'id' => 'test']);
        route('account.company.report.edit', ['accountId' => 'test', 'id' => 'test']);
        route('account.company.report.index', ['accountId' => 'test', 'id' => 'test']);
        route('account.companies.edit', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.ajax_yandex_balance_for_period', ['accountId' => 'test', 'id' => 'test']);
        route('account.company.analytic_calculator', ['accountId' => 'test', 'id' => 'test']);

        $company = Company::factory()->create();
        route('account.company.finance', ['accountId' => 'test', 'company' => $company]);
        route('account.company.proxy-leads', ['accountId' => 'test', 'company' => 'test']);
        route('account.companies.delete', ['accountId' => 'test', 'id' => 'test']);
        route('account.users.edit', ['accountId' => 'test', 'user' => 'test']);
        route('account.users.update', ['accountId' => 'test', 'user' => 'test']);
        route('account.users.destroy', ['accountId' => 'test', 'user' => 'test']);
        route('account.users.store', ['accountId' => 'test']);
        route('yandex.webhook', ['id' => 'test']);
        route('account.companies.index', ['accountId' => 'test', 'managerId' => 1, 'channelId' => 'test']);
        route('account.company.bitrix.store', ['accountId' => 'test', 'company' => 2]);
        route('account.company.proxy-leads.store', ['accountId' => 'test', 'company' => 2]);
        route('api.v1.web-leads.common.store', ['test']);
        route('api.v1.web-leads.common.store', ['test']);
        route('account.company.proxy-leads.store', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.update', ['accountId' => 'test', 'company' => 'test', 'lead' => 'test']);
        route('account.company.proxy-lead.report.approve', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.report.emailable', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.report.approvenew', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.reconciliation.store', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.report.edit', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.report.approve', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.report.confirm', ['accountId' => 'test', 'company' => 'test']);
        route('account.company.proxy-lead.update', ['accountId' => 'test', 'company' => 'test', 'lead' => 'test']);
        route('account.company.proxy-lead.update', ['accountId' => 'test', 'company' => 'test', 'lead' => 'test']);
        route('account.company.proxy-lead.report.delete', ['accountId' => 'test', 'company' => 'test', 'lead' => 'test']);
        route('account.company.proxy-lead.report.delete', ['accountId' => 'test', 'company' => 'test', 'lead' => 'test']);

        //дошли до конца, уже хорошо
        $this->assertTrue(true);
    }
}
