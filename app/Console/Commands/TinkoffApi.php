<?php

namespace App\Console\Commands;

use App\Domain\Tinkoff\Models\TinkoffSetting;
use App\Domain\Tinkoff\Services\TinkoffService;

class TinkoffApi
{
    public function accountStatement()
    {
        $settings = TinkoffSetting::where('is_active', 1)->get();
        foreach ($settings as $s) {
            (new TinkoffService)
                ->setToken($s->token)
                ->setAccountNumber($s->account)
                ->setAccount($s->account_id)
                ->setInn($s->inn)
                ->accountStatement();
        }
    }
}
