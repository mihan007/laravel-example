<?php

namespace App\Cabinet\Finance\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Finance\Actions\CalculateExpenseAction;
use App\Domain\Finance\Actions\CalculateIncomeAction;
use App\Domain\Finance\Repositories\PaymentTransactionRepository;
use App\Support\Controllers\Controller;
use App\Support\Helper\RouteHelper;
use App\View\Components\Layout\Menu;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index($accountId, Company $company, Request $request)
    {
        $rows = (new PaymentTransactionRepository($company->id, $request))->get();
        $visibleMenuItems = current_user_is_client() ? [ Menu::MENU_ORDER ] : null;

        return view('finance.index', [
            'company' => $company,
            'role' => current_general_role(),
            'rows' => $rows,
            'visibleMenuItems' => $visibleMenuItems
        ]);
    }

    public function redirectToAccountCompany(Company $company)
    {
        return redirect()->route('account.company.finance', ['accountId' => $company->account_id, 'company' => $company]);
    }

    public function ajaxList($accountId, Company $company, Request $request)
    {
        return (new PaymentTransactionRepository($company->id, $request))->getAndPaginate();
    }

    /**
     * Get company
     *
     * @param integer $accountId
     * @param integer $id
     * @return $company
     */
    public function balanceCompany($accountId, Company $company)
    {
        return $company;
    }

    /**
     * Get company
     *
     * @param integer $accountId
     * @param integer $id
     */
    public function expenseIncome($accountId, Company $company, Request $request): array
    {
        [$startAt, $endAt] = RouteHelper::getDateRange($request);

        return [
            'expense' => (new CalculateExpenseAction())->execute($company, $startAt, $endAt),
            'income' => (new CalculateIncomeAction())->execute($company, $startAt, $endAt)
        ];
    }
}
