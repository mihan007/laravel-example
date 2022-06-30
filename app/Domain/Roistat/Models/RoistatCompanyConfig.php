<?php

namespace App\Domain\Roistat\Models;

use App\Domain\Company\Models\Company;
use App\Models;
use App\Models\ApprovedReport;
use App\Support\Interfaces\Approvable;
use App\Support\Interfaces\ReportLeads;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Domain\Roistat\Models\RoistatCompanyConfig
 *
 * @property int $id
 * @property int $company_id
 * @property string $roistat_project_id
 * @property string $api_key
 * @property string $timezone
 * @property float $google_limit_amount Minimum google amount
 * @property string|null $max_lead_price Maximum lead price
 * @property string|null $max_costs Maximum costs for yesterday
 * @property int|null $avito_visits_limit Avito minimum visits limit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|RoistatAnalytic[] $analytics
 * @property-read int|null $analytics_count
 * @property-read Collection|ApprovedReport[] $approvedReports
 * @property-read int|null $approved_reports_count
 * @property-read Collection|RcAvitoAnalytic[] $avitoAnalytics
 * @property-read int|null $avito_analytics_count
 * @property-read RcAvitoAnalytic|null $avitoYesterdayAnalytic
 * @property-read Company $company
 * @property-read Collection|RoistatAnalyticsDimensionValue[] $dimensionsValues
 * @property-read int|null $dimensions_values_count
 * @property-read mixed $php_timezone
 * @property-read Collection|RoistatGoogleAnalytic[] $googleAnalytics
 * @property-read int|null $google_analytics_count
 * @property-read Collection|RoistatProxyLead[] $leads
 * @property-read int|null $leads_count
 * @property-read RoistatAnalytic|null $mostRecentAnalytic
 * @property-read Collection|RoistatProxyLeadsReport[] $reportLeads
 * @property-read int|null $report_leads_count
 * @property-read Collection|RoistatReconciliation[] $roistatReconciliations
 * @property-read int|null $roistat_reconciliations_count
 * @property-read RoistatAnalytic|null $yesterdayAnalytic
 * @method static Builder|RoistatCompanyConfig newModelQuery()
 * @method static Builder|RoistatCompanyConfig newQuery()
 * @method static Builder|RoistatCompanyConfig query()
 * @method static Builder|RoistatCompanyConfig whereApiKey($value)
 * @method static Builder|RoistatCompanyConfig whereAvitoVisitsLimit($value)
 * @method static Builder|RoistatCompanyConfig whereCompanyId($value)
 * @method static Builder|RoistatCompanyConfig whereCreatedAt($value)
 * @method static Builder|RoistatCompanyConfig whereGoogleLimitAmount($value)
 * @method static Builder|RoistatCompanyConfig whereId($value)
 * @method static Builder|RoistatCompanyConfig whereMaxCosts($value)
 * @method static Builder|RoistatCompanyConfig whereMaxLeadPrice($value)
 * @method static Builder|RoistatCompanyConfig whereRoistatProjectId($value)
 * @method static Builder|RoistatCompanyConfig whereTimezone($value)
 * @method static Builder|RoistatCompanyConfig whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Roistat\Models\RoistatCompanyConfigFactory factory(...$parameters)
 */
class RoistatCompanyConfig extends Model implements ReportLeads, Approvable
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'roistat_project_id',
        'api_key',
        'created_at',
        'updated_at',
        'timezone',
        'google_limit_amount',
        'max_lead_price',
        'max_costs',
        'avito_visits_limit',
    ];

    protected $casts = [
        'google_limit_amount' => 'double',
        'max_lead_price' => 'integer',
        'max_costs' => 'integer',
    ];

    public function getPhpTimezoneAttribute()
    {
        $timezones = [
            '+0200' => 'Europe/Kaliningrad',
            '+0300' => 'Europe/Moscow',
            '+0400' => 'Europe/Samara',
            '+0500' => 'Asia/Yekaterinburg',
            '+0600' => 'Asia/Omsk',
            '+0700' => 'Asia/Krasnoyarsk',
            '+0800' => 'Asia/Irkutsk',
            '+0900' => 'Asia/Yakutsk',
            '+1000' => 'Asia/Vladivostok',
            '+1100' => 'Asia/Srednekolymsk',
        ];

        return $timezones[$this->timezone];
    }

    /**
     * @return HasMany
     */
    public function roistatReconciliations(): HasMany
    {
        return $this->hasMany(RoistatReconciliation::class);
    }

    /**
     * Attach approvedReports.
     *
     * @return HasMany
     */
    public function approvedReports(): HasMany
    {
        return $this->hasMany(Models\ApprovedReport::class);
    }

    /**
     * Get the company that owns the Roistat Config.
     */
    public function company()
    {
        return $this->belongsTo(\App\Domain\Company\Models\Company::class);
    }

    public function dimensionsValues()
    {
        return $this->hasMany(\App\Domain\Roistat\Models\RoistatAnalyticsDimensionValue::class);
    }

    /**
     * @return HasMany
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(RoistatAnalytic::class);
    }

    /**
     * @return HasMany
     */
    public function avitoAnalytics(): HasMany
    {
        return $this->hasMany(RcAvitoAnalytic::class);
    }

    public function mostRecentAnalytic()
    {
        return $this->hasOne(RoistatAnalytic::class)
            ->where('for_date', '=', Carbon::yesterday()->format('Y-m-d'));
    }

    public function yesterdayAnalytic()
    {
        return $this->hasOne(\App\Domain\Roistat\Models\RoistatAnalytic::class)->where(
            'for_date',
            '=',
            Carbon::yesterday()->format('Y-m-d')
        );
    }

    public function googleAnalytics()
    {
        return $this->hasMany(RoistatGoogleAnalytic::class);
    }

    /**
     * @return $this
     */
    public function mostRecentGoogleAnalytic()
    {
        return $this->googleAnalytics()->orderBy('created_at', 'desc')->take(1);
    }

    public function avitoYesterdayAnalytic()
    {
        return $this->hasOne(\App\Domain\Roistat\Models\RcAvitoAnalytic::class)->where('for_date', '=', Carbon::yesterday()->format('Y-m-d'));
    }

    /**
     * Config has many proxy leads in report.
     *
     * @return HasMany
     */
    public function reportLeads()
    {
        return $this->hasMany(\App\Domain\Roistat\Models\RoistatProxyLeadsReport::class);
    }

    public function leads()
    {
        return $this->hasManyThrough(RoistatProxyLead::class, Company::class, 'id', 'company_id', 'company_id');
    }

    public function approves()
    {
        return $this->approvedReports();
    }
}
