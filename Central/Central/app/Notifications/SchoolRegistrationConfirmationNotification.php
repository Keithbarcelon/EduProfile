<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchoolRegistrationConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly School $school,
        private readonly string $adminName,
        private readonly string $adminEmail,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $scheme = parse_url((string) config('app.url', 'http://localhost'), PHP_URL_SCHEME) ?: 'http';
        $isApproved = $this->school->isApproved() && $this->school->tenant_domain;
        $tenantPortalUrl = $isApproved
            ? sprintf('%s://%s', $scheme, $this->school->tenant_domain)
            : null;

        $message = (new MailMessage)
            ->subject($isApproved ? 'School Registration Confirmed' : 'School Registration Pending Approval')
            ->greeting('Hello '.$this->adminName.',')
            ->line($isApproved
                ? 'Your school registration has been approved successfully.'
                : 'Your school registration has been received and is pending central admin approval.')
            ->line('School: '.$this->school->name)
            ->line('Admin email: '.$this->adminEmail)
            ->line('Plan: '.strtoupper((string) $this->school->plan_type))
            ->line('Plan window: '.($this->school->plan_started_at?->format('Y-m-d') ?? 'N/A').' to '.($this->school->plan_due_at?->format('Y-m-d') ?? 'N/A'));

        if ($tenantPortalUrl) {
            $message->line('Tenant domain: '.$this->school->tenant_domain)
                ->action('Open School Portal', $tenantPortalUrl);
        } elseif ($this->school->requested_tenant_domain) {
            $message->line('Requested domain: '.$this->school->requested_tenant_domain);
        }

        if ($isApproved) {
            $message->line('You can now sign in with the admin email and password used during registration.');
        } else {
            $message->line('Login access will be enabled after approval and domain activation.');
        }

        return $message->line('This is an automated message from EduProfile Central.');
    }
}