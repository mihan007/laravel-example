<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Site
 *
 * @property int $id
 * @property int $company_id
 * @property string $url Site ulr
 * @property int $mobile_score Pagespeed mobile score
 * @property int $mobile_usability Pagespeed mobile usability
 * @property int $desktop_score Pagespeed desktop score
 * @property string $last_pagespeed_sync Last date of pagespeed synchronization
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Site newModelQuery()
 * @method static Builder|Site newQuery()
 * @method static Builder|Site query()
 * @method static Builder|Site whereCompanyId($value)
 * @method static Builder|Site whereCreatedAt($value)
 * @method static Builder|Site whereDesktopScore($value)
 * @method static Builder|Site whereId($value)
 * @method static Builder|Site whereLastPagespeedSync($value)
 * @method static Builder|Site whereMobileScore($value)
 * @method static Builder|Site whereMobileUsability($value)
 * @method static Builder|Site whereUpdatedAt($value)
 * @method static Builder|Site whereUrl($value)
 * @mixin Eloquent
 */
/**
 * App\Models\Site
 *
 * @deprecated 
 * @property int $id
 * @property int $company_id
 * @property string $url Site ulr
 * @property int $mobile_score Pagespeed mobile score
 * @property int $mobile_usability Pagespeed mobile usability
 * @property int $desktop_score Pagespeed desktop score
 * @property string $last_pagespeed_sync Last date of pagespeed synchronization
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Site newModelQuery()
 * @method static Builder|Site newQuery()
 * @method static Builder|Site query()
 * @method static Builder|Site whereCompanyId($value)
 * @method static Builder|Site whereCreatedAt($value)
 * @method static Builder|Site whereDesktopScore($value)
 * @method static Builder|Site whereId($value)
 * @method static Builder|Site whereLastPagespeedSync($value)
 * @method static Builder|Site whereMobileScore($value)
 * @method static Builder|Site whereMobileUsability($value)
 * @method static Builder|Site whereUpdatedAt($value)
 * @method static Builder|Site whereUrl($value)
 * @mixin Eloquent
 */
class Site extends Model
{
    protected $fillable = [
        'url',
        'mobile_score',
        'desktop_score',
        'last_pagespeed_sync',
    ];
}
