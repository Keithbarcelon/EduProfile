<?php

namespace App\Notifications;

use App\Models\TenantRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantRequestRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TenantRequest $tenantRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('School Registration Request Rejected')
            ->greeting('Hello '.$this->tenantRequest->signup_admin_name.',')
            ->line('Your school registration request was reviewed and rejected by the central administrator.')
            ->line('School: '.$this->tenantRequest->tenant_name)
            ->line('Reason: '.($this->tenantRequest->rejection_reason ?: 'No reason was provided.'))
            ->line('You can submit a new request after resolving the issue above.')
            ->line('This is an automated message from EduProfile Central.');
    }
}
