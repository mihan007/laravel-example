<?php

namespace App\Domain\Account\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\Domain\Account\Models\AboutCompany
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $u_name
 * @property string|null $inn
 * @property string|null $index
 * @property string|null $city
 * @property string|null $address
 * @property string|null $head
 * @property string|null $accountant
 * @property string|null $seal_img
 * @property string|null $head_sign
 * @property string|null $accountant_sign
 * @property int $account_id
 * @method static Builder|AboutCompany newModelQuery()
 * @method static Builder|AboutCompany newQuery()
 * @method static Builder|AboutCompany query()
 * @method static Builder|AboutCompany whereAccountId($value)
 * @method static Builder|AboutCompany whereAccountant($value)
 * @method static Builder|AboutCompany whereAccountantSign($value)
 * @method static Builder|AboutCompany whereAddress($value)
 * @method static Builder|AboutCompany whereCity($value)
 * @method static Builder|AboutCompany whereCreatedAt($value)
 * @method static Builder|AboutCompany whereHead($value)
 * @method static Builder|AboutCompany whereHeadSign($value)
 * @method static Builder|AboutCompany whereId($value)
 * @method static Builder|AboutCompany whereIndex($value)
 * @method static Builder|AboutCompany whereInn($value)
 * @method static Builder|AboutCompany whereName($value)
 * @method static Builder|AboutCompany whereSealImg($value)
 * @method static Builder|AboutCompany whereUName($value)
 * @method static Builder|AboutCompany whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AboutCompany extends Model
{
    protected $fillable = [
        'created_at',
        'updated_at',
        'name',
        'u_name',
        'inn',
        'index',
        'city',
        'address',
        'head',
        'accountant',
        'seal_img',
        'head_sign',
        'accountant_sign',
        'account_id',
    ];

    public function saveImage($to, $path, $extension)
    {
        $img = new File($path);

        if (! $to) {
            if (strtoupper($extension) !== 'PNG') {
                return false;
            }
            $to = '/logo_'.time().'_'.Str::random(10).'.'.$extension;
        }

        Storage::disk('public')->put($to, File::get($path));

        return $to;
    }
}
