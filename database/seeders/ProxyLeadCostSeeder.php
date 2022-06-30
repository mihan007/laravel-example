<?php

namespace Database\Seeders;

use App\Domain\Finance\Models\PaymentTransaction;
use App\Domain\ProxyLead\Models\ProxyLead;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ProxyLeadCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $proxyLeadsCount = ProxyLead::count();
        $output = new ConsoleOutput();
        $progress = new ProgressBar($output, $proxyLeadsCount);
        $progress->start();

        foreach (ProxyLead::cursor() as $proxyLead) {
            if ($proxyLead->company) {
                /** @var PaymentTransaction $lastTransaction */
                $lastTransaction = $this->getLastTransaction($proxyLead->id);
                $proxyLead->cost = $lastTransaction && $lastTransaction->isReduceBalance() ? abs($lastTransaction->amount) : 0;
                $proxyLead->save();
            }
            $progress->advance();
        }

        $progress->finish();
    }

    private function getLastTransaction($proxy_lead_id)
    {
        return PaymentTransaction::query()
            ->where('proxy_leads_id', '=', $proxy_lead_id)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
