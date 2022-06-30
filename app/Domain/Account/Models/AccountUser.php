<?php

namespace App\Domain\Account\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Account\Models\AccountUser
 *
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property string|null $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AccountUser newModelQuery()
 * @method static Builder|AccountUser newQuery()
 * @method static Builder|AccountUser query()
 * @method static Builder|AccountUser whereAccountId($value)
 * @method static Builder|AccountUser whereCreatedAt($value)
 * @method static Builder|AccountUser whereId($value)
 * @method static Builder|AccountUser whereRole($value)
 * @method static Builder|AccountUser whereUpdatedAt($value)
 * @method static Builder|AccountUser whereUserId($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Account\Models\AccountUserFactory factory(...$parameters)
 */
class AccountUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'role',
        'created_at',
        'updated_at',
    ];
}
