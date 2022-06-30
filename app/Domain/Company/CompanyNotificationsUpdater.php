<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 02.10.2017
 * Time: 10:50.
 */

namespace App\Domain\Company;

use App\Domain\Notification\Models\EmailCompanyAdmin;
use App\Domain\Notification\Models\EmailNotificationSetting;
use Illuminate\Database\Eloquent\Model;

class CompanyNotificationsUpdater
{
    /**
     * @var \App\Domain\Company\Models\Company
     */
    private $company;

    /**
     * Store yandex direct notifications emails.
     *
     * @var array
     */
    private $yandexDirectNotifications = [];

    /**
     * Store roistat google notifications emails.
     *
     * @var array
     */
    private $roistatGoogleNotifications = [];

    /**
     * Store roistat balance notifications emails.
     *
     * @var array
     */
    private $roistatBalanceNotifications = [];

    /**
     * Store main notifications emails.
     *
     * @var array
     */
    private $mainNotifications = [];

    /**
     * Store main notifications emails.
     *
     * @var array
     */
    //private $reportNotifications = [];

    private $custometBalanceNotifications = [];

    /**
     * CompanyNotificationsUpdater constructor.
     * @param $company
     */
    public function __construct($company)
    {
        $this->company = $company;
    }

    /**
     * Update company notifications.
     *
     * @param $notifications - as accossiative multidimensional array ['id', 'email', 'type']
     * @return bool
     */
    public function update($notifications = [], $admins = [])
    {
        $removedEmails = EmailNotificationSetting::select('email', 'notification_type')
            ->where(['company_id' => $this->company->id])
            ->where('notification_type', '<>', 'main')
            ->distinct()
            ->get()
            ->pluck('email', 'notification_type')
            ->toArray();
        $emailsToNotify = [];

        foreach ($notifications as $notification) {
            if (! empty($notification['delete'])) {
                EmailNotificationSetting::where(['id' => $notification['delete']])->delete();
                continue;
            }
            $notificationEmail = $notification['email'];
            $notificationType = $notification['type'];
            $repeat = $notification['repeat'] ?? false;
            $updateNotificationResult = $this->updateOrCreateNotification($notificationEmail, $notificationType, $repeat);

            $removedEmails = array_diff_assoc($removedEmails, [$notificationType => $notificationEmail]);
            if (! empty($notification['id']) && $updateNotificationResult === false) {
                continue;
            }
            $emailsToNotify[$notificationEmail] = 1;
        }

        $this->removeUnusedNotifications($removedEmails);
        $this->changeAdmins($admins);
        $this->sendVerificationEmails(array_keys($emailsToNotify));

        return true;
    }

    /**
     * Remove all recipient that wasn't in notifications.
     *
     * @return bool
     */
    protected function removeUnusedNotifications($existingEmails)
    {
        foreach ($existingEmails as $type => $email) {
            EmailNotificationSetting::where(['company_id' => $this->company->id, 'email' => $email, 'notification_type' => $type])->delete();
            EmailCompanyAdmin::where(['company_id' => $this->company->id, 'email' => $email])->delete();
        }

        return true;
    }

    /**
     * @param $email
     * @param $type
     * @return bool|Model
     * @throws \App\Exceptions\EmailSubscriptionException
     */
    protected function updateOrCreateNotification($email, $type, $repeat)
    {
        return $this->company->updateOrCreateEmailNotification($type, $email, $repeat);
    }

    private function changeAdmins(array $admins)
    {
        $adminsBefore = EmailCompanyAdmin::select('email')
            ->where(['company_id' => $this->company->id])
            ->pluck('email')
            ->toArray();
        EmailCompanyAdmin::where(['company_id' => $this->company->id])->delete();
        $changed = false;
        foreach ($admins as $adminEmail) {
            EmailCompanyAdmin::create([
                'company_id' => $this->company->id,
                'email' => $adminEmail,
            ]);
            if (! in_array($adminEmail, $adminsBefore, true)) {
                $changed = true;
            }
            $adminsBefore = array_diff($adminsBefore, [$adminEmail]);
        }
        if ($changed || count($adminsBefore)) {
            $this->company->setNewManageKey();
        }
    }

    private function sendVerificationEmails($emailsToNotify)
    {
        foreach ($emailsToNotify as $email) {
            EmailNotificationSetting::requestToApproveEmail($email);
        }
    }
}
