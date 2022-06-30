<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TotalDayLead
 *
 * @property int $id
 * @property int $amount amount of leads in special day
 * @property string|null $for_date Attach date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|TotalDayLead currentMonthLeads()
 * @method static Builder|TotalDayLead leadsOfHalfYear()
 * @method static Builder|TotalDayLead newModelQuery()
 * @method static Builder|TotalDayLead newQuery()
 * @method static Builder|TotalDayLead query()
 * @method static Builder|TotalDayLead whereAmount($value)
 * @method static Builder|TotalDayLead whereCreatedAt($value)
 * @method static Builder|TotalDayLead whereForDate($value)
 * @method static Builder|TotalDayLead whereId($value)
 * @method static Builder|TotalDayLead whereUpdatedAt($value)
 * @mixin Eloquent
 */
/**
 * App\Models\TotalDayLead
 *
 * @deprecated 
 * @property int $id
 * @property int $amount amount of leads in special day
 * @property string|null $for_date Attach date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|TotalDayLead currentMonthLeads()
 * @method static \Database\Factories\TotalDayLeadFactory factory(...$parameters)
 * @method static Builder|TotalDayLead leadsOfHalfYear()
 * @method static Builder|TotalDayLead newModelQuery()
 * @method static Builder|TotalDayLead newQuery()
 * @method static Builder|TotalDayLead query()
 * @method static Builder|TotalDayLead whereAmount($value)
 * @method static Builder|TotalDayLead whereCreatedAt($value)
 * @method static Builder|TotalDayLead whereForDate($value)
 * @method static Builder|TotalDayLead whereId($value)
 * @method static Builder|TotalDayLead whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TotalDayLead extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'for_date'];

    public function halfYearLeads()
    {
        $limitDate = new Carbon('-6 months');
        $limitDate->startOfMonth();

        return $this->where('created_at', '>=', $limitDate);
    }

    /**
     * Scope a query to only include leads for half year.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLeadsOfHalfYear($query)
    {
        return $query->whereBetween('for_date', [
            Carbon::parse('-6 months')->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ]);
    }

    /**
     * Scope a query to only include leads in current month.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentMonthLeads($query)
    {
        return $query->whereBetween('for_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ]);
    }
}
