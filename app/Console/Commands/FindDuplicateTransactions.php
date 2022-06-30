<?php

namespace App\Console\Commands;

use App\Domain\Company\Actions\UpdateCompanyBalanceAction;
use App\Domain\Finance\Models\PaymentTransaction;
use Illuminate\Console\Command;

class FindDuplicateTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get money back for double charge';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $checkFrom = '2020-04-01 00:00:00';
        $paymentTransactions = PaymentTransaction::query()
            ->where('created_at', '>=', $checkFrom)
            ->where('status', 'write-off')
            ->where('operation', 'write-off')
            ->where('company_id', '!=', 112)
            ->orderBy('created_at', 'asc')
            ->get();
        $processed = [];
        foreach ($paymentTransactions as $paymentTransaction) {
            if (in_array($paymentTransaction->proxy_leads_id, $processed)) {
                continue;
            }
            $processed[] = $paymentTransaction->proxy_leads_id;
            $doublePaymentTransactions = PaymentTransaction::query()
                ->where('created_at', '>=', $checkFrom)
                ->where('status', 'write-off')
                ->where('operation', 'write-off')
                ->where('id', '!=', $paymentTransaction->id)
                ->where('proxy_leads_id', $paymentTransaction->proxy_leads_id)
                ->get();
            $backPaymentTransactions = PaymentTransaction::query()
                ->where('created_at', '>=', $checkFrom)
                ->where('status', 'replenishment')
                ->where('operation', 'replenishment')
                ->where('id', '!=', $paymentTransaction->id)
                ->where('proxy_leads_id', $paymentTransaction->proxy_leads_id)
                ->get();
            $countOfCharges = $doublePaymentTransactions->count() + 1;
            $countOfBack = $backPaymentTransactions->count();
            $countOfWriteOff = $countOfCharges - $countOfBack;
            if ($countOfWriteOff > 1) {
                echo "\nCharges for lead "
                    ."{$paymentTransaction->proxy_leads_id}"
                    .": {$countOfCharges}"
                    ."\n";

                foreach ($doublePaymentTransactions as $doublePaymentTransaction) {
                    echo "Double charge {$paymentTransaction->amount} for lead "
                        ."{$paymentTransaction->proxy_leads_id} "
                        ."for company {$paymentTransaction->company_id}, "
                        ."{$paymentTransaction->created_at} and {$doublePaymentTransaction->created_at}"
                        ."\n";

                    if ($this->returnMoney($paymentTransaction)) {
                        echo "We returned {$paymentTransaction->amount} for lead "
                            ."{$paymentTransaction->proxy_leads_id} "
                            ."for company {$paymentTransaction->company_id}"
                            ."\n";
                    }
                }
            }
        }
    }

    private function returnMoney(PaymentTransaction $paymentTransaction)
    {
        $returnPaymentTransaction = new PaymentTransaction();
        $returnPaymentTransaction->status = 'replenishment';
        $returnPaymentTransaction->operation = 'replenishment';
        $returnPaymentTransaction->proxy_leads_id = $paymentTransaction->proxy_leads_id;
        $returnPaymentTransaction->amount = $paymentTransaction->amount;
        $returnPaymentTransaction->payment_type = $paymentTransaction->payment_type;
        $returnPaymentTransaction->company_id = $paymentTransaction->company_id;
        $returnPaymentTransaction->information = "Возврат средств за дубль заявки №{$paymentTransaction->proxy_leads_id}";

        echo "Balance was {$paymentTransaction->company->balance}, ";
        (new UpdateCompanyBalanceAction())->execute($paymentTransaction->company, $paymentTransaction->amount);
        $returnPaymentTransaction->save();

        echo "new balance is {$paymentTransaction->company->balance}\n";
    }
}
