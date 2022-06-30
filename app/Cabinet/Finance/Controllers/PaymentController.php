<?php

namespace App\Cabinet\Finance\Controllers;

use App\Domain\Finance\Models\FinanceReport;
use App\Domain\Finance\Models\Payment;
use App\Support\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'finance_report_id' => 'required|exists:finance_reports,id',
            'amount' => 'required|numeric|min:1',
        ]);

        /** @var FinanceReport $report */
        $report = FinanceReport::findOrFail($request->get('finance_report_id'));

        $payment = $report->payments()->create($request->all());

        (new \App\Domain\Finance\FinanceReportCreator($report->company()->first(), Carbon::parse($report->for_date)))->create();

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        /** @var \App\Domain\Finance\Models\FinanceReport $report */
        $report = $payment->financeReport()->first();

        $payment->delete();

        (new \App\Domain\Finance\FinanceReportCreator($report->company()->first(), Carbon::parse($report->for_date)))->create();
    }
}
