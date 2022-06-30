<?php
namespace App\Support\Reports;

use App\Domain\Channel\Models\Channel;
use App\Domain\Company\Models\Company;
use App\Domain\User\Models\User;
use Carbon\Carbon;

abstract class ReportBuilder
{
    /** @var \Carbon\Carbon */
    protected $startAt;

    /** @var \Carbon\Carbon */
    protected $endAt;

    private ?User $currentUser;

    private ?Company $currentCompany;

    private ?Channel $currentChannel;

    private ?User $currentManager;

    /**
     * Get channel.
     *
     * @return mixed
     */
    abstract protected function getReportBuilder($proxyLeadSettings, $withTrashed = false);

      /**
     * @return \Carbon\Carbon
     */
    public function getStartAt(): Carbon
    {
        return $this->startAt;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getEndAt(): Carbon
    {
        return $this->endAt;
    }

    public function getCurrentManager(): ?User
    {
        return $this->currentManager;
    }

    public function getCurrentCompany(): ?Company
    {
        return $this->currentCompany;
    }

    public function getCurrentChannel(): ?Channel
    {
        return $this->currentChannel;
    }
}
