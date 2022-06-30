<?php

namespace App\Domain\Notification\Mail;

use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Illuminate\Mail\Mailable;

abstract class SubscriptionMailable extends Mailable
{
    abstract public function getNotificationType();

    abstract public function getCompanyId();

    public function isMailPossible($email): bool
    {
        if (! $email) {
            return false;
        }

        if ($this->getNotificationType() === true) {
            return true;
        }

        if (! in_array($this->getNotificationType(), EmailNotification::getListOfAvailableTypes(), true)) {
            return false;
        }

        return EmailNotificationSetting::where([
                'company_id' => $this->getCompanyId(),
                'email' => $email,
                'notification_type' => $this->getNotificationType(),
                'status' => EmailNotificationSetting::STATUS_APPROVED,
            ])->count() > 0;
    }

    protected function setAddress($address, $name = null, $property = 'to')
    {
        if ($this->isMailPossible($address)) {
            return parent::setAddress($address, $name, $property);
        }

        return $this;
    }
}
