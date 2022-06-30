<?php

namespace App\Domain\Amocrm\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Amocrm\Models\AmocrmStatus
 *
 * @property int $id
 * @property int $company_amocrm_config_id
 * @property int $status_id Amocrm status id
 * @property string|null $type Status type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AmocrmStatus entry()
 * @method static Builder|AmocrmStatus newModelQuery()
 * @method static Builder|AmocrmStatus newQuery()
 * @method static Builder|AmocrmStatus query()
 * @method static Builder|AmocrmStatus whereCompanyAmocrmConfigId($value)
 * @method static Builder|AmocrmStatus whereCreatedAt($value)
 * @method static Builder|AmocrmStatus whereId($value)
 * @method static Builder|AmocrmStatus whereStatusId($value)
 * @method static Builder|AmocrmStatus whereType($value)
 * @method static Builder|AmocrmStatus whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AmocrmStatus extends Model
{
    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeEntry($query)
    {
        return $query->where('type', '=', 'entry');
    }
}
