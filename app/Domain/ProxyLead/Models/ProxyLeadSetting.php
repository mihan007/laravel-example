<?php

namespace App\Domain\ProxyLead\Models;

use App\Domain\Company\Models\Company;
use App\Support\Interfaces\Approvable;
use App\Support\Interfaces\ReportLeads;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Domain\ProxyLead\Models\ProxyLeadSetting
 *
 * @property int $id
 * @property int $company_id
 * @property string $public_key
 * @property string|null $bitrix_webhook Bitrix integration webhook
 * @property mixed|null $match_phone
 * @property mixed|null $match_name
 * @property mixed|null $match_info
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|PlApprovedReport[] $approvedReports
 * @property-read int|null $approved_reports_count
 * @property-read Company $company
 * @property-read Collection|PlEmailRecipients[] $emailRecipients
 * @property-read int|null $email_recipients_count
 * @property-read Collection|ProxyLead[] $proxyLeads
 * @property-read int|null $proxy_leads_count
 * @method static Builder|ProxyLeadSetting newModelQuery()
 * @method static Builder|ProxyLeadSetting newQuery()
 * @method static Builder|ProxyLeadSetting query()
 * @method static Builder|ProxyLeadSetting whereBitrixWebhook($value)
 * @method static Builder|ProxyLeadSetting whereCompanyId($value)
 * @method static Builder|ProxyLeadSetting whereCreatedAt($value)
 * @method static Builder|ProxyLeadSetting whereId($value)
 * @method static Builder|ProxyLeadSetting whereMatchInfo($value)
 * @method static Builder|ProxyLeadSetting whereMatchName($value)
 * @method static Builder|ProxyLeadSetting whereMatchPhone($value)
 * @method static Builder|ProxyLeadSetting wherePublicKey($value)
 * @method static Builder|ProxyLeadSetting whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\ProxyLeadSettingFactory factory(...$parameters)
 */
class ProxyLeadSetting extends Model implements ReportLeads, Approvable
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'public_key',
        'bitrix_webhook',
        'match_phone',
        'match_name',
        'match_info',
    ];

    public function approvedReports()
    {
        return $this->hasMany(PlApprovedReport::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function emailRecipients()
    {
        return $this->hasMany(PlEmailRecipients::class);
    }

    /**
     * Attach to proxy leads.
     */
    public function proxyLeads()
    {
        return $this->hasMany(ProxyLead::class);
    }

    public function leads()
    {
        return $this->proxyLeads();
    }

    public function approves()
    {
        return $this->approvedReports();
    }
}
