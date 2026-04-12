<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Notifications\TenantLifecycleNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTenantSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:send-subscription-reminders {--days=7 : Number of days before due date to trigger reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send subscription reminder emails for expiring or expired tenants.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);

        $tenants = School::query()
            ->whereNotNull('plan_expiration_email')
            ->whereNotNull('plan_due_at')
            ->get();

        $sent = 0;
        $autoDisabled = 0;

        foreach ($tenants as $tenant) {
            $eventType = null;

            if ($tenant->isSubscriptionExpired()) {
                $eventType = 'subscription_expired';

                if ($tenant->is_enabled) {
                    $tenant->update([
                        'is_enabled' => false,
                        'disabled_at' => now(),
                        'disable_reason' => 'Plan expired',
                    ]);
                    $autoDisabled++;
                }
            } elseif ($tenant->isSubscriptionExpiringWithinDays($days)) {
                $eventType = 'subscription_expiring';
            }

            if (! $eventType) {
                continue;
            }

            Notification::route('mail', $tenant->plan_expiration_email)
                ->notify(new TenantLifecycleNotification($tenant, $eventType));

            $sent++;
        }

        $this->info("Tenant subscription reminders sent: {$sent}");
        $this->info("Tenants auto-disabled due to expiration: {$autoDisabled}");

        return self::SUCCESS;
    }
}
