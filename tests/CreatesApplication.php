<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // fix relation to public_html directory
        $app->bind('path.public', function () {
            return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public_html';
        });

        config(['database.connections.mysql.database' => 'panel_troiza_test']);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        //Artisan::call('migrate');
    }

    protected function tearDown(): void
    {
        //Artisan::call('migrate:reset');
        parent::tearDown();
    }
}
