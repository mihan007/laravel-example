<?php

namespace App\Domain\ProxyLead\Models;

use App\Domain\Company\Models\Company;
use App\Support\Interfaces\GoalCounterInterface;
use Eloquent;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * App\Domain\ProxyLead\Models\ProxyLeadGoalCounter
 *
 * @property int $id
 * @property int $company_id
 * @property int $target
 * @property int $not_target
 * @property int $not_confirmed
 * @property int $user_not_confirmed
 * @property int $admin_not_confirmed
 * @property string $for_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $lead_cost
 * @property-read Company $company
 * @method static Builder|ProxyLeadGoalCounter newModelQuery()
 * @method static Builder|ProxyLeadGoalCounter newQuery()
 * @method static Builder|ProxyLeadGoalCounter query()
 * @method static Builder|ProxyLeadGoalCounter whereAdminNotConfirmed($value)
 * @method static Builder|ProxyLeadGoalCounter whereCompanyId($value)
 * @method static Builder|ProxyLeadGoalCounter whereCreatedAt($value)
 * @method static Builder|ProxyLeadGoalCounter whereForDate($value)
 * @method static Builder|ProxyLeadGoalCounter whereId($value)
 * @method static Builder|ProxyLeadGoalCounter whereLeadCost($value)
 * @method static Builder|ProxyLeadGoalCounter whereNotConfirmed($value)
 * @method static Builder|ProxyLeadGoalCounter whereNotTarget($value)
 * @method static Builder|ProxyLeadGoalCounter whereTarget($value)
 * @method static Builder|ProxyLeadGoalCounter whereUpdatedAt($value)
 * @method static Builder|ProxyLeadGoalCounter whereUserNotConfirmed($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\ProxyLead\Models\ProxyLeadGoalCounterFactory factory(...$parameters)
 */
class ProxyLeadGoalCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'target',
        'not_target',
        'not_confirmed',
        'user_not_confirmed',
        'admin_not_confirmed',
        'for_date',
        'lead_cost',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'target' => 'integer',
        'not_target' => 'integer',
        'not_confirmed' => 'integer',
        'user_not_confirmed' => 'integer',
        'admin_not_confirmed' => 'integer',
    ];

    /**
     * Decrement from instance.
     *
     * @param GoalCounterInterface $instance
     * @param $statuses
     * @param int $amount
     * @return ProxyLeadGoalCounter
     */
    public static function decrementInstance(GoalCounterInterface $instance, $statuses, $amount = 1)
    {
        /** @var ProxyLeadGoalCounter $counter */
        $counter = self::firstOrCreate($instance->getGoalCounterData());

        self::changeStatusesInCounter($counter, 'decrement', $statuses, $amount);

        return $counter;
    }

    /**
     * Increment from instance.
     *
     * @param GoalCounterInterface $instance
     * @param $statuses
     * @param int $amount
     * @return ProxyLeadGoalCounter
     */
    public static function incrementInstance(GoalCounterInterface $instance, $statuses, $amount = 1)
    {
        /** @var ProxyLeadGoalCounter $counter */
        $counter = self::firstOrCreate($instance->getGoalCounterData());

        self::changeStatusesInCounter($counter, 'increment', $statuses, $amount);

        return $counter;
    }

    /**
     * Change status count in counter.
     *
     * @param ProxyLeadGoalCounter $counter
     * @param $method
     * @param $statuses
     * @param $amount
     * @return bool
     */
    private static function changeStatusesInCounter(self $counter, $method, $statuses, $amount)
    {
        if (is_string($statuses)) {
            $statuses = [$statuses];
        } elseif (is_array($statuses)) {
            $statuses = $statuses;
        } elseif (is_object($statuses) && is_a($statuses, Arrayable::class)) {
            $statuses = $statuses->toArray();
        } else {
            throw new InvalidArgumentException('Cann\'t convert statuses variable into valid type');
        }
        foreach (self::filterStatuses($statuses) as $status) {
            $counter->{$method}($status, $amount);
        }

        return true;
    }

    private static function filterStatuses(array $statuses)
    {
        $aliases = [
            'is_target' => 'target',
            'is_non_targeted' => 'not_target',
            'is_not_confirmed' => 'not_confirmed',
            'is_not_confirmed_user' => 'user_not_confirmed',
            'is_not_confirmed_admin' => 'admin_not_confirmed',
        ];

        $validArgs = [
            'target',
            'not_target',
            'not_confirmed',
            'user_not_confirmed',
            'admin_not_confirmed',
        ];

        foreach ($statuses as $index => $status) {
            $key = array_search($status, array_keys($aliases), true);

            if (false === $key) {
                continue;
            }

            $statuses[$index] = $aliases[$status];
        }

        $result = array_filter($statuses, function ($status) use ($validArgs) {
            return in_array($status, $validArgs);
        });

        return $result ?? [];
    }

    /**
     * Attach to company.
     *
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all leads for day.
     *
     * @return mixed
     */
    public function getAllLeads()
    {
        return $this->target + $this->not_target + $this->not_confirmed;
    }
}
