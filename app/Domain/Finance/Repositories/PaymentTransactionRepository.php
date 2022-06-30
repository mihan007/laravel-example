<?php

namespace App\Domain\Finance\Repositories;

use App\Domain\Finance\Models\PaymentTransaction;
use App\Support\Repositories\DataTablesListRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentTransactionRepository extends DataTablesListRepository
{
    /** @var Carbon */
    protected $endAt;

    /** @var Carbon */
    protected $startAt;
    public $company_id;
    /**
     * @var mixed
     */
    private $operation;
    /**
     * @var mixed
     */
    private $payment_type;

    public function __construct($id, Request $request)
    {
        parent::__construct($request);
        $this->company_id = $id;
        $this->startAt = $request->has('start_at') && !empty($request->get('start_at'))
            ? Carbon::parse($request->get('start_at'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $this->endAt = $request->has('end_at') && !empty($request->get('end_at'))
            ? Carbon::parse($request->get('end_at'))->endOfDay()
            : now()->endOfDay()->endOfDay();
        $this->operation = $request->get('operation');
        $this->payment_type = $request->get('payment_type');
    }

    /**
     * Get data.
     *
     * @return Collection
     */
    public function get()
    {
        return $this->getBuilder()->get();
    }

    /**
     * Get paginate data.
     *
     * @return Collection
     */
    public function getAndPaginate(): Collection
    {
        $page = ($this->request->get('start') / $this->getPaginationAmount()) + 1;

        $reportBuilder = clone $this->getBuilder();
        $reportBuilder = $this->addSort($reportBuilder);
        $reportBuilder = $this->addSearch($reportBuilder);
        $paginator = $reportBuilder->paginate($this->getPaginationAmount(), ['*'], 'page', $page);
        $transaction = collect($paginator->items());
        $this->transformCompanies($transaction);

        $paginatorArray = $paginator->toArray();
        $paginatorArray['start_at'] = $this->startAt->toDateString();
        $paginatorArray['end_at'] = $this->endAt->toDateString();
        $paginatorArray['recordsTotal'] = $paginator->total();
        $paginatorArray['recordsFiltered'] = $paginator->total();

        return collect($paginatorArray);
    }

    /**
     * Builder sort companies.
     *
     * @param Builder $builder
     * @return Builder
     */
    private function addSort(Builder $builder): Builder
    {
        $sortName = $this->getSortName();
        $sortType = $this->getSortType();

        if (!$this->isSortRequired($sortType, $sortName)) {
            return $builder->orderBy('updated_at', 'DESC');
        }

        return $builder->orderBy($sortName, $sortType)->orderBy('updated_at', 'DESC');
    }

    /**
     * @param string $sortType
     * @param string $sortName
     * @return bool
     */
    private function isSortRequired(string $sortType, string $sortName): bool
    {
        return !empty($sortType)
            && !empty($sortName);
    }

    /**
     * Add search query.
     *
     * @param Builder $builder
     * @return Builder
     */
    protected function addSearch(Builder $builder): Builder
    {
        $filter = $this->getSearchQuery();

        if (!$filter) {
            return $builder;
        }

        return $builder->whereRaw('information LIKE ? ', ['%' . trim($filter) . '%'])
                ->orWhereRaw('amount LIKE ? ', ['%' . trim($filter) . '%'])
                ->orWhereRaw('balance LIKE ? ', ['%' . trim($filter) . '%']);
    }

    /**
     * Get paginate data.
     *
     * @return Collection
     */
    public function paginate()
    {
        $paginator = $this->getBuilder()->paginate($this->getPaginationAmount());

        $transaction = collect($paginator->items());
        $this->transformCompanies($transaction);

        $paginatorArray = $paginator->toArray();
        $paginatorArray['data'] = $transaction->toArray();
        $paginatorArray['start_at'] = $this->startAt->toDateString();
        $paginatorArray['end_at'] = $this->endAt->toDateString();

        return collect($paginatorArray);
    }

    protected function builderSearch(Builder $builder)
    {
        $filter = $this->getSearchQuery();

        if (null === $filter) {
            return;
        }

        $builder->whereRaw('LOWER(`name`) LIKE ? ', ['%' . trim(strtolower($filter)) . '%']);
    }

    /**
     * Create companies builder with necessary data.
     *
     * @return Builder
     */
    protected function getBuilder(): Builder
    {
        $startAt = $this->startAt;
        $endAt = $this->endAt;

        return PaymentTransaction::query()
            ->where('company_id', $this->company_id)
            ->when(
                $this->payment_type,
                function ($query, $paymentType) {
                    return $query->where('payment_type', $paymentType);
                }
            )
            ->when(
                $this->operation,
                function ($query, $operation) {
                    switch ($operation) {
                        case PaymentTransaction::FILTER_INCOME:
                            return $query->incomeWithoutMoneyBack();
                        case PaymentTransaction::FILTER_EXPENSE:
                            return $query->expense();
                        case PaymentTransaction::FILTER_MONEY_BACK:
                            return $query->moneyBack();
                        case PaymentTransaction::FILTER_NOT_PAID:
                            return $query->notPaidNotInvoice();
                        case PaymentTransaction::FILTER_INVOICED:
                            return $query->notPaidInvoice();
                        default:
                            return $query;
                    }
                }
            )
            ->whereBetween('updated_at', [$startAt, $endAt]);
    }

    private function transformCompanies(Collection $payment_transactions)
    {
        $payment_transactions->each(
            function (PaymentTransaction $payment_transaction) {
                $payment_transaction->amount = $this->getReadableAmount($payment_transaction);
                $payment_transaction->operation = $this->getReadablePaymentStatus($payment_transaction);
                $payment_transaction->paid_by_tinkoff = $payment_transaction->getPaidByTinkoffAttribute();
            }
        );
    }

    protected function getChannel()
    {
        return auth()->user()->getCompaniesChannel();
    }

    /**
     * @param \App\Domain\Finance\Models\PaymentTransaction $payment_transaction
     * @return string
     */
    private function getReadablePaymentStatus(PaymentTransaction $payment_transaction): string
    {
        $isReturn = mb_stripos($payment_transaction->information, 'нецелевая') !== false;
        $isReturn = $isReturn || mb_stripos($payment_transaction->information, 'удален') !== false;
        if ($isReturn) {
            return PaymentTransaction::$operations[PaymentTransaction::OPERATION_RETURN];
        }
        if (!$payment_transaction->operation && in_array(
                $payment_transaction->payment_type,
                PaymentTransaction::$replinishmentSource
            )) {
            $payment_transaction->operation = PaymentTransaction::OPERATION_REPLENISHMENT;
        }
        $transactionStatus = PaymentTransaction::$operations[$payment_transaction->status] ?? 'Неизвестный тип операции';
        $transactionReadableStatus = PaymentTransaction::$operations[$payment_transaction->operation] ?? $transactionStatus;
        if ($payment_transaction->operation === PaymentTransaction::OPERATION_REPLENISHMENT) {
            $paymentSource = PaymentTransaction::$replinishmentSourceNames[$payment_transaction->payment_type] ?? "Ручное";
            $transactionReadableStatus .= " (" . $paymentSource . ")";
        } else if ($payment_transaction->payment_type === PaymentTransaction::PAYMENT_TYPE_MANUAL) {
            $transactionReadableStatus .= " (Ручное)";
        }
        return $transactionReadableStatus;
    }

    private function getReadableAmount(PaymentTransaction $payment_transaction)
    {
        if ($payment_transaction->status === PaymentTransaction::STATUS_NOT_PAID) {
            return (int)$payment_transaction->amount;
        }
        $sign = '';
        if (0 != $payment_transaction->amount) {
            $sign = $payment_transaction->isReduceBalance() ? '-' : '+';
        }

        return $sign . (int)$payment_transaction->amount;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompanyBuilder(): Builder
    {
        return $this->getBuilder();
    }

    /**
     * Get pagination amount.
     *
     * @return int
     */
    protected function getPaginationAmount(): int
    {
        $perPage = (int)$this->request->get('per_page', 30);

        return $perPage < 30 ? 30 : $perPage;
    }
}
