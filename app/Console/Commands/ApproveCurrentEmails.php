<?php

namespace App\Console\Commands;

use App\Domain\Company\Models\Company;
use App\Domain\Notification\Models\EmailCompanyAdmin;
use App\Domain\Notification\Models\EmailManageLink;
use App\Domain\Notification\Models\EmailNotification;
use App\Domain\Notification\Models\EmailNotificationSetting;
use App\Domain\ProxyLead\Models\PlEmailRecipients;
use App\Domain\ProxyLead\Models\ProxyLeadSetting;
use App\Exceptions\EmailSubscriptionException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ApproveCurrentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:approve-current-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send initial emails with approve notification link';

    /**
     * @throws EmailSubscriptionException
     */
    public function handle(): void
    {
        $emails1 = EmailNotification::select(['email'])->distinct()->get()->pluck('email');
        $emails2 = PlEmailRecipients::select(['email'])->distinct()->get()->pluck('email');
        $emails = $emails1->merge($emails2)->unique();
        $this->info("Found {$emails->count()} emails");

        $emailNotificationTypes = EmailNotification::getListOfAvailableTypes();
        $this->info('Found '.count($emailNotificationTypes).' notification types: '.implode(', ',
                $emailNotificationTypes));

        $this->addEmailNotifications($emails, $emailNotificationTypes);
        $this->info('Mark all existing notifications as pending, non existing as disabled');

        $this->addEmailManageLinks($emails);
        $this->info('Generated email manage links');

        $this->sendInitialApproveEmail($emails);
        $this->info('Sent approve emails');

        $this->generateCompaniesKeys();
        $this->info('Generated manage company key');
    }

    /**
     * @param Collection $emails
     * @param array $emailNotificationTypes
     * @throws EmailSubscriptionException
     */
    private function addEmailNotifications(Collection $emails, array $emailNotificationTypes): void
    {
        foreach ($emails as $email) {
            $companies = Company::all()->pluck('id');
            foreach ($companies as $companyId) {
                $types = $this->getAssignedEmailTypes($email, $companyId);
                if ($types->count() === 0) {
                    continue;
                }
                foreach ($emailNotificationTypes as $emailNotificationType) {
                    if ($types->contains($emailNotificationType)) {
                        EmailNotificationSetting::setPending($email, $emailNotificationType, $companyId);
                        if ($emailNotificationType === EmailNotification::MAIN_TYPE) {
                            $admin = new EmailCompanyAdmin();
                            $admin->company_id = $companyId;
                            $admin->email = $email;
                            $admin->save();
                        }
                    } else {
                        EmailNotificationSetting::setDisabled($email, $emailNotificationType, $companyId);
                    }
                }
            }
        }
    }

    private function addEmailManageLinks(Collection $emails): void
    {
        $emails->each(static function ($email) {
            EmailManageLink::init($email);
        });
    }

    private function sendInitialApproveEmail(Collection $emails): void
    {
        $emails->each(static function ($email) {
            EmailNotificationSetting::requestToApproveEmail($email);
        });
    }

    private function generateCompaniesKeys(): void
    {
        $companies = Company::all();
        $companies->each(static function (Company $company) {
            $company->setNewManageKey();
        });
    }

    /**
     * @param $email
     * @param $companyId
     * @return Collection
     */
    private function getAssignedEmailTypes($email, $companyId): Collection
    {
        $types = EmailNotification::select('type')
            ->where([
                'email' => $email,
                'company_id' => $companyId,
            ])
            ->get()->pluck('type');
        $plEmailRecipients = PlEmailRecipients::whereEmail($email)->get();
        foreach ($plEmailRecipients as $plEmailRecipient) {
            if (! $plEmailRecipient->proxy_lead_setting_id) {
                continue;
            }
            $plSettings = ProxyLeadSetting::find(['id' => $plEmailRecipient->proxy_lead_setting_id])->first();
            if (! $plSettings || ($plSettings->company_id !== $companyId)) {
                continue;
            }
            $types->push(EmailNotification::PROXY_LEADS);
        }

        return $types;
    }
}
