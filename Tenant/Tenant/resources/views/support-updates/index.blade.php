@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'Support & Updates'" :role="$roleLabel" :suppressFlash="true">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Support & Updates</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-cyan-900 to-emerald-800 px-6 py-6 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200">Tenant Operations</p>
            <h2 class="mt-2 text-2xl font-bold">Support and Update Control Center</h2>
            <p class="mt-2 max-w-3xl text-sm text-cyan-100/90">Check your tenant version against the latest release, acknowledge update notices, and submit support requests to central.</p>
        </div>

        @if(!empty($moduleError))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ $moduleError }}
            </div>
        @endif

        <div id="updateInlineNotice" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-cyan-100 bg-white p-5 shadow-sm" id="currentVersionCard">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700">Current Version</p>
                <p class="mt-2 text-2xl font-bold text-slate-900" id="currentVersionText">{{ $currentVersion ?: 'N/A' }}</p>
            </article>
            <article class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm" id="latestVersionCard">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-700">Latest Version</p>
                <p class="mt-2 text-2xl font-bold {{ $latestVersion ? 'text-slate-900' : 'text-slate-500' }}" id="latestVersionText">{{ $latestVersion ?: 'N/A' }}</p>
                <p class="mt-1 text-xs text-slate-500" id="latestSourceText">Source: {{ str_replace('_', ' ', $latestSource) }}</p>
            </article>
            <article class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm" id="updateStatusCard">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Update Status</p>
                <button
                    type="button"
                    id="statusRefreshButton"
                    class="mt-2 inline-flex items-center gap-2 rounded-lg border px-2.5 py-1 text-left text-sm font-semibold transition-colors {{ $updateAvailable ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                    title="Click to refresh update status">
                    <span id="statusRefreshSpinner" class="hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                    <span id="statusRefreshText">{{ $updateAvailable ? 'Update available - refresh' : 'Up to date - refresh' }}</span>
                </button>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" id="lastCheckedCard">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">Last Checked</p>
                <p class="mt-2 text-sm font-semibold text-slate-900" id="lastCheckedText">{{ $tenantUpdate?->last_checked_at?->format('M d, Y h:i A') ?? 'Never' }}</p>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Manual Update Check</h3>
                    <p class="text-sm text-slate-600">Run update check now and refresh tenant update status.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" id="manualCheckButton" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">
                        Check for Updates
                    </button>

                    @if($isTenantAdmin)
                        <form method="POST" action="{{ route('support-updates.sync-latest') }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Sync to Latest Version
                            </button>
                        </form>

                        <form method="POST" action="{{ route('support-updates.acknowledge') }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                                Acknowledge Update
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @php
                $githubReleaseUrl = trim((string) config('app.release.github_url', ''));
            @endphp
            @if($githubReleaseUrl !== '')
                <a href="{{ $githubReleaseUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">
                    Open GitHub Releases
                </a>
            @endif
            @if($tenantUpdate?->acknowledged_at)
                <p class="mt-3 text-xs text-slate-500">Acknowledged at {{ $tenantUpdate->acknowledged_at->format('M d, Y h:i A') }}</p>
            @endif
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Latest Release Notes</h3>
                <div class="space-y-3">
                    @forelse($releaseNotes as $version)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-slate-900">{{ $version->version }}</p>
                                @if($version->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-slate-700">{{ $version->notes ?: 'No release notes provided.' }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $version->created_at?->format('M d, Y h:i A') }}</p>
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">No release notes are available yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Submit Support Request</h3>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">Tenant Admin</span>
                </div>

                @if($isTenantAdmin)
                <form method="POST" action="{{ route('support-updates.requests.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Subject</label>
                        <input
                            type="text"
                            name="subject"
                            value="{{ old('subject') }}"
                            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            maxlength="255"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Message</label>
                        <textarea
                            name="message"
                            rows="5"
                            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            maxlength="3000"
                            required>{{ old('message') }}</textarea>
                    </div>

                    @if($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            Please review the form fields and try again.
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Submit Request
                        </button>
                    </div>
                </form>
                @else
                    <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Only tenant admins can submit support requests.</p>
                @endif
            </section>
        </div>

        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Previous Support Requests</h3>
            <div class="space-y-3">
                @forelse($supportRequests as $supportRequest)
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-slate-900">{{ $supportRequest->subject }}</p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $supportRequest->status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : ($supportRequest->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700') }}">
                                {{ str_replace('_', ' ', $supportRequest->status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-700">{{ $supportRequest->message }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $supportRequest->created_at?->format('M d, Y h:i A') }}</p>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">No support requests submitted yet.</p>
                @endforelse
            </div>

            @if(method_exists($supportRequests, 'hasPages') && $supportRequests->hasPages())
                <div class="mt-4">
                    {{ $supportRequests->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    </div>

    <script>
        (function () {
            const endpoint = @json(route('support-updates.check-json'));
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const statusCard = document.getElementById('updateStatusCard');
            const statusButton = document.getElementById('statusRefreshButton');
            const manualButton = document.getElementById('manualCheckButton');
            const spinner = document.getElementById('statusRefreshSpinner');
            const statusText = document.getElementById('statusRefreshText');
            const notice = document.getElementById('updateInlineNotice');
            const currentVersionText = document.getElementById('currentVersionText');
            const latestVersionText = document.getElementById('latestVersionText');
            const latestSourceText = document.getElementById('latestSourceText');
            const lastCheckedText = document.getElementById('lastCheckedText');

            if (!statusButton || !manualButton || !spinner || !statusText || !notice) {
                return;
            }

            function setLoading(loading) {
                spinner.classList.toggle('hidden', !loading);
                statusCard?.classList.toggle('animate-pulse', loading);
                statusButton.disabled = loading;
                manualButton.disabled = loading;
                manualButton.classList.toggle('opacity-70', loading);
                manualButton.classList.toggle('cursor-not-allowed', loading);
                if (loading) {
                    statusText.textContent = 'Refreshing...';
                }
            }

            function setNotice(message, isError) {
                notice.textContent = message;
                notice.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'border-red-200', 'bg-red-50', 'text-red-700');
                if (isError) {
                    notice.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
                } else {
                    notice.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                }
            }

            function applyStatusStyle(updateAvailable) {
                statusButton.classList.remove('border-amber-200', 'bg-amber-50', 'text-amber-700', 'hover:bg-amber-100', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'hover:bg-emerald-100');
                if (updateAvailable) {
                    statusButton.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-700', 'hover:bg-amber-100');
                    statusText.textContent = 'Update available - refresh';
                } else {
                    statusButton.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'hover:bg-emerald-100');
                    statusText.textContent = 'Up to date - refresh';
                }
            }

            async function runCheck() {
                setLoading(true);
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        setNotice(data.message || 'Unable to refresh update status.', true);
                        return;
                    }

                    applyStatusStyle(Boolean(data.updateAvailable));
                    setNotice(data.message || 'Update status refreshed.', false);

                    if (currentVersionText) {
                        currentVersionText.textContent = data.currentVersion || 'N/A';
                    }
                    if (latestVersionText) {
                        latestVersionText.textContent = data.latestVersion || 'N/A';
                        latestVersionText.classList.toggle('text-slate-500', !data.latestVersion);
                        latestVersionText.classList.toggle('text-slate-900', Boolean(data.latestVersion));
                    }
                    if (latestSourceText) {
                        latestSourceText.textContent = 'Source: ' + String(data.latestSource || 'unknown').replaceAll('_', ' ');
                    }
                    if (lastCheckedText) {
                        lastCheckedText.textContent = data.lastCheckedAt || 'Just now';
                    }
                } catch (error) {
                    setNotice('Network error while checking updates.', true);
                } finally {
                    setLoading(false);
                }
            }

            statusButton.addEventListener('click', runCheck);
            manualButton.addEventListener('click', runCheck);
        })();
    </script>
</x-layouts.admin>
