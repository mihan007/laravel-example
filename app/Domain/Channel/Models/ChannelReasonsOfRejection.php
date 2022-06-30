<?php

namespace App\Domain\Channel\Models;

use App\Domain\ProxyLead\Models\ReasonsOfRejection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Channel\Models\ChannelReasonsOfRejection
 *
 * @property int $id
 * @property int $channel_id
 * @property int $reasons_of_rejection_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Channel $channel
 * @property-read \App\Domain\ProxyLead\Models\ReasonsOfRejection $reasonOrRejection
 * @method static Builder|ChannelReasonsOfRejection newModelQuery()
 * @method static Builder|ChannelReasonsOfRejection newQuery()
 * @method static Builder|ChannelReasonsOfRejection query()
 * @method static Builder|ChannelReasonsOfRejection whereChannelId($value)
 * @method static Builder|ChannelReasonsOfRejection whereCreatedAt($value)
 * @method static Builder|ChannelReasonsOfRejection whereId($value)
 * @method static Builder|ChannelReasonsOfRejection whereReasonsOfRejectionId($value)
 * @method static Builder|ChannelReasonsOfRejection whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Channel\Models\ChannelReasonsOfRejectionFactory factory(...$parameters)
 */
class ChannelReasonsOfRejection extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'reasons_of_rejection_id'];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function reasonOrRejection()
    {
        return $this->belongsTo(\App\Domain\ProxyLead\Models\ReasonsOfRejection::class, 'reasons_of_rejection_id');
    }
}
