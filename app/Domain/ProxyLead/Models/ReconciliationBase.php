<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 29.08.2018
 * Time: 9:22.
 */

namespace App\Domain\ProxyLead\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ReconciliationBase extends Model
{
    public const USER_TYPE = 'user';
    public const ADMIN_TYPE = 'admin';

    /**
     * Get available types.
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::USER_TYPE,
            self::ADMIN_TYPE,
        ];
    }
}
