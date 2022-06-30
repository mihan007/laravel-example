<?php

namespace App\Support\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Support\Models\ScheduleTaskLog
 *
 * @property int $id
 * @property string $task_id
 * @property string $name task name
 * @property string|null $started_at
 * @property string|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ScheduleTaskLog newModelQuery()
 * @method static Builder|ScheduleTaskLog newQuery()
 * @method static Builder|ScheduleTaskLog query()
 * @method static Builder|ScheduleTaskLog whereCreatedAt($value)
 * @method static Builder|ScheduleTaskLog whereFinishedAt($value)
 * @method static Builder|ScheduleTaskLog whereId($value)
 * @method static Builder|ScheduleTaskLog whereName($value)
 * @method static Builder|ScheduleTaskLog whereStartedAt($value)
 * @method static Builder|ScheduleTaskLog whereTaskId($value)
 * @method static Builder|ScheduleTaskLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ScheduleTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'name',
        'started_at',
        'finished_at',
    ];
}
