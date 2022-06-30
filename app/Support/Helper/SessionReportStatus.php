<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.09.2018
 * Time: 14:21.
 */

namespace App\Support\Helper;

use MyCLabs\Enum\Enum;

/**
 * Class SessionReportStatus.
 * @method static SessionReportStatus SUCCESS
 * @method static SessionReportStatus ERROR
 */
class SessionReportStatus extends Enum
{
    public const __default = self::SUCCESS;

    public const SUCCESS = 'success';
    public const ERROR = 'error';
}
