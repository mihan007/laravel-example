<?php

namespace Tests\Pages\Auth;

use Tests\TestCase;

class InitResetPasswordPageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_open_reset_password_page()
    {
        $this->get('password/reset')
            ->assertStatus(200)
            ->assertSee('Восстановление пароля');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
