<?php

namespace Tests\Unit;

use App\Support\Helper\SessionReportMessage;
use App\Support\Helper\SessionReportStatus;
use Tests\TestCase;

class SessionReportMessageTest extends TestCase
{
    /** @test */
    public function it_should_convert_to_array() :void
    {
        $status = SessionReportStatus::SUCCESS();

        $message = new SessionReportMessage($status, 'message');

        $this->assertSame(['status' => (string) $status, 'text' => 'message'], $message->toArray());
    }

    /** @test */
    public function it_can_get_report_variable_name(): void
    {
        $message = new SessionReportMessage(SessionReportStatus::SUCCESS(), 'm');

        $this->assertSame('message', $message->getReportVariableName());
    }
}
