<?php

namespace App\Cabinet\Dashboard\Controllers;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;
use App\Models\TotalDayLead;
use App\Support\Controllers\Controller;
use Carbon\Carbon;

class IndexController extends Controller
{
    public function index()
    {
        if (current_general_role() === User::GENERAL_ROLE_CLIENT) {
            $company = current_user()->companies->first();
            abort_unless($company, 404);

            $defaultRoute = route('account.company.proxy-leads', ['company' => $company]);
            return redirect()->intended($defaultRoute);
        }

        $defaultRoute = route('account.companies.index');
        return redirect()->intended($defaultRoute);
    }

    public function dashboard()
    {
        $currentAccount = Account::current();
        $currentUser = User::current();
        $data['channels'] = $currentUser->channels;
        $data['current_month_leads'] = $currentAccount->countAllLeads;

        return view('dashboard')->with('data', $data);
    }

    /**
     * Get leads in current month.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthLeads()
    {
        $dayLeads = TotalDayLead::currentMonthLeads()->get();

        return response()->json($dayLeads);
    }

    public function getHalfYearLeads()
    {
        $halYearLeads = TotalDayLead::leadsOfHalfYear()->get();

        $monthIndex = 0;
        $amountOfElements = 0;
        $result = [];
        $months = [''.'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        // group
        foreach ($halYearLeads as $leadElement) {
            $leadElementMonth = Carbon::parse($leadElement->for_date)->format('Y-m');

            $key = array_search($leadElementMonth, array_column($result, 'month'));

            if (false === $key) {
                $result[] = [
                    'month' => $leadElementMonth,
                    'readableMonth' => $months[Carbon::parse($leadElement->for_date)->format('n') - 1],
                    'amount' => $leadElement->amount,
                ];

                continue;
            }

            $result[$key]['amount'] += $leadElement->amount;
        }

        return $result;
    }
}
