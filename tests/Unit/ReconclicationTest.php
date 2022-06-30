<?php

namespace Tests\Unit;

use App\Domain\ProxyLead\Models\Reconclication;
use Tests\TestCase;

class ReconclicationTest extends TestCase
{
    /** @test */
    public function it_should_return_available_types() :void
    {
        $this->assertCount(2, Reconclication::getTypes());
        $this->assertSame('user', Reconclication::getTypes()[0]);
        $this->assertSame('admin', Reconclication::getTypes()[1]);
    }
}
