<?php

namespace App\Domain\Amocrm\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Amocrm\Models\AmocrmLead
 *
 * @property int $id
 * @property int $company_amocrm_config_id
 * @property int $lead_id
 * @property string $name
 * @property int $status_id
 * @property int|null $old_status_id
 * @property int|null $price
 * @property int $responsible_user_id
 * @property string $last_modified
 * @property int $modified_user_id
 * @property int $created_user_id
 * @property string $date_create
 * @property int $pipeline_id
 * @property int $account_id
 * @property string|null $target_set_at When lead set in target status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|AmocrmLead newModelQuery()
 * @method static Builder|AmocrmLead newQuery()
 * @method static Builder|AmocrmLead query()
 * @method static Builder|AmocrmLead whereAccountId($value)
 * @method static Builder|AmocrmLead whereCompanyAmocrmConfigId($value)
 * @method static Builder|AmocrmLead whereCreatedAt($value)
 * @method static Builder|AmocrmLead whereCreatedUserId($value)
 * @method static Builder|AmocrmLead whereDateCreate($value)
 * @method static Builder|AmocrmLead whereId($value)
 * @method static Builder|AmocrmLead whereLastModified($value)
 * @method static Builder|AmocrmLead whereLeadId($value)
 * @method static Builder|AmocrmLead whereModifiedUserId($value)
 * @method static Builder|AmocrmLead whereName($value)
 * @method static Builder|AmocrmLead whereOldStatusId($value)
 * @method static Builder|AmocrmLead wherePipelineId($value)
 * @method static Builder|AmocrmLead wherePrice($value)
 * @method static Builder|AmocrmLead whereResponsibleUserId($value)
 * @method static Builder|AmocrmLead whereStatusId($value)
 * @method static Builder|AmocrmLead whereTargetSetAt($value)
 * @method static Builder|AmocrmLead whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AmocrmLead extends Model
{
    protected $fillable = [
        'company_amocrm_config_id',
        'lead_id',
        'name',
        'status_id',
        'old_status_id',
        'price',
        'responsible_user_id',
        'last_modified',
        'modified_user_id',
        'created_user_id',
        'date_create',
        'pipeline_id',
        'account_id',
        'target_set_at',
    ];
}
