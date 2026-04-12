<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantLifecycleNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly School $tenant,
        private readonly string $eventType,
        private readonly ?string $note = null,
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
        $subject = match ($this->eventType) {
            'created' => 'Tenant Signup Completed',
            'enabled' => 'Tenant Activated',
            'disabled' => 'Tenant Deactivated',
            'subscription_updated' => 'Tenant Subscription Updated',
            'subscription_expiring' => 'Tenant Subscription Expiring Soon',
            'subscription_expired' => 'Tenant Subscription Expired',
            default => 'Tenant Notification',
        };

        $status = $this->tenant->is_enabled ? 'Enabled' : 'Disabled';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello,')
            ->line('Tenant: '.$this->tenant->name)
            ->line('Domain: '.($this->tenant->tenant_domain ?? 'N/A'))
            ->line('Database: '.($this->tenant->tenant_database ?? 'N/A'))
            ->line('Plan: '.strtoupper((string) $this->tenant->plan_type))
            ->line('Plan window: '.($this->tenant->plan_started_at?->format('Y-m-d') ?? 'N/A').' to '.($this->tenant->plan_due_at?->format('Y-m-d') ?? 'N/A'))
            ->line('Status: '.$status);

        if ($this->note) {
            $message->line($this->note);
        }

        return $message->line('This is an automated message from the central app.');
    }
}
