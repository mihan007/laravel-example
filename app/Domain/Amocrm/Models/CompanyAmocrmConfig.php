<?php

namespace App\Domain\Amocrm\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Amocrm\Models\CompanyAmocrmConfig
 *
 * @property int $id
 * @property int $company_id
 * @property string $subdomain Amocrm subdomain
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|AmocrmPipeline[] $pipelines
 * @property-read int|null $pipelines_count
 * @property-read Collection|AmocrmStatus[] $statuses
 * @property-read int|null $statuses_count
 * @method static Builder|CompanyAmocrmConfig newModelQuery()
 * @method static Builder|CompanyAmocrmConfig newQuery()
 * @method static Builder|CompanyAmocrmConfig query()
 * @method static Builder|CompanyAmocrmConfig whereCompanyId($value)
 * @method static Builder|CompanyAmocrmConfig whereCreatedAt($value)
 * @method static Builder|CompanyAmocrmConfig whereId($value)
 * @method static Builder|CompanyAmocrmConfig whereSubdomain($value)
 * @method static Builder|CompanyAmocrmConfig whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CompanyAmocrmConfig extends Model
{
    /**
     * Config pipelines.
     *
     * @return HasMany
     */
    public function pipelines()
    {
        return $this->hasMany(\App\Domain\Amocrm\Models\AmocrmPipeline::class);
    }

    /**
     * Config statuses.
     *
     * @return HasMany
     */
    public function statuses()
    {
        return $this->hasMany(\App\Domain\Amocrm\Models\AmocrmStatus::class);
    }
}
