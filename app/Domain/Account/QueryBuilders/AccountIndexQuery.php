<?php

namespace App\Domain\Account\QueryBuilders;

use App\Domain\Account\Models\Account;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class AccountIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = Account::query()->orderBy('id', 'desc');
        parent::__construct($query, $request);
    }
}
