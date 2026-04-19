<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantRequestReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly School $school)
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
            ->greeting('Hello '.$this->school->signup_admin_name.',')
            ->line('Your school registration request has been received by EduProfile Central.')
            ->line('School: '.$this->school->name)
            ->line('Plan: '.strtoupper((string) $this->school->plan_type))
            ->line('Requested domain: '.($this->school->requested_tenant_domain ?? 'Not provided'))
            ->line('Status: Pending admin approval')
            ->line('You will receive another email after review.')
            ->line('This is an automated message from EduProfile Central.');
    }
}
