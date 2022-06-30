<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\LidogeneratorSubscription
 *
 * @property int $id
 * @property string $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|LidogeneratorSubscription newModelQuery()
 * @method static Builder|LidogeneratorSubscription newQuery()
 * @method static Builder|LidogeneratorSubscription query()
 * @method static Builder|LidogeneratorSubscription whereCreatedAt($value)
 * @method static Builder|LidogeneratorSubscription whereEmail($value)
 * @method static Builder|LidogeneratorSubscription whereId($value)
 * @method static Builder|LidogeneratorSubscription whereUpdatedAt($value)
 * @mixin Eloquent
 */
class LidogeneratorSubscription extends Model
{
    protected $fillable = [
        'email',
    ];
}
