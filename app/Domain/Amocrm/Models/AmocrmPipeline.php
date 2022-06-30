<?php

namespace App\Domain\Amocrm\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Amocrm\Models\AmocrmPipeline
 *
 * @property int $id
 * @property int $company_amocrm_config_id
 * @property int $pipeline_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AmocrmPipeline newModelQuery()
 * @method static Builder|AmocrmPipeline newQuery()
 * @method static Builder|AmocrmPipeline query()
 * @method static Builder|AmocrmPipeline whereCompanyAmocrmConfigId($value)
 * @method static Builder|AmocrmPipeline whereCreatedAt($value)
 * @method static Builder|AmocrmPipeline whereId($value)
 * @method static Builder|AmocrmPipeline wherePipelineId($value)
 * @method static Builder|AmocrmPipeline whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AmocrmPipeline extends Model
{
    //
}
