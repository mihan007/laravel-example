<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 12.04.2018
 * Time: 9:44.
 */

namespace App\Domain\Company\Notification;

use App\Domain\Notification\AdminSendReportVerification;
use App\Domain\Notification\UserSendReportVerification;
use Illuminate\Notifications\DatabaseNotification;

class NotificationReadableNameParser
{
    private $notifications = [
        UserSendReportVerification::class => 'Сотрудник отправил отчет на согласование',
        AdminSendReportVerification::class => 'Администратор отправил отчет на согласование',
    ];
    /**
     * @var DatabaseNotification
     */
    private $notification;

    /**
     * NotificationReadableNameParser constructor.
     * @param DatabaseNotification $notification
     */
    public function __construct(DatabaseNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get readable name.
     *
     * @return bool
     */
    public function getName()
    {
        $result = array_filter($this->notifications, function ($key) {
            return $this->notification->type === $key;
        }, ARRAY_FILTER_USE_KEY);

        return empty($result) ? false : array_values($result)[0];
    }
}
