<?php

namespace App\Cabinet\Client\Subscription\Controllers;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailCompanyAdmin;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Exceptions\EmailSubscriptionException;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;

class SubscriptionController extends Controller
{
    public function unsubscribe($key)
    {
        /** @var \App\Domain\Notification\Models\EmailNotificationSetting $emailNotificationSetting */
        $emailNotificationSetting = EmailNotificationSetting::whereDisableLinkKey($key)->firstOrFail();
        $emailManageLink = EmailManageLink::whereEmail($emailNotificationSetting->email)->firstOrFail();

        return redirect()->route(
            'subscription.manage',
            [
                'key' => $emailManageLink->notification_settings_key,
                'unsubscribe_type' => 'unsubscribe',
                'unsubscribe_key' => $key,
            ]
        );
    }

    public function unsubscribeAll($key)
    {
        $emailManageLink = EmailManageLink::whereDisableAllKey($key)->firstOrFail();

        return redirect()->route(
            'subscription.manage',
            [
                'key' => $emailManageLink->notification_settings_key,
                'unsubscribe_type' => 'unsubscribeAll',
                'unsubscribe_key' => $key,
            ]
        );
    }

    public function ajaxUnsubscribe($key)
    {
        /** @var EmailNotificationSetting $emailNotificationSetting */
        $emailNotificationSetting = EmailNotificationSetting::whereDisableLinkKey($key)->firstOrFail();
        $emailManageLink = EmailManageLink::whereEmail($emailNotificationSetting->email)->firstOrFail();
        $emailNotificationSetting->setStatus(EmailNotificationSetting::STATUS_DISABLED);
        request()->session()->flash(
            'alert-success',
            'Вы успешно отписаны от рассылки "'
            .EmailNotification::getReadableTypeName($emailNotificationSetting->notification_type).'"'
            .(($emailNotificationSetting->company) ? " для компании \"{$emailNotificationSetting->company->name}\"" : '')
        );

        return redirect()->route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
    }

    public function ajaxUnsubscribeAll($key)
    {
        $emailManageLink = EmailManageLink::whereDisableAllKey($key)->firstOrFail();
        $emailNotificationSettings = EmailNotificationSetting::whereEmail($emailManageLink->email)->get();
        /** @var EmailNotificationSetting $emailNotificationSetting */
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $emailNotificationSetting->setStatus(EmailNotificationSetting::STATUS_DISABLED);
        }
        request()->session()->flash('alert-success', 'Вы успешно отписаны от всех рассылок');

