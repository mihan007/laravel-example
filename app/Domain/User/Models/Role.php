<?php

namespace App\Domain\User\Models;

use App\Domain\User\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Shanmuga\LaravelEntrust\Models\EntrustRole;

/**
 * App\Domain\User\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static Builder|Role query()
 * @method static Builder|Role whereCreatedAt($value)
 * @method static Builder|Role whereDescription($value)
 * @method static Builder|Role whereDisplayName($value)
 * @method static Builder|Role whereId($value)
 * @method static Builder|Role whereName($value)
 * @method static Builder|Role whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|\App\Domain\User\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @method static \Database\Factories\Domain\User\Models\RoleFactory factory(...$parameters)
 */
class Role extends EntrustRole
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'display_name', 'description'];

    public function users()
    {
        return $this->belongsToMany(
            Config::get('auth.providers.users.model'),
            Config::get('entrust.role_user_table'),
            Config::get('entrust.role_foreign_key'),
            Config::get('entrust.user_foreign_key')
        );
    }

    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = is_null($description) ? '' : $description;
    }
}
