<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 04.04.2018
 * Time: 11:15.
 */

namespace App\Domain\Company\Notification;

use App\Domain\Notification\AdminSendReportVerification;
use App\Domain\Notification\UserSendReportVerification;
use Illuminate\Notifications\DatabaseNotification;

class NotificationTypeParser
{
    /**
     * @var DatabaseNotification
     */
    private $notification;

    /**
     * Store types of info notifications.
     *
     * @var array
     */
    private $infoTypes = [
        UserSendReportVerification::class,
        AdminSendReportVerification::class,
    ];

    /**
     * NotificationTypeParser constructor.
     * @param DatabaseNotification $notification
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get type of notification.
     *
     * @return string
     */
    public function getType()
    {
        return in_array($this->notification->type, $this->infoTypes, true) ? 'info' : false;
    }
}
