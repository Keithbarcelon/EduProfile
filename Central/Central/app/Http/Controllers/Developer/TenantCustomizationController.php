<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\School;
use App\Models\TenantDocumentRequirement;
use App\Models\TenantFormField;
use App\Models\TenantProfileSection;
use App\Models\TenantModule;
use App\Models\TenantSetting;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantCustomizationController extends Controller
{
    /**
     * @return array<int, array{section_key:string,label:string,enabled:bool,sort_order:int}>
     */
    private function defaultProfileSections(): array
    {
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

    public function edit(School $tenant): View
    {
        $moduleMap = TenantModule::query()
            ->where('school_id', $tenant->id)
            ->pluck('is_enabled', 'module_id')
            ->map(fn ($value) => (bool) $value)
            ->all();

        $settingMap = TenantSetting::query()
            ->where('school_id', $tenant->id)
            ->pluck('setting_value', 'setting_key')
            ->all();

        $customFieldLines = TenantFormField::query()
            ->where('school_id', $tenant->id)
            ->where('module_key', 'students')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (TenantFormField $field): string {
                $options = collect((array) $field->options_json)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->implode(',');

                $visibleStatuses = collect((array) (($field->rules_json ?? [])['visible_statuses'] ?? []))
                    ->map(fn ($value) => trim(strtolower((string) $value)))
                    ->filter()
                    ->implode(',');

                return implode('|', [
                    $field->label,
                    $field->field_type,
                    $field->is_required ? '1' : '0',
                    $options,
                    $visibleStatuses,
                    (string) (($field->rules_json['section'] ?? '') ?: 'custom_fields'),
                ]);
            })
            ->implode(PHP_EOL);

        $profileSections = TenantProfileSection::query()
            ->where('school_id', $tenant->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['section_key', 'label', 'enabled', 'sort_order'])
            ->map(fn (TenantProfileSection $section) => [
                'section_key' => (string) $section->section_key,
                'label' => (string) $section->label,
                'enabled' => (bool) $section->enabled,
                'sort_order' => (int) $section->sort_order,
            ])
            ->all();

        if ($profileSections === []) {
            $profileSections = $this->defaultProfileSections();
        }

        $documentRequirementLines = TenantDocumentRequirement::query()
            ->where('school_id', $tenant->id)
            ->where('module_key', 'documents')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (TenantDocumentRequirement $requirement): string {
                return implode('|', [
                    $requirement->document_name,
                    $requirement->status_category ?: '*',
                    $requirement->is_required ? '1' : '0',
                ]);
            })
            ->implode(PHP_EOL);

        $workflowTemplate = WorkflowTemplate::query()
            ->where('school_id', $tenant->id)
            ->where('module_key', 'status_monitoring')
            ->where('workflow_key', 'status_change_approval')
            ->first();

        $workflowStepLines = collect();
        if ($workflowTemplate) {
            $workflowStepLines = WorkflowStep::query()
                ->where('workflow_template_id', $workflowTemplate->id)
                ->where('is_active', true)
                ->orderBy('step_order')
                ->orderBy('id')
                ->get()
                ->map(fn (WorkflowStep $step) => implode('|', [
                    $step->role_slug,
                    $step->step_name ?: '',
                ]));
        }

        return view('developer.tenants.customization', [
            'tenant' => $tenant,
            'modules' => Module::query()->orderBy('category')->orderBy('name')->get(),
            'moduleMap' => $moduleMap,
            'settingMap' => $settingMap,
            'customFieldLines' => old('custom_fields_text', $customFieldLines),
            'documentRequirementLines' => old('document_requirements_text', $documentRequirementLines),
            'workflowStepLines' => old('workflow_steps_text', $workflowStepLines->implode(PHP_EOL)),
            'profileSections' => $profileSections,
        ]);
    }

    public function update(Request $request, School $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
            'custom_fields_text' => ['nullable', 'string', 'max:20000'],
            'document_requirements_text' => ['nullable', 'string', 'max:20000'],
            'workflow_steps_text' => ['nullable', 'string', 'max:20000'],
            'profile_sections' => ['nullable', 'array'],
            'profile_sections.*.section_key' => ['required_with:profile_sections', 'string', 'max:100'],
            'profile_sections.*.label' => ['required_with:profile_sections', 'string', 'max:150'],
            'profile_sections.*.enabled' => ['nullable', 'boolean'],
            'profile_sections.*.sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $moduleInputs = collect($validated['modules'] ?? [])->map(fn ($value) => (bool) $value);
        $modules = Module::query()->get(['id', 'default_enabled']);

        foreach ($modules as $module) {
            $isAlwaysOn = (bool) $module->is_core || (string) $module->key === 'support_updates';

            $enabled = $isAlwaysOn
                ? true
                : ($moduleInputs->has((string) $module->id)
                    ? $moduleInputs->get((string) $module->id)
                    : (bool) $module->default_enabled);

            TenantModule::query()->updateOrCreate(
                [
                    'school_id' => $tenant->id,
                    'module_id' => $module->id,
                ],
                [
                    'is_enabled' => $enabled,
                    'activated_at' => $enabled ? now() : null,
                ]
            );
        }

        $settings = collect($validated['settings'] ?? [])
            ->map(fn ($value) => trim((string) $value));

        foreach ($settings as $settingKey => $settingValue) {
            if ($settingValue === '') {
                TenantSetting::query()
                    ->where('school_id', $tenant->id)
                    ->where('setting_key', $settingKey)
                    ->delete();

                continue;
            }

            TenantSetting::query()->updateOrCreate(
                [
                    'school_id' => $tenant->id,
                    'setting_key' => (string) $settingKey,
                ],
                [
                    'setting_value' => $settingValue,
                ]
            );
        }

        DB::transaction(function () use ($tenant, $validated): void {
            TenantProfileSection::query()
                ->where('school_id', $tenant->id)
                ->delete();

            $sections = collect($validated['profile_sections'] ?? [])
                ->map(function (array $row): array {
                    return [
                        'section_key' => strtolower(trim((string) ($row['section_key'] ?? ''))),
                        'label' => trim((string) ($row['label'] ?? '')),
                        'enabled' => (bool) ($row['enabled'] ?? false),
                        'sort_order' => max((int) ($row['sort_order'] ?? 1), 1),
                    ];
                })
                ->filter(fn (array $row) => $row['section_key'] !== '' && $row['label'] !== '')
                ->unique('section_key')
                ->values();

            if ($sections->isEmpty()) {
                $sections = collect($this->defaultProfileSections());
            }

            foreach ($sections as $row) {
                TenantProfileSection::query()->create([
                    'school_id' => $tenant->id,
                    'section_key' => $row['section_key'],
                    'label' => $row['label'],
                    'enabled' => (bool) $row['enabled'],
                    'sort_order' => (int) $row['sort_order'],
                ]);
            }

            TenantFormField::query()
                ->where('school_id', $tenant->id)
                ->where('module_key', 'students')
                ->delete();

            foreach ($this->parseCustomFields((string) ($validated['custom_fields_text'] ?? '')) as $index => $field) {
                TenantFormField::query()->create([
                    'school_id' => $tenant->id,
                    'module_key' => 'students',
                    'field_key' => $field['field_key'],
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'is_required' => $field['is_required'],
                    'options_json' => $field['options_json'],
                    'rules_json' => $field['rules_json'],
                    'sort_order' => $field['sort_order'] > 0 ? $field['sort_order'] : ($index + 1),
                    'is_active' => true,
                ]);
            }

            TenantDocumentRequirement::query()
                ->where('school_id', $tenant->id)
                ->where('module_key', 'documents')
                ->delete();

            foreach ($this->parseDocumentRequirements((string) ($validated['document_requirements_text'] ?? '')) as $index => $requirement) {
                TenantDocumentRequirement::query()->create([
                    'school_id' => $tenant->id,
                    'module_key' => 'documents',
                    'status_category' => $requirement['status_category'],
                    'document_name' => $requirement['document_name'],
                    'is_required' => $requirement['is_required'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $template = WorkflowTemplate::query()->updateOrCreate(
                [
                    'school_id' => $tenant->id,
                    'module_key' => 'status_monitoring',
                    'workflow_key' => 'status_change_approval',
                ],
                [
                    'name' => 'Status Change Approval',
                    'is_default' => true,
                ]
            );

            WorkflowStep::query()
                ->where('workflow_template_id', $template->id)
                ->delete();

            foreach ($this->parseWorkflowSteps((string) ($validated['workflow_steps_text'] ?? '')) as $step) {
                WorkflowStep::query()->create([
                    'workflow_template_id' => $template->id,
                    'step_order' => $step['step_order'],
                    'role_slug' => $step['role_slug'],
                    'step_name' => $step['step_name'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('developer.tenants.customization.edit', $tenant)
            ->with('success', 'Tenant customization updated successfully.');
    }

    /**
     * @return array<int, array{sort_order:int,field_key:string,label:string,field_type:string,is_required:bool,options_json:array<int,string>,rules_json:array{visible_statuses:array<int,string>}}>
     */
    private function parseCustomFields(string $rawText): array
    {
        $allowedTypes = ['text', 'number', 'date', 'select', 'textarea'];

        $lines = collect(preg_split('/\r\n|\n|\r/', trim($rawText)) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        $fields = [];
        $usedFieldKeys = [];

        foreach ($lines as $index => $line) {
            $parts = array_map('trim', explode('|', $line));

            $sortOrder = $index + 1;
            $fieldKey = '';
            $label = '';
            $type = 'text';
            $required = '0';
            $options = '';
            $visibleStatuses = '';

            // Legacy explicit format: order|field_key|label|type|required|options|visible_statuses
            if (isset($parts[0], $parts[1], $parts[2]) && is_numeric($parts[0])) {
                [$sortOrder, $fieldKey, $label, $type, $required, $options, $visibleStatuses, $section] = array_pad($parts, 8, '');
                $sortOrder = max((int) $sortOrder, 1);
            }
            // Legacy explicit format: field_key|label|type|required|options|visible_statuses
            elseif (count($parts) >= 3 && ! in_array(strtolower((string) ($parts[1] ?? '')), $allowedTypes, true)) {
                [$fieldKey, $label, $type, $required, $options, $visibleStatuses, $section] = array_pad($parts, 7, '');
            }
            // Simplified format: label|type|required|options|visible_statuses
            else {
                [$label, $type, $required, $options, $visibleStatuses, $section] = array_pad($parts, 6, '');
                $fieldKey = str((string) $label)->lower()->replace(' ', '_')->replaceMatches('/[^a-z0-9_]/', '')->toString();
            }

            $fieldKey = trim((string) $fieldKey);
            $label = trim((string) $label);
            $type = strtolower(trim((string) $type));
            $section = strtolower(trim((string) ($section ?? 'custom_fields')));

            if ($section === '') {
                $section = 'custom_fields';
            }

            if ($fieldKey === '' || $label === '') {
                continue;
            }

            if (! in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $baseKey = $fieldKey;
            $suffix = 2;
            while (in_array($fieldKey, $usedFieldKeys, true)) {
                $fieldKey = $baseKey . '_' . $suffix;
                $suffix++;
            }
            $usedFieldKeys[] = $fieldKey;

            $fields[] = [
                'sort_order' => $sortOrder,
                'field_key' => $fieldKey,
                'label' => $label,
                'field_type' => $type,
                'is_required' => in_array(trim((string) $required), ['1', 'true', 'yes'], true),
                'options_json' => collect(explode(',', (string) $options))
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all(),
                'rules_json' => [
                    'visible_statuses' => collect(explode(',', (string) $visibleStatuses))
                        ->map(fn ($value) => trim(strtolower((string) $value)))
                        ->filter()
                        ->values()
                        ->all(),
                    'section' => $section,
                ],
            ];
        }

        return collect($fields)
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{status_category:?string,document_name:string,is_required:bool}>
     */
    private function parseDocumentRequirements(string $rawText): array
    {
        return collect(preg_split('/\r\n|\n|\r/', trim($rawText)) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line): ?array {
                $parts = array_map('trim', explode('|', $line));

                // Legacy format: status|document|required
                if (isset($parts[0]) && ($parts[0] === '*' || in_array(strtolower((string) $parts[0]), ['regular', 'affirmative', 'probation'], true))) {
                    [$statusCategory, $documentName, $required] = array_pad($parts, 3, '');
                } else {
                    // Simplified format: document|status|required
                    [$documentName, $statusCategory, $required] = array_pad($parts, 3, '');
                }

                $statusCategory = trim(strtolower($statusCategory));
                $documentName = trim($documentName);

                if ($documentName === '') {
                    return null;
                }

                return [
                    'status_category' => $statusCategory === '*' || $statusCategory === '' ? null : $statusCategory,
                    'document_name' => $documentName,
                    'is_required' => ! in_array(trim($required), ['0', 'false', 'no'], true),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{step_order:int,role_slug:string,step_name:?string}>
     */
    private function parseWorkflowSteps(string $rawText): array
    {
        return collect(preg_split('/\r\n|\n|\r/', trim($rawText)) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->map(function (string $line, int $index): ?array {
                $parts = array_map('trim', explode('|', $line));

                if (isset($parts[0]) && is_numeric($parts[0])) {
                    [$order, $roleSlug, $stepName] = array_pad($parts, 3, '');
                    $stepOrder = (int) $order;
                } else {
                    [$roleSlug, $stepName] = array_pad($parts, 2, '');
                    $stepOrder = $index + 1;
                }

                $roleSlug = trim(strtolower((string) $roleSlug));

                if ($roleSlug === '') {
                    return null;
                }

                return [
                    'step_order' => $stepOrder,
                    'role_slug' => $roleSlug,
                    'step_name' => trim($stepName) !== '' ? trim($stepName) : null,
                ];
            })
            ->filter()
            ->sortBy('step_order')
            ->values()
            ->all();
    }
}
