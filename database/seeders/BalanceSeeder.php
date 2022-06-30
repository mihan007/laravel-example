<?php

namespace Database\Seeders;

use App\Domain\Finance\Models\PaymentTransaction;
use Illuminate\Database\Seeder;

class BalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentTransactions = PaymentTransaction::query()
            ->with('company')
            ->orderBy('updated_at', 'desc');
        $companyBalances = [];
        $this->command->getOutput()->progressStart($paymentTransactions->count());
        $paymentTransactions->chunk(
            5000,
            function ($paymentTransactionChunk) use (&$companyBalances) {
                $cases = [];
                $params = [];
                $ids = [];
                /** @var PaymentTransaction $paymentTransaction */
                foreach ($paymentTransactionChunk as $paymentTransaction) {
                    $this->command->getOutput()->progressAdvance();
                    $companyId = $paymentTransaction->company_id;
                    if (!$paymentTransaction->company) {
                        continue;
                    }
                    if (!isset($companyBalances[$companyId])) {
                        $companyBalances[$companyId] = $paymentTransaction->company->balance;
                    }
                    $cases[] = "WHEN {$paymentTransaction->id} then ?";
                    $params[] = $companyBalances[$companyId];
                    $ids[] = $paymentTransaction->id;
                    if ($paymentTransaction->isReduceBalance()) {
                        $companyBalances[$companyId] += $paymentTransaction->amount;
                    } else {
                        $companyBalances[$companyId] -= $paymentTransaction->amount;
                    }
                }

                $ids = implode(',', $ids);
                $cases = implode(' ', $cases);

                if (!empty($ids)) {
                    \DB::update(
                        "UPDATE payment_transactions SET `balance` = CASE `id` {$cases} END WHERE `id` in ({$ids})",
                        $params
                    );
                }
            }
        );
        $this->command->getOutput()->progressFinish();
    }
}
