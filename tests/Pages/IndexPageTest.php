<?php

namespace Tests\Pages;

use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;

class IndexPageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_redirect_from_index_to_login_page()
    {
        $this->withExceptionHandling();

        $this->get('/')
            ->assertRedirect('/login');
    }

    /** @test */
    public function can_hide_index_for_unathorized()
    {
        $this->expectException(AuthenticationException::class);

        $this->get('/');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
