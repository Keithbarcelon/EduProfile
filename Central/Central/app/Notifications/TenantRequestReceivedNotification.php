<?php

namespace App\Notifications;

use App\Models\TenantRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantRequestReceivedNotification extends Notification
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
            ->subject('School Registration Request Received')
            ->greeting('Hello '.$this->tenantRequest->signup_admin_name.',')
            ->line('Your school registration request has been received by EduProfile Central.')
            ->line('School: '.$this->tenantRequest->tenant_name)
            ->line('Plan: '.strtoupper((string) $this->tenantRequest->plan_type))
            ->line('Requested domain: '.($this->tenantRequest->requested_tenant_domain ?? 'Not provided'))
            ->line('Status: Pending admin approval')
            ->line('You will receive another email after review.')
            ->line('This is an automated message from EduProfile Central.');
    }
}
