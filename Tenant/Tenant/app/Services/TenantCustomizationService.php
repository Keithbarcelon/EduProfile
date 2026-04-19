<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TenantCustomizationService
{
    private bool $loaded = false;

    private ?int $centralSchoolId = null;

    /**
     * @var array<string, bool>
     */
    private array $moduleMap = [];

    /**
     * @var array<string, bool>
     */
    private array $featureFlagMap = [];

    /**
     * @var array<string, string>
     */
    private array $settingsMap = [];

    /**
        * @var array<int, array{field_key:string,label:string,field_type:string,is_required:bool,options:array<int,string>,visible_statuses:array<int,string>,section:string}>
     */
    private array $studentCustomFields = [];

        /**
        * @var array<int, array{section_key:string,label:string,enabled:bool,sort_order:int}>
        */
        private array $profileSections = [];

    /**
     * @var array<int, array{status_category:?string,document_name:string,is_required:bool}>
     */
    private array $documentRequirements = [];

    /**
     * @var array<string, array<int, array{step_order:int,role_slug:string,step_name:?string}>>
     */
    private array $workflowStepsByKey = [];

    public function moduleEnabled(string $moduleKey, bool $default = true): bool
    {
        $this->load();

        return $this->moduleMap[$moduleKey] ?? $default;
    }

    public function featureActive(string $featureKey, bool $default = false): bool
    {
        $this->load();

        return $this->featureFlagMap[$featureKey] ?? $default;
    }

    public function setting(string $settingKey, ?string $default = null): ?string
    {
        $this->load();

        return $this->settingsMap[$settingKey] ?? $default;
    }

    /**
     * @return array<int, array{field_key:string,label:string,field_type:string,is_required:bool,options:array<int,string>,visible_statuses:array<int,string>,section:string}>
     */
    public function studentCustomFields(): array
    {
        $this->load();

        return $this->studentCustomFields;
    }

    /**
     * @return array<int, array{section_key:string,label:string,enabled:bool,sort_order:int}>
     */
    public function profileSections(): array
    {
        $this->load();

        if ($this->profileSections !== []) {
            return $this->profileSections;
        }

        return [
            ['section_key' => 'basic_info', 'label' => 'Basic Info', 'enabled' => true, 'sort_order' => 1],
            ['section_key' => 'academic_info', 'label' => 'Academic Info', 'enabled' => true, 'sort_order' => 2],
            ['section_key' => 'family_background', 'label' => 'Family Background', 'enabled' => true, 'sort_order' => 3],
            ['section_key' => 'custom_fields', 'label' => 'Custom Fields', 'enabled' => true, 'sort_order' => 4],
            ['section_key' => 'documents', 'label' => 'Documents', 'enabled' => true, 'sort_order' => 5],
            ['section_key' => 'status_history', 'label' => 'Status History', 'enabled' => true, 'sort_order' => 6],
            ['section_key' => 'interventions', 'label' => 'Interventions', 'enabled' => true, 'sort_order' => 7],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function requiredDocumentNamesForStatus(?string $statusCategory = null): array
    {
        $this->load();

        $normalizedStatus = $statusCategory ? strtolower(trim($statusCategory)) : null;

        return collect($this->documentRequirements)
            ->filter(function (array $requirement) use ($normalizedStatus): bool {
                return $requirement['is_required']
                    && ($requirement['status_category'] === null || $requirement['status_category'] === $normalizedStatus);
            })
            ->pluck('document_name')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{step_order:int,role_slug:string,step_name:?string}>
     */
    public function workflowSteps(string $workflowKey = 'status_change_approval'): array
    {
        $this->load();

        $steps = $this->workflowStepsByKey[$workflowKey] ?? [];

        if ($steps !== []) {
            return $steps;
        }

        return [
            [
                'step_order' => 1,
                'role_slug' => 'department',
                'step_name' => 'Department Review',
            ],
            [
                'step_order' => 2,
                'role_slug' => 'tenant_admin',
                'step_name' => 'Final Approval',
            ],
        ];
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        if (! $this->centralCustomizationTablesAvailable()) {
            return;
        }

        /** @var School|null $currentSchool */
        $currentSchool = app('currentSchool');

        if (! $currentSchool) {
            return;
        }

        $this->centralSchoolId = $this->resolveCentralSchoolId($currentSchool);

        if (! $this->centralSchoolId) {
            return;
        }

        try {
            $moduleRows = DB::connection('central')
                ->table('modules')
                ->leftJoin('tenant_modules', function ($join): void {
                    $join->on('tenant_modules.module_id', '=', 'modules.id')
                        ->where('tenant_modules.school_id', '=', $this->centralSchoolId);
                })
                ->select([
                    'modules.key',
                    'modules.is_core',
                    'modules.default_enabled',
                    'tenant_modules.is_enabled',
                ])
                ->get();

            foreach ($moduleRows as $row) {
                if ((bool) $row->is_core) {
                    $this->moduleMap[(string) $row->key] = true;
                    continue;
                }

                $isEnabled = $row->is_enabled;
                $this->moduleMap[(string) $row->key] = $isEnabled === null
                    ? (bool) $row->default_enabled
                    : (bool) $isEnabled;
            }

            $featureRows = DB::connection('central')
                ->table('tenant_feature_flags')
                ->where('school_id', $this->centralSchoolId)
                ->get(['flag_key', 'is_active']);

            foreach ($featureRows as $row) {
                $this->featureFlagMap[(string) $row->flag_key] = (bool) $row->is_active;
            }

            $settingRows = DB::connection('central')
                ->table('tenant_settings')
                ->where('school_id', $this->centralSchoolId)
                ->get(['setting_key', 'setting_value']);

            foreach ($settingRows as $row) {
                $this->settingsMap[(string) $row->setting_key] = (string) ($row->setting_value ?? '');
            }

            if (Schema::connection('central')->hasTable('tenant_form_fields')) {
                $this->studentCustomFields = DB::connection('central')
                    ->table('tenant_form_fields')
                    ->where('school_id', $this->centralSchoolId)
                    ->where('module_key', 'students')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn ($row) => [
                        'field_key' => (string) $row->field_key,
                        'label' => (string) $row->label,
                        'field_type' => (string) $row->field_type,
                        'is_required' => (bool) $row->is_required,
                        'options' => collect(json_decode((string) ($row->options_json ?? '[]'), true) ?: [])
                            ->map(fn ($value) => (string) $value)
                            ->filter()
                            ->values()
                            ->all(),
                        'visible_statuses' => collect((array) (json_decode((string) ($row->rules_json ?? '{}'), true)['visible_statuses'] ?? []))
                            ->map(fn ($value) => strtolower(trim((string) $value)))
                            ->filter()
                            ->values()
                            ->all(),
                        'section' => strtolower(trim((string) ((json_decode((string) ($row->rules_json ?? '{}'), true)['section'] ?? 'custom_fields')))) ?: 'custom_fields',
                    ])
                    ->all();
            }

            if (Schema::connection('central')->hasTable('tenant_profile_sections')) {
                $this->profileSections = DB::connection('central')
                    ->table('tenant_profile_sections')
                    ->where('school_id', $this->centralSchoolId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['section_key', 'label', 'enabled', 'sort_order'])
                    ->map(fn ($row) => [
                        'section_key' => strtolower(trim((string) $row->section_key)),
                        'label' => trim((string) $row->label),
                        'enabled' => (bool) $row->enabled,
                        'sort_order' => max((int) $row->sort_order, 1),
                    ])
                    ->filter(fn (array $row) => $row['section_key'] !== '' && $row['label'] !== '')
                    ->values()
                    ->all();
            }

            if (Schema::connection('central')->hasTable('tenant_document_requirements')) {
                $this->documentRequirements = DB::connection('central')
                    ->table('tenant_document_requirements')
                    ->where('school_id', $this->centralSchoolId)
                    ->where('module_key', 'documents')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn ($row) => [
                        'status_category' => $row->status_category ? strtolower((string) $row->status_category) : null,
                        'document_name' => (string) $row->document_name,
                        'is_required' => (bool) $row->is_required,
                    ])
                    ->all();
            }

            if (Schema::connection('central')->hasTable('workflow_templates') && Schema::connection('central')->hasTable('workflow_steps')) {
                $templates = DB::connection('central')
                    ->table('workflow_templates')
                    ->where('school_id', $this->centralSchoolId)
                    ->where('module_key', 'status_monitoring')
                    ->get(['id', 'workflow_key']);

                foreach ($templates as $template) {
                    $steps = DB::connection('central')
                        ->table('workflow_steps')
                        ->where('workflow_template_id', (int) $template->id)
                        ->where('is_active', true)
                        ->orderBy('step_order')
                        ->orderBy('id')
                        ->get(['step_order', 'role_slug', 'step_name'])
                        ->map(fn ($step) => [
                            'step_order' => (int) $step->step_order,
                            'role_slug' => strtolower((string) $step->role_slug),
                            'step_name' => $step->step_name ? (string) $step->step_name : null,
                        ])
                        ->all();

                    $this->workflowStepsByKey[(string) $template->workflow_key] = $steps;
                }
            }
        } catch (Throwable) {
            // Keep graceful defaults when central customization tables are unreachable.
        }
    }

    private function resolveCentralSchoolId(School $currentSchool): ?int
    {
        try {
            $centralSchool = DB::connection('central')
                ->table('schools')
                ->where(function ($query) use ($currentSchool): void {
                    $tenantDatabase = trim((string) $currentSchool->tenant_database);
                    $tenantDomain = trim((string) $currentSchool->tenant_domain);

                    if ($tenantDatabase !== '') {
                        $query->orWhere('tenant_database', $tenantDatabase);
                    }

                    if ($tenantDomain !== '') {
                        $query->orWhere('tenant_domain', $tenantDomain)
                            ->orWhere('requested_tenant_domain', $tenantDomain);
                    }
                })
                ->first(['id']);

            return $centralSchool ? (int) $centralSchool->id : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function centralCustomizationTablesAvailable(): bool
    {
        static $available;

        if ($available !== null) {
            return $available;
        }

        try {
            $available = Schema::connection('central')->hasTable('modules')
                && Schema::connection('central')->hasTable('tenant_modules')
                && Schema::connection('central')->hasTable('tenant_feature_flags')
                && Schema::connection('central')->hasTable('tenant_settings');
        } catch (Throwable) {
            $available = false;
        }

        return $available;
    }
}
