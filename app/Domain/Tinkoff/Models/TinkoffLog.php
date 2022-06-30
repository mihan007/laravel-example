<?php

namespace App\Domain\Tinkoff\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Tinkoff\Models\TinkoffLog
 *
 * @property int $id
 * @property string $type
 * @property array $request
 * @property array|null $response
 * @property int $success
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $account_id
 * @method static Builder|TinkoffLog newModelQuery()
 * @method static Builder|TinkoffLog newQuery()
 * @method static Builder|TinkoffLog query()
 * @method static Builder|TinkoffLog whereAccountId($value)
 * @method static Builder|TinkoffLog whereCreatedAt($value)
 * @method static Builder|TinkoffLog whereId($value)
 * @method static Builder|TinkoffLog whereRequest($value)
 * @method static Builder|TinkoffLog whereResponse($value)
 * @method static Builder|TinkoffLog whereSuccess($value)
 * @method static Builder|TinkoffLog whereType($value)
 * @method static Builder|TinkoffLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TinkoffLog extends Model
{
    public $fillable = ['type', 'request', 'response', 'success', 'account_id'];

    protected $casts = [
        'request' => 'json',
        'response' => 'json',
    ];
}
