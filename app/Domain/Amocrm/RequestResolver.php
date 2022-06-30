<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 16.05.2018
 * Time: 14:15.
 */

namespace App\Domain\Amocrm;

use App\Domain\Amocrm\Models\CompanyAmocrmConfig;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RequestResolver
{
    /**
     * @var Request
     */
    private $request;

    /**
     * RequestResolver constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function resolve()
    {
        if (! $this->request->has('account.subdomain')) {
            throw new InvalidArgumentException('Request in '.get_class($this).' does not have subdomain');
        }

        $similarConfigurations = CompanyAmocrmConfig::where('subdomain', '=', $this->request->get('account')['subdomain'])
            ->with('pipelines')
            ->get();

        if (empty($similarConfigurations)) {
            throw new InvalidArgumentException(get_class($this).' can not find any attach configs to subdomain - '.$this->request->get('account.subdomain'));
        }

        if ($this->request->has('leads.add')) {
            $this->addLeads($similarConfigurations, $this->request->only(['leads.add'])['leads']['add']);
        }

        if ($this->request->has('leads.status')) {
            $this->statusLeads($similarConfigurations, $this->request->only(['leads.status'])['leads']['status']);
        }

        return true;
    }

    /**
     * Add new leads.
     *
     * @param $similarConfigurations
     * @param $leads
     * @return bool
     */
    private function addLeads($similarConfigurations, $leads)
    {
        foreach ($leads as $lead) {
            if (empty($lead['pipeline_id'])) {
                continue;
            }

            $configuration = $this->resolveConfiguration($similarConfigurations, $lead['pipeline_id']);

            if (false === $configuration) {
                continue;
            }

            (new NewLead($configuration, $lead))->add();
        }

        return true;
    }

    /**
     * Get certain configuration.
     *
     * @param $similarConfigurations
     * @param $pipelineId
     * @return bool|\App\Domain\Amocrm\Models\CompanyAmocrmConfig
     */
    private function resolveConfiguration($similarConfigurations, $pipelineId)
    {
        foreach ($similarConfigurations as $configuration) {
            /** @var \App\Domain\Amocrm\Models\CompanyAmocrmConfig $configuration */
            if (empty($configuration->pipelines)) {
                continue;
            }

            $index = $configuration->pipelines->search(function ($item, $key) use ($pipelineId) {
                return $item->pipeline_id == $pipelineId;
            });

            if (false === $index) {
                continue;
            }

            return $configuration;
        }

        return false;
    }

    /**
     * Change status information of the lead.
     *
     * @param $similarConfigurations
     * @param $leads
     * @return bool
     */
    private function statusLeads($similarConfigurations, $leads)
    {
        foreach ($leads as $lead) {
            if (empty($lead['pipeline_id'])) {
                continue;
            }

            $configuration = $this->resolveConfiguration($similarConfigurations, $lead['pipeline_id']);

            if (false === $configuration) {
                continue;
            }

            (new ChangeStatus($configuration, $lead))->change();
        }

        return true;
    }
}
