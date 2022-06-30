<?php

namespace App\Domain\Notification\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Domain\Notification\Models\EmailNotification
 *
 * @property int $id
 * @property int $company_id Attach company
 * @property string $email Store where need send message
 * @property string $type What kind of message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailNotification newModelQuery()
 * @method static Builder|EmailNotification newQuery()
 * @method static Builder|EmailNotification query()
 * @method static Builder|EmailNotification whereCompanyId($value)
 * @method static Builder|EmailNotification whereCreatedAt($value)
 * @method static Builder|EmailNotification whereEmail($value)
 * @method static Builder|EmailNotification whereId($value)
 * @method static Builder|EmailNotification whereType($value)
 * @method static Builder|EmailNotification whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static \Database\Factories\Domain\Notification\Models\EmailNotificationFactory factory(...$parameters)
 */
class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'type',
    ];

    public const YANDEX_DIRECT_TYPE = 'yandex_direct';
    public const ROISTAT_GOOGLE_TYPE = 'roistat_google';
    public const ROISTAT_BALANCE_TYPE = 'roistat_balance';
    public const MAIN_TYPE = 'main';
    //public const REPORT_TYPE = 'report';
    public const CUSTOMER_BALANCE = 'customer_balance';
    public const INFORMATION = 'information';
    public const PROXY_LEADS = 'proxy_leads';

    /**
     * Get list of allowed types.
     *
     * @return array
     */
    public static function getListOfAvailableTypes(): array
    {
        return [
            self::YANDEX_DIRECT_TYPE,
            self::ROISTAT_GOOGLE_TYPE,
            self::ROISTAT_BALANCE_TYPE,
            self::MAIN_TYPE,
            //self::REPORT_TYPE,
            self::CUSTOMER_BALANCE,
            self::PROXY_LEADS,
        ];
    }

    public static function getReadableTypeName($type)
    {
        $map = [
            self::YANDEX_DIRECT_TYPE => 'Яндекс Баланс',
            self::ROISTAT_GOOGLE_TYPE => 'Google Баланс',
            self::ROISTAT_BALANCE_TYPE => 'Roistat Баланс',
            self::MAIN_TYPE => 'Отчет администратора',
            self::CUSTOMER_BALANCE => 'Лидогенерация Баланс',
            self::PROXY_LEADS => 'Заявки от клиентов',
        ];

        return $map[$type] ?? false;
    }
}
