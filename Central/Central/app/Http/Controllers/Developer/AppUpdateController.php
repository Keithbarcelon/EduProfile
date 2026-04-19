<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\AppUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppUpdateController extends Controller
{
    public function index(Request $request): View
    {
        $updates = AppUpdate::query()
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', (bool) $request->boolean('is_active')))
            ->orderByDesc('release_date')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('developer.app-updates.index', [
            'updates' => $updates,
        ]);
    }

    public function create(): View
    {
        return view('developer.app-updates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUpdate($request);
        AppUpdate::query()->create($validated);

        return redirect()->route('developer.app-updates.index')
            ->with('success', 'App update created successfully.');
    }

    public function edit(AppUpdate $appUpdate): View
    {
        return view('developer.app-updates.edit', [
            'appUpdate' => $appUpdate,
        ]);
    }

    public function update(Request $request, AppUpdate $appUpdate): RedirectResponse
    {
        $validated = $this->validateUpdate($request);
        $appUpdate->update($validated);

        return redirect()->route('developer.app-updates.index')
            ->with('success', 'App update updated successfully.');
    }

    public function destroy(AppUpdate $appUpdate): RedirectResponse
    {
        $appUpdate->delete();

        return redirect()->route('developer.app-updates.index')
            ->with('success', 'App update deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'version' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_date' => ['nullable', 'date'],
            'release_document_path' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
