<?php

namespace App\Domain\ProxyLead\Models;

use App\Domain\Channel\Models\ChannelReasonsOfRejection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\ReasonsOfRejection
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|ChannelReasonsOfRejection[] $channelReasonOfRejections
 * @property-read int|null $channel_reason_of_rejections_count
 * @method static Builder|ReasonsOfRejection newModelQuery()
 * @method static Builder|ReasonsOfRejection newQuery()
 * @method static Builder|ReasonsOfRejection query()
 * @method static Builder|ReasonsOfRejection whereCreatedAt($value)
 * @method static Builder|ReasonsOfRejection whereId($value)
 * @method static Builder|ReasonsOfRejection whereName($value)
 * @method static Builder|ReasonsOfRejection whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\ReasonsOfRejectionFactory factory(...$parameters)
 */
class ReasonsOfRejection extends Model
{
    use HasFactory;

    //Не оставляли заявку
    public const NOT_LEAD = 4;

    protected $fillable = ['name'];

    public function channelReasonOfRejections()
    {
        return $this->hasMany(ChannelReasonsOfRejection::class);
    }
}
