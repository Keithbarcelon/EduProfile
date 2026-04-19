<?php

namespace App\Support;

use App\Services\TenantCustomizationService;

class TenantConfig
{
    public static function moduleEnabled(string $moduleKey, bool $default = true): bool
    {
        return app(TenantCustomizationService::class)->moduleEnabled($moduleKey, $default);
    }

    public static function featureActive(string $featureKey, bool $default = false): bool
    {
        return app(TenantCustomizationService::class)->featureActive($featureKey, $default);
    }

    public static function setting(string $settingKey, ?string $default = null): ?string
    {
        return app(TenantCustomizationService::class)->setting($settingKey, $default);
    }

    public static function settingBool(string $settingKey, bool $default = false): bool
    {
        $raw = static::setting($settingKey);

        if ($raw === null) {
            return $default;
        }

        return in_array(strtolower(trim($raw)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<int, array{field_key:string,label:string,field_type:string,is_required:bool,options:array<int,string>,visible_statuses:array<int,string>}>
     */
    public static function studentCustomFields(): array
    {
        return app(TenantCustomizationService::class)->studentCustomFields();
    }

    /**
     * @return array<int, string>
     */
    public static function requiredDocumentNamesForStatus(?string $statusCategory = null): array
    {
        return app(TenantCustomizationService::class)->requiredDocumentNamesForStatus($statusCategory);
    }

    /**
     * @return array<int, array{section_key:string,label:string,enabled:bool,sort_order:int}>
     */
    public static function profileSections(): array
    {
        return app(TenantCustomizationService::class)->profileSections();
    }

    /**
     * @return array<int, array{step_order:int,role_slug:string,step_name:?string}>
     */
    public static function workflowSteps(string $workflowKey = 'status_change_approval'): array
    {
        return app(TenantCustomizationService::class)->workflowSteps($workflowKey);
    }
}