        return redirect()->route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
    }

    public function subscribeAllPending($key)
    {
        $emailManageLink = EmailManageLink::whereApproveAllPendingKey($key)->firstOrFail();
        $emailNotificationSettings = EmailNotificationSetting::whereEmail($emailManageLink->email)->where(
            'status',
            '=',
            EmailNotificationSetting::STATUS_PENDING
        )->get();
        $subscriptionList = [];
        /** @var \App\Domain\Notification\Models\EmailNotificationSetting $emailNotificationSetting */
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $emailNotificationSetting->setStatus(EmailNotificationSetting::STATUS_APPROVED);
            $subscriptionList[] = EmailNotification::getReadableTypeName($emailNotificationSetting->notification_type);
        }
        if (count($subscriptionList) > 0) {
            request()->session()->flash(
                'alert-success',
                'Вы успешно подписались на рассылки '.implode(', ', $subscriptionList)
            );
        }

        return redirect()->route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
    }

    public function manage($key)
    {
        list($emailManageLink, $data) = $this->handleSave($key);

        list($result, $notifications) = $this->buildNotifications($emailManageLink);
        $adminAtCompany = EmailCompanyAdmin::whereEmail($emailManageLink->email)->with('company')->get();
        $companyAdmin = [];
        foreach ($adminAtCompany as $item) {
            if (! $item->company) {
                continue;
            }
            $companyAdmin[$item->company->name] = route(
                'subscription.company.admin',
                ['key' => $item->company->manage_subscription_key]
            );
        }
        $data = [
            'email' => $emailManageLink->email,
            'emailNotificationSettings' => $result,
            'companyAdmin' => $companyAdmin,
        ];

        return view('pages.subscription.manage', $data);
    }

    public function manageCompany($key)
    {
        $company = Company::where(['manage_subscription_key' => $key])->firstOrFail();
        $emailNotificationSettings = EmailNotificationSetting::select('email')->distinct()->where(
            ['company_id' => $company->id]
        )->get();
        $result = [];
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $email = $emailNotificationSetting->email;
            $emailManageLink = EmailManageLink::whereEmail($email)->first();
            if (! $emailManageLink) {
                continue;
            }
            [$item, $notifications] = $this->buildNotifications($emailManageLink, $company);
            $result[$email] = $item;
            $added = $this->setupNonHandledNotifications($notifications, $emailManageLink);
            if ($added) {
                [$item, $notifications] = $this->buildNotifications($emailManageLink, $company);
                $result[$email] = $item;
            }
        }
        $admins = EmailCompanyAdmin::select('email')
            ->where(['company_id' => $company->id])
            ->pluck('email')
            ->toArray();
        $data = [
            'emailNotifications' => $result,
            'company' => $company,
            'admins' => $admins,
            'changeAdminUrl' => route('subscription.company.changeAdmin', ['key' => $company->manage_subscription_key]),
        ];

        return view('pages.subscription.admin', $data);
    }

    public function saveCompany($key, Request $request)
    {
        $company = Company::where(['manage_subscription_key' => $key])->firstOrFail();
        $data = Input::all();
        if (count($data) > 0) {
            if ($data['newEmail']) {
                $this->validate($request, ['newEmail' => 'email']);
            }

            foreach ($data['notifications'] as $email => $emailNotificationIds) {
                $changed = false;
                $currentEmailNotifications = EmailNotificationSetting::where(
                    [
                        'email' => $email,
                        'company_id' => $company->id,
                    ]
                )->get();
                foreach ($currentEmailNotifications as $currentEmailNotification) {
                    if (in_array($currentEmailNotification->id, $emailNotificationIds)) {
                        if (($currentEmailNotification->status !== EmailNotificationSetting::STATUS_APPROVED)
                            && $currentEmailNotification->status !== EmailNotificationSetting::STATUS_PENDING) {
                            $currentEmailNotification->status = EmailNotificationSetting::STATUS_PENDING;
                            $currentEmailNotification->save();
                            $changed = true;
                        }
                    } else {
                        if ($currentEmailNotification->status !== EmailNotificationSetting::STATUS_DISABLED) {
                            $currentEmailNotification->status = EmailNotificationSetting::STATUS_DISABLED;
                            $currentEmailNotification->save();
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    EmailNotificationSetting::requestToApproveEmail($email);
                }
            }
            if ($data['newEmail']) {
                $newEmail = $data['newEmail'];
                $isValid = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
                if (! $isValid) {
                    request()->session()->flash(
                        'alert-warning',
                        "{$newEmail} не добавлен, так как это некорректный email"
                    );
                    unset($newEmail);
                } else {
                    EmailManageLink::init($newEmail);
                    $availableNotificationTypes = EmailNotification::getListOfAvailableTypes();
                    foreach ($availableNotificationTypes as $notificationType) {
                        EmailNotificationSetting::setDisabled($newEmail, $notificationType, $company->id);
                    }
                }
            }
            $message = 'Вы успешно внесли изменения в настройки'.(isset($newEmail) ? ' и добавили ящик '.$newEmail : '');
            request()->session()->flash('alert-success', $message);
        }

        return redirect()->route('subscription.company.admin', ['key' => $company->manage_subscription_key]);
    }

    public function changeAdmin($key, \Request $request)
    {
        $company = Company::where(['manage_subscription_key' => $key])->firstOrFail();
        $data = Input::all();
        $adminEmail = $data['adminEmail'];
        if ($data['action'] === 'assign') {
            EmailCompanyAdmin::firstOrCreate(['company_id' => $company->id, 'email' => $adminEmail]);
            request()->session()->flash('alert-success', "{$adminEmail} получил роль администратора подписки компании");
        }
        if ($data['action'] === 'remove') {
            EmailCompanyAdmin::where(['company_id' => $company->id, 'email' => $adminEmail])->delete();
            request()->session()->flash('alert-success', "{$adminEmail} больше не администратор подписки компании");
        }

        return redirect()->route('subscription.company.admin', ['key' => $company->manage_subscription_key]);
    }

    /**
     * @param array $notifications
     * @param $emailManageLink
     * @return bool
     * @throws EmailSubscriptionException
     */
    private function setupNonHandledNotifications(array $notifications, $emailManageLink): bool
    {
        $changed = false;
        $availableNotificationSettings = EmailNotification::getListOfAvailableTypes();
        foreach ($notifications as $companyId => $notificationTypes) {
            $nonHandledNotifications = $availableNotificationSettings;
            foreach ($notificationTypes as $notificationType) {
                $nonHandledNotifications = array_diff($nonHandledNotifications, [$notificationType]);
            }

            //создает лишние рассылки, поэтому удалена
            //foreach ($nonHandledNotifications as $nonHandledNotification) {
                //EmailNotificationSetting::setDisabled($emailManageLink->email, $nonHandledNotification, $companyId);
                //$changed = true;
            //}
        }

        return $changed;
    }

    /**
     * @param $emailManageLink
     * @param \App\Domain\Company\Models\Company $company
     * @return array
     */
    private function buildNotifications($emailManageLink, $company = null): array
    {
        if ($company) {
            $emailNotificationSettings = EmailNotificationSetting::where(
                [
                    'email' => $emailManageLink->email,
                    'company_id' => $company->id,
                ]
            )->get();
        } else {
            $emailNotificationSettings = EmailNotificationSetting::whereEmail($emailManageLink->email)->get();
        }
        $result = [];
        $notifications = [];
        foreach ($emailNotificationSettings as $emailNotificationSetting) {
            $companyName = $emailNotificationSetting->company ? $emailNotificationSetting->company->name : false;
            if (! $companyName) {
                continue;
            }
            if ($company && ($company->name !== $companyName)) {
                continue;
            }
            $companyId = $emailNotificationSetting->company ? $emailNotificationSetting->company->id : false;
            if (! isset($result[$companyName])) {
                $result[$companyName] = [];
                $notifications[$companyId] = [];
            }
            $result[$companyName][] = $emailNotificationSetting;
            $notifications[$companyId][] = $emailNotificationSetting->notification_type;
        }

        return [$result, $notifications];
    }

    /**
     * @param $key
     * @return array
     * @throws EmailSubscriptionException
     */
    private function handleSave($key): array
    {
        $emailManageLink = EmailManageLink::whereNotificationSettingsKey($key)->firstOrFail();
        $data = Input::all();

        if (count($data) > 0 && ! isset($data['unsubscribe_type'])) {
            $subscriptionList = [];

            if (isset($data['notifications'])) {
                foreach ($data['notifications'] as $notificationId) {
                    $emailNotificationSetting = EmailNotificationSetting::find($notificationId);
                    if ($emailNotificationSetting && $emailNotificationSetting->email == $emailManageLink->email) {
                        if ($emailNotificationSetting->status != EmailNotificationSetting::STATUS_APPROVED) {
                            $emailNotificationSetting->setStatus(EmailNotificationSetting::STATUS_APPROVED);
                        }
                        $subscriptionList[] = $emailNotificationSetting->company->name.': '.EmailNotification::getReadableTypeName(
                                $emailNotificationSetting->notification_type
                            );
                    }
                }

                foreach (
                    EmailNotificationSetting::whereEmail($emailManageLink->email)->whereNotIn(
                        'id',
                        $data['notifications']
                    )->get() as $notification
                ) {
                    if ($notification->status != EmailNotificationSetting::STATUS_DISABLED) {
                        $notification->setStatus(EmailNotificationSetting::STATUS_DISABLED);
                    }
                    $notification->save();
                }
            } else {
                foreach (EmailNotificationSetting::whereEmail($emailManageLink->email)->get() as $notification) {
                    if ($notification->status != EmailNotificationSetting::STATUS_DISABLED) {
                        $notification->setStatus(EmailNotificationSetting::STATUS_DISABLED);
                    }
                    $notification->save();
                }
            }

            if (count($subscriptionList) > 0) {
                request()->session()->flash(
                    'alert-success',
                    'Вы успешно подписались на рассылки: '.implode(', ', $subscriptionList)
                );
            } else {
                request()->session()->flash('alert-success', 'Вы успешно отписаны от всех рассылок');
            }
        }

        return [$emailManageLink, $data];
    }

    public function ajaxGetUnsubscribeDate($unsubscribe_type, $key)
    {
        if ($unsubscribe_type == 'unsubscribeAll') {
            $emailManageLink = EmailManageLink::whereDisableAllKey($key)->firstOrFail();
            $disabled_url = route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
            $response_text = 'Вы действительно хотите отписаться от всех уведомлений?';
            $response_url = route('subscription.ajax.unsubscribe.all', ['key' => $key]);
        } elseif ($unsubscribe_type == 'unsubscribe') {
            $emailNotificationSetting = EmailNotificationSetting::whereDisableLinkKey($key)->firstOrFail();
            $emailManageLink = EmailManageLink::whereEmail($emailNotificationSetting->email)->firstOrFail();
            $disabled_url = route('subscription.manage', ['key' => $emailManageLink->notification_settings_key]);
            $response_text = 'Вы действительно хотите отписаться от рассылки "'
                .EmailNotification::getReadableTypeName($emailNotificationSetting->notification_type).'"'
                .(($emailNotificationSetting->company) ? " для компании \"{$emailNotificationSetting->company->name}\"" : '');
            $response_url = route('subscription.ajax.unsubscribe.one', ['key' => $key]);
        }

        return response()->json(
            [
                'response_text' => $response_text,
                'response_url' => $response_url,
                'disabled_url' => $disabled_url,
            ]
        );
    }
}
