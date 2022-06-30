<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Instruction
 *
 * @property int $id
 * @property string $title
 * @property string $path_to_view
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Instruction newModelQuery()
 * @method static Builder|Instruction newQuery()
 * @method static Builder|Instruction query()
 * @method static Builder|Instruction whereCreatedAt($value)
 * @method static Builder|Instruction whereId($value)
 * @method static Builder|Instruction wherePathToView($value)
 * @method static Builder|Instruction whereTitle($value)
 * @method static Builder|Instruction whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Instruction extends Model
{
    //
}
