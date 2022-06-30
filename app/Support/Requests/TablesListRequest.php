<?php

namespace App\Support\Requests;

use Illuminate\Http\Request;

class TablesListRequest
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

    public function getStartPage()
    {
        return $this->request->get('start');
    }

    /**
     * @return mixed
     */
    public function getSearchQuery()
    {
        return $this->request->get('search') ? $this->request->get('search')['value'] : "";
    }

    /**
     * @return string|null
     */
    public function getSortType(): ?string
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
    public function getSortName(): ?string
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
    public function getPaginationAmount(): int
    {
        if (!$this->request->has('per_page')) {
            return 15;
        }

        $perPage = (int)$this->request->get('per_page', 15);

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
