<?php

namespace App\Domain\Company\Events;

use App\Domain\Company\Models\Company;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreReconciliationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Carbon
     */
    public $period;
    /**
     * @var \App\Domain\Company\Models\Company
     */
    public $company;

    /**
     * Create a new event instance.
     *
     * @param \App\Domain\Company\Models\Company $company
     * @param Carbon $period
     */
    public function __construct(Company $company, Carbon $period)
    {
        $this->period = $period;
        $this->company = $company;
    }
}
