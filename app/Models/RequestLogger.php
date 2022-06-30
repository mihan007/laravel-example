<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\RequestLogger
 *
 * @property int $id
 * @property string $url
 * @property string $method
 * @property mixed $get
 * @property mixed $post
 * @property string $raw_post
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger newQuery()
 * @method static Builder|RequestLogger onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereGet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger wherePost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereRawPost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestLogger whereUrl($value)
 * @method static Builder|RequestLogger withTrashed()
 * @method static Builder|RequestLogger withoutTrashed()
 * @mixin Eloquent
 */
/* @deprecated */
class RequestLogger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'url',
        'method',
        'get',
        'post',
        'raw_post',
    ];

    public function getPostAttribute($value)
    {
        $result = json_decode($value, true);
        if (! is_array($result)) {
            return [];
        } else {
            return $result;
        }
    }

    public function setPostAttribute($value)
    {
        $this->attributes['post'] = json_encode($value);
    }

    public function getGetAttribute($value)
    {
        $result = json_decode($value, true);
        if (! is_array($result)) {
            return [];
        } else {
            return $result;
        }
    }

    public function setGetAttribute($value)
    {
        $this->attributes['get'] = json_encode($value);
    }
}
