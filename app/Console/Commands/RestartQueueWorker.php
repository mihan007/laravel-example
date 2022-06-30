<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Illuminated\Console\Mutex;
use Illuminated\Console\WithoutOverlapping;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartQueueWorker extends Command
{
    use WithoutOverlapping;

    /**
     * @var string
     */
    protected $signature = 'queue:restart-worker {--tries=1} {--timeout=10} {--sleep=10}';

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $description = 'Restart queue worker';

    public function __construct()
    {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->initializeMutex();
        } catch (\RuntimeException $exception) {
            $this->initialized = true;
        }
    }

    /**
     * @return bool
     */
    public function handle()
    {
        if ($this->initialized) {
            return false;
        }

        Artisan::call('queue:work', [
            '--tries' => $this->option('tries') ?? 3,
            '--timeout' => $this->option('timeout') ?? 10,
            '--sleep' => $this->option('sleep') ?? 3,
        ]);

        return true;
    }
}
