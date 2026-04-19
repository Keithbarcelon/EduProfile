<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SettingsController extends Controller
{
    public function index(): View
    {
        $school = School::query()->findOrFail((int) app('currentSchool')->id);

        return view('admin.settings.index', [
            'school' => $school,
            'branding' => $this->loadBrandingSettings($school),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = School::query()->findOrFail((int) app('currentSchool')->id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'branding_primary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'branding_accent_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
        ]);

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('schools/logos', 'public');
        }

        unset($validated['logo']);
        unset($validated['email']);
        $primaryColor = $this->normalizeColor($validated['branding_primary_color'] ?? null);
        $accentColor = $this->normalizeColor($validated['branding_accent_color'] ?? null);
        unset($validated['branding_primary_color'], $validated['branding_accent_color']);

        $school->update($validated);
        app()->instance('currentSchool', $school->fresh());

        $brandingSaved = $this->syncBrandingSettings($school, $primaryColor, $accentColor);

        $message = 'School settings updated successfully.';

        if (! $brandingSaved) {
            $message .= ' Branding settings were not synced to central configuration.';
        }

        return redirect()->route('admin.settings.index')
            ->with('success', $message);
    }

    public function logo(Request $request): BinaryFileResponse
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant instanceof School, 404, 'Tenant not found.');

        $school = School::query()->findOrFail((int) $tenant->id);
        $logoPath = trim((string) $school->logo_path);

        abort_unless($logoPath !== '', 404, 'Logo not found.');

        if (! Storage::disk('public')->exists($logoPath)) {
            abort(404, 'Logo file missing.');
        }

        return response()->file(Storage::disk('public')->path($logoPath), [
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * @return array{primary_color:string,accent_color:string}
     */
    private function loadBrandingSettings(School $school): array
    {
        $defaults = [
            'primary_color' => '#4f46e5',
            'accent_color' => '#0891b2',
        ];

        $centralSchoolId = $this->resolveCentralSchoolId($school);

        if (! $centralSchoolId) {
            return $defaults;
        }

        try {
            $settings = DB::connection('central')
                ->table('tenant_settings')
                ->where('school_id', $centralSchoolId)
                ->whereIn('setting_key', ['branding.primary_color', 'branding.accent_color'])
                ->pluck('setting_value', 'setting_key');

            $primary = $this->normalizeColor($settings['branding.primary_color'] ?? null) ?? $defaults['primary_color'];
            $accent = $this->normalizeColor($settings['branding.accent_color'] ?? null) ?? $defaults['accent_color'];

            return [
                'primary_color' => $primary,
                'accent_color' => $accent,
            ];
        } catch (Throwable $exception) {
            Log::warning('Unable to load tenant branding settings from central.', [
                'school_id' => $school->id,
                'error' => $exception->getMessage(),
            ]);

            return $defaults;
        }
    }

    private function syncBrandingSettings(School $school, ?string $primaryColor, ?string $accentColor): bool
    {
        $centralSchoolId = $this->resolveCentralSchoolId($school);

        if (! $centralSchoolId) {
            return false;
        }

        try {
            $payload = [
                'branding.primary_color' => $primaryColor,
                'branding.accent_color' => $accentColor,
            ];

            foreach ($payload as $key => $value) {
                if ($value === null || $value === '') {
                    DB::connection('central')
                        ->table('tenant_settings')
                        ->where('school_id', $centralSchoolId)
                        ->where('setting_key', $key)
                        ->delete();

                    continue;
                }

                DB::connection('central')
                    ->table('tenant_settings')
                    ->updateOrInsert(
                        [
                            'school_id' => $centralSchoolId,
                            'setting_key' => $key,
                        ],
                        [
                            'setting_value' => $value,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Unable to sync tenant branding settings to central.', [
                'school_id' => $school->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function resolveCentralSchoolId(School $school): ?int
    {
        try {
            $query = DB::connection('central')->table('schools');

            $tenantDatabase = trim((string) $school->tenant_database);
            $tenantDomain = trim((string) $school->tenant_domain);

            if ($tenantDatabase === '' && $tenantDomain === '') {
                return null;
            }

            $central = $query
                ->where(function ($builder) use ($tenantDatabase, $tenantDomain): void {
                    if ($tenantDatabase !== '') {
                        $builder->orWhere('tenant_database', $tenantDatabase);
                    }

                    if ($tenantDomain !== '') {
                        $builder->orWhere('tenant_domain', $tenantDomain)
                            ->orWhere('requested_tenant_domain', $tenantDomain);
                    }
                })
                ->first(['id']);

            return $central ? (int) $central->id : null;
        } catch (Throwable $exception) {
            Log::warning('Unable to resolve central school for tenant branding sync.', [
                'school_id' => $school->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizeColor(?string $value): ?string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        return strtolower($raw);
    }
}
