<?php

namespace App\Domain\Channel\Models;

use App\Domain\Channel\Observers\ChannelObserver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Channel\Models\Channel
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $account_id
 * @property-read Collection|ChannelReasonsOfRejection[] $channelReasonsOfRejection
 * @property-read int|null $channel_reasons_of_rejection_count
 * @method static Builder|Channel newModelQuery()
 * @method static Builder|Channel newQuery()
 * @method static Builder|Channel query()
 * @method static Builder|Channel whereAccountId($value)
 * @method static Builder|Channel whereCreatedAt($value)
 * @method static Builder|Channel whereId($value)
 * @method static Builder|Channel whereName($value)
 * @method static Builder|Channel whereSlug($value)
 * @method static Builder|Channel whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Channel\Models\ChannelFactory factory(...$parameters)
 */
class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'account_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(ChannelObserver::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function channelReasonsOfRejection()
    {
        return $this->hasMany(ChannelReasonsOfRejection::class);
    }

    public function getReasonsOfRejection()
    {
        $this->load([
            'channelReasonsOfRejection.reasonOrRejection' => function ($query) {
                /* @var Builder $query */
                $query->orderBy('id');
            },
        ]);

        if (null === $this->channelReasonsOfRejection) {
            return collect([]);
        }

        return $this->channelReasonsOfRejection->pluck('reasonOrRejection');
    }
}
