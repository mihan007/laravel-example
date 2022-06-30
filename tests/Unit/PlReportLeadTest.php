<?php

namespace Tests\Unit;

use App\Domain\ProxyLead\Models\PlReportLead;
use App\Domain\ProxyLead\Models\ProxyLead;
use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Tests\TestCase;

/**
 * Class PlReportLeadTest.
 */
class PlReportLeadTest extends TestCase
{
    /** @test */
    public function is_must_soft_delete()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var \App\Domain\ProxyLead\Models\PlReportLead $lead */
        $lead = PlReportLead::factory()->create();

        $this->assertFalse($lead->trashed());

        $lead->delete();

        $this->assertTrue($lead->trashed());
    }

    /** @test */
    public function if_user_agree_admin_must_also_agree()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        $reportLead = create(
            PlReportLead::class,
            [
                'company_confirmed' => PlReportLead::STATUS_DISAGREE,
                'admin_confirmed' => PlReportLead::STATUS_NOT_CONFIRMED,
            ]
        );

        $reportLead->userConfirmation(PlReportLead::STATUS_AGREE)->save();

        $reportLead->fresh();

        $this->assertEquals(PlReportLead::STATUS_AGREE, $reportLead->company_confirmed);
        $this->assertEquals(PlReportLead::STATUS_AGREE, $reportLead->admin_confirmed);
    }

    /** @test */
    public function if_user_disagree_admin_confirmation_must_change_to_not_confirmed_status()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        $reportLead = PlReportLead::factory()->create();

        $reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE)->save();

        $reportLead->fresh();

        $this->assertEquals(PlReportLead::STATUS_DISAGREE, $reportLead->company_confirmed);
        $this->assertEquals(PlReportLead::STATUS_NOT_CONFIRMED, $reportLead->admin_confirmed);
    }

    /**
     * @dataProvider diffTypesOfDisagreeStatus
     * @param $status
     * @test
     */
    public function user_confirmation_function_must_work_correctly_with_diff_types_of_input_disagree_values($status)
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var \App\Domain\ProxyLead\Models\PlReportLead $reportLead */
        $reportLead = PlReportLead::factory()->create();

        $reportLead->userConfirmation($status)->save();

        $reportLead->fresh();

        $this->assertEquals(PlReportLead::STATUS_DISAGREE, $reportLead->company_confirmed);
        $this->assertEquals(PlReportLead::STATUS_NOT_CONFIRMED, $reportLead->admin_confirmed);
    }

    public function diffTypesOfDisagreeStatus()
    {
        return [
            [PlReportLead::STATUS_DISAGREE], // int
            ['0'],
            [false],
        ];
    }

    /**
     * @dataProvider diffTypesOfAgreeStatus
     * @param $status
     * @test
     */
    public function user_confirmation_function_must_work_correctly_with_diff_types_of_input_agree_values($status)
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var \App\Domain\ProxyLead\Models\PlReportLead $reportLead */
        $reportLead = create(
            PlReportLead::class,
            [
                'company_confirmed' => PlReportLead::STATUS_DISAGREE,
                'admin_confirmed' => PlReportLead::STATUS_NOT_CONFIRMED,
            ]
        );

        $reportLead->userConfirmation($status)->save();

        $reportLead->fresh();

        $this->assertEquals(PlReportLead::STATUS_AGREE, $reportLead->company_confirmed);
        $this->assertEquals(PlReportLead::STATUS_AGREE, $reportLead->admin_confirmed);
    }

    public function diffTypesOfAgreeStatus()
    {
        return [
            [PlReportLead::STATUS_AGREE], // int
            ['1'],
            [true],
        ];
    }

    /** @test */
    public function admin_agree_with_user_disagree()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var PlReportLead $reportLead */
        $reportLead = PlReportLead::factory()->create();

        $reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE)
            ->adminConfirmation(PlReportLead::STATUS_DISAGREE)
            ->save();

        $reportLead->fresh();

        $this->assertEquals(PlReportLead::STATUS_AGREE, $reportLead->company_confirmed);
        $this->assertEquals(PlReportLead::STATUS_DISAGREE, $reportLead->admin_confirmed);
    }

    /** @test */
    public function target_status_gets_correct()
    {
        PlReportLead::factory()->create();
        $reportLead = PlReportLead::first();

        $this->assertTrue($reportLead->is_target);
    }

    /** @test */
    public function non_target_status_gets_correct()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var \App\Domain\ProxyLead\Models\PlReportLead $reportLead */
        $reportLead = PlReportLead::factory()->create();

        $reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE)
            ->adminConfirmation(PlReportLead::STATUS_AGREE);

        $this->assertTrue($reportLead->is_non_targeted);
    }

    /** @test */
    public function each_report_may_have_reason()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        $reason = ReasonsOfRejection::factory()->create();
        $report = PlReportLead::factory()->create(['reasons_of_rejection_id' => $reason->id]);

        $this->assertEquals($reason->toArray(), $report->reason()->first()->toArray());
    }

    /** @test */
    public function admin_not_confirmed_status_gets_correct()
    {
        $this->truncate(ProxyLead::class);
        $this->truncate(PlReportLead::class);

        /** @var \App\Domain\ProxyLead\Models\PlReportLead $reportLead */
        $reportLead = PlReportLead::factory()->create();

        $reportLead->userConfirmation(PlReportLead::STATUS_DISAGREE);

        $this->assertTrue($reportLead->is_not_confirmed_admin);

        $reportLead->adminConfirmation(PlReportLead::STATUS_DISAGREE);

        $this->assertFalse($reportLead->is_not_confirmed_admin);
    }
}
