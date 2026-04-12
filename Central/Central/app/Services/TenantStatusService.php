<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class TenantStatusService
{
    public function toggle(School $tenant, ?string $disableReason = null): School
    {
        if ($tenant->is_enabled) {
            $reason = trim((string) $disableReason);

            if ($reason === '') {
                throw new InvalidArgumentException('Disable reason is required when disabling a tenant.');
            }

            $tenant->update([
                'is_enabled' => false,
                'disabled_at' => Carbon::now(),
                'disable_reason' => $reason,
            ]);

            return $tenant->refresh();
        }

        $tenant->update([
            'is_enabled' => true,
            'disabled_at' => null,
            'disable_reason' => null,
        ]);

        return $tenant->refresh();
    }
}