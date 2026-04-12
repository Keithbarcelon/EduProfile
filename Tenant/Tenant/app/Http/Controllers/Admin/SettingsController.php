<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'school' => School::query()->findOrFail((int) app('currentSchool')->id),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $school = School::query()->findOrFail((int) app('currentSchool')->id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('schools/logos', 'public');
        }

        unset($validated['logo']);

        $school->update($validated);
        app()->instance('currentSchool', $school->fresh());

        return redirect()->route('admin.settings.index')
            ->with('success', 'School settings updated successfully.');
    }
}
