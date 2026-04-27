@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
    $sectionMap = collect($profileSections ?? [])->mapWithKeys(fn ($section, $key) => [
        is_array($section) ? (string) ($section['section_key'] ?? $key) : (string) $key => is_array($section) ? $section : [],
    ])->all();

    $sectionEnabled = function (string $key, bool $default = true) use ($sectionMap): bool {
        if (! array_key_exists($key, $sectionMap)) {
            return $default;
        }

        return (bool) ($sectionMap[$key]['enabled'] ?? $default);
    };

    $sectionLabel = function (string $key, string $default) use ($sectionMap): string {
        return (string) ($sectionMap[$key]['label'] ?? $default);
    };
@endphp
<x-layouts.admin :pageTitle="$student->full_name" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.students.index') }}" class="tenant-link transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">{{ $student->full_name }}</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="tenant-hero admin-soft-ring rounded-3xl px-6 py-6 text-white sm:px-8">
            <p class="tenant-hero-kicker text-xs font-semibold uppercase tracking-[0.2em]">Student Registry</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Student Information</h2>
            <p class="tenant-hero-body mt-2 max-w-2xl text-sm">Complete student profile summary linked to the account record and academic details.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-start gap-4">
                @if(!empty($student->profile_image_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($student->profile_image_path) }}" alt="{{ $student->full_name }} photo" class="h-14 w-14 rounded-2xl border border-slate-200 object-cover shrink-0">
                @else
                    <div class="tenant-primary-soft-bg tenant-primary-text flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-bold shrink-0">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                @endif

                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $student->full_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $student->student_id }} • {{ $student->course }}</p>

                    @php
                        $badge = match($student->status) {
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'inactive' => 'bg-slate-100 text-slate-700',
                            'graduated' => 'bg-sky-100 text-sky-700',
                            'dropped' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>

                <div class="w-full sm:w-auto sm:ml-auto flex items-center gap-2 justify-start sm:justify-end">
                          <a href="{{ route('admin.students.edit', $student) }}"
                              class="tenant-primary-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            <div class="px-6 pt-5">
                <div class="flex flex-wrap gap-2">
                    @foreach(['basic_info' => 'Basic Info', 'academic_info' => 'Academic Info', 'family_background' => 'Family Background', 'custom_fields' => 'Custom Fields', 'documents' => 'Documents', 'status_history' => 'Status History', 'interventions' => 'Interventions'] as $sectionKey => $defaultLabel)
                        @if($sectionEnabled($sectionKey, $sectionKey === 'basic_info'))
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $sectionLabel($sectionKey, $defaultLabel) }}</span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 gap-5">
                @if($sectionEnabled('basic_info', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('basic_info', 'Basic Info') }}</h3>
                    <div class="mt-4 grid grid-cols-1 gap-x-10 gap-y-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Linked Account</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->user?->name ?? 'Not linked' }}</p>
                    <p class="text-xs text-slate-500">{{ $student->user?->email ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Department</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->department?->name ?? 'Unassigned' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->email }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Phone</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->phone ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gender</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->gender ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Birthdate</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->birthdate?->format('F j, Y') ?? '—' }}</p>
                </div>

                @if($student->address)
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Address</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->address }}</p>
                </div>
                @endif
                    </div>
                </section>
                @endif

                @if($sectionEnabled('academic_info', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('academic_info', 'Academic Info') }}</h3>
                    <div class="mt-4 grid grid-cols-1 gap-x-10 gap-y-6 md:grid-cols-2">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Course</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->course }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">School</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->school?->name ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Year and Section</p>
                    <p class="mt-1 text-sm text-slate-800">Year {{ $student->year_level }}{{ $student->section ? ' • '.$student->section : '' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Category</p>
                    <p class="mt-1 text-sm text-slate-800">{{ ucfirst((string) $student->status_category) }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Date Enrolled</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->enrolled_at?->format('F j, Y') ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Record Created</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->created_at->format('F j, Y') }}</p>
                </div>
                    </div>
                </section>
                @endif

                @if($sectionEnabled('family_background', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('family_background', 'Family Background') }}</h3>
                    <div class="mt-4 grid grid-cols-1 gap-x-10 gap-y-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Guardian</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->guardian_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Guardian Contact</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->guardian_contact ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Emergency Contact</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->emergency_contact_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Emergency Number</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->emergency_contact_number ?: '—' }}</p>
                </div>
                    </div>
                </section>
                @endif

                @if($sectionEnabled('custom_fields', true) && !empty($customFieldDefinitionsBySection))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('custom_fields', 'Custom Fields') }}</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($customFieldDefinitionsBySection as $sectionKey => $sectionFields)
                        @if($sectionEnabled((string) $sectionKey, $sectionKey === 'custom_fields'))
                            @foreach($sectionFields as $field)
                                @php
                                    $fieldKey = (string) ($field['field_key'] ?? '');
                                    $fieldLabel = (string) ($field['label'] ?? $fieldKey);
                                    $fieldValue = $fieldKey !== '' ? ($customFieldValueMap[$fieldKey] ?? null) : null;
                                @endphp
                                @if($fieldKey !== '' && strtolower($fieldKey) !== 'student_id' && $fieldValue !== null && trim((string) $fieldValue) !== '')
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $fieldLabel }}</p>
                                    <p class="mt-1 text-sm text-slate-800">{{ $fieldValue }}</p>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    </div>
                </section>
                @endif

                @if($sectionEnabled('documents', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('documents', 'Documents') }}</h3>
                    <div class="mt-3 text-sm text-slate-700">
                        <p>Total uploads: <span class="font-semibold">{{ $student->documents->count() }}</span></p>
                        <p class="text-xs text-slate-500">Pending: {{ $student->documents->where('status', 'pending')->count() }} · Approved: {{ $student->documents->where('status', 'approved')->count() }} · Rejected: {{ $student->documents->where('status', 'rejected')->count() }}</p>
                    </div>
                </section>
                @endif

                @if($sectionEnabled('status_history', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('status_history', 'Status History') }}</h3>
                    <div class="mt-3 space-y-2">
                        @forelse($student->statusUpdates->take(5) as $statusUpdate)
                        <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-200">
                            <span class="font-semibold">{{ ucfirst((string) $statusUpdate->old_status) }}</span>
                            <span class="text-slate-400">to</span>
                            <span class="font-semibold">{{ ucfirst((string) $statusUpdate->new_status) }}</span>
                            <span class="text-xs text-slate-500">· {{ $statusUpdate->created_at?->format('M d, Y h:i A') }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500">No status history yet.</p>
                        @endforelse
                    </div>
                </section>
                @endif

                @if($sectionEnabled('interventions', true))
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">{{ $sectionLabel('interventions', 'Interventions') }}</h3>
                    <div class="mt-3 space-y-2">
                        @forelse($student->remarks->take(5) as $remark)
                        <div class="rounded-xl bg-white px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-200">
                            <p>{{ $remark->content }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $remark->user?->name ?? 'Staff' }} · {{ $remark->created_at?->format('M d, Y h:i A') }}</p>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500">No intervention notes yet.</p>
                        @endforelse
                    </div>
                </section>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ route('admin.students.index') }}"
                   class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition-colors">
                    Back to Students
                </a>
            </div>
        </div>
    </div>
</x-layouts.admin>
