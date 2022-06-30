<?php

namespace Tests\Pages\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_open_login_page()
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Лидогенератор')
            ->assertSee('Восстановить пароль')
            ->assertSee('Запомнить меня')
            ->assertSee('Войти');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
