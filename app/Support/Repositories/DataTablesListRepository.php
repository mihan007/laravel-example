<?php

namespace App\Support\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class DataTablesListRepository
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * VueTableListRepository constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Add search query.
     *
     * @param Builder $builder
     */
    abstract protected function addSearch(Builder $builder);

    /**
     * Get channel.
     *
     * @return mixed
     */
    abstract protected function getChannel();

    /**
     * Create companies builder with necessary data.
     *
     * @return Builder
     */
    abstract protected function getCompanyBuilder(): Builder;

    /**
     * @return mixed
     */
    protected function getSearchQuery()
    {
        return $this->request->get('search') ? $this->request->get('search')['value'] : "";
    }

    /**
     * @return string|null
     */
    protected function getSortType(): ?string
    {
        $order = $this->request->get('order')[0] ?? null;

        if (null === $order) {
            return null;
        }

        return $order['dir'];
    }

    /**
     * Get request sort name.
     *
     * @return string|null
     */
    protected function getSortName(): ?string
    {
        $columns = $this->request->get('columns');

        $order = $this->request->get('order') ? $this->request->get('order')[0] : null;

        if (null === $order) {
            return null;
        }

        return $columns[$order['column']]['name'];
    }

    /**
     * Parse request sort.
     *
     * @return string|null
     */
    protected function parseSort()
    {
        $columns = $this->request->get('columns');

        $order = $this->request->get('order')[0];

        if (null === $order) {
            return null;
        }

        return $columns[$order['column']]['name'];
    }

    /**
     * Get pagination amount.
     *
     * @return int
     */
    protected function getPaginationAmount(): int
    {
        if (! $this->request->has('per_page')) {
            return 15;
        }

        $perPage = (int) $this->request->get('per_page', 15);

        return $perPage < 15 ? 15 : $perPage;
    }

    /**
     * Get status sort
     *
     * @return string|null
     */
    protected function getStatus(): ?string
    {
        $status = $this->request->get('status');

        if (null === $status) {
            return null;
        }

        return $status;
    }

}
