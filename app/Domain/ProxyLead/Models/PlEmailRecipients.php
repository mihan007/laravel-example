<?php

namespace App\Domain\ProxyLead\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\PlEmailRecipients
 *
 * @property int $id
 * @property int $proxy_lead_setting_id
 * @property string $email
 * @property string|null $type Type of email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PlEmailRecipients admins()
 * @method static Builder|PlEmailRecipients newModelQuery()
 * @method static Builder|PlEmailRecipients newQuery()
 * @method static Builder|PlEmailRecipients query()
 * @method static Builder|PlEmailRecipients receivers()
 * @method static Builder|PlEmailRecipients whereCreatedAt($value)
 * @method static Builder|PlEmailRecipients whereEmail($value)
 * @method static Builder|PlEmailRecipients whereId($value)
 * @method static Builder|PlEmailRecipients whereProxyLeadSettingId($value)
 * @method static Builder|PlEmailRecipients whereType($value)
 * @method static Builder|PlEmailRecipients whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\PlEmailRecipientsFactory factory(...$parameters)
 */
class PlEmailRecipients extends Model
{
    use HasFactory;

    public const TYPE_RECEIVER = 'receiver';
    public const TYPE_ADMIN = 'admin';

    protected $fillable = [
        'email',
        'type',
    ];

    /**
     * Return only emails with type 'admin'.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeAdmins(Builder $builder)
    {
        return $builder->where('type', self::TYPE_ADMIN);
    }

    /**
     * Return only emails with type 'receiver'.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeReceivers(Builder $builder)
    {
        return $builder->where('type', self::TYPE_RECEIVER);
    }
}
