@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Reports & Analytics'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Reports</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-8">
        {{-- Header Section --}}
        <section class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-2xl">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-indigo-400">Analytics Engine</p>
                    <h2 class="admin-display mt-2 text-3xl font-extrabold tracking-tight">Tenant Reports</h2>
                    <p class="mt-3 max-w-2xl text-base text-slate-400 leading-relaxed">
                        Comprehensive insights into student status, department distribution, and document compliance for <span class="text-indigo-300 font-semibold">{{ app('currentSchool')->name }}</span>.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <a href="{{ route('admin.reports.print') }}" target="_blank" class="flex items-center gap-2 rounded-2xl bg-white/10 px-6 py-4 text-sm font-bold text-white transition-all hover:bg-white/20 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Detailed PDF
                    </a>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 shadow-inner">
                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        {{-- Reports Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            
            {{-- Students Per Status --}}
            <div class="group flex flex-col rounded-3xl border border-slate-200 bg-white p-1 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50">
                <div class="flex-1 p-7">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Demographics</span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-900">Student Status</h3>
                    <p class="mt-1 text-sm text-slate-500">Breakdown by enrollment status category.</p>

                    <div class="mt-8 space-y-4">
                        @php
                            $statusColors = [
                                'regular' => 'bg-emerald-100 text-emerald-700',
                                'affirmative' => 'bg-sky-100 text-sky-700',
                                'probation' => 'bg-amber-100 text-amber-700'
                            ];
                        @endphp
                        @foreach($studentStats['by_category'] as $category => $count)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-50 bg-slate-50/50 px-4 py-3.5 transition-colors hover:bg-slate-50">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full {{ $category === 'regular' ? 'bg-emerald-500' : ($category === 'affirmative' ? 'bg-sky-500' : 'bg-amber-500') }}"></span>
                                <span class="text-sm font-semibold text-slate-700">{{ ucfirst($category) }}</span>
                            </div>
                            <span class="text-base font-bold text-slate-900">{{ number_format($count) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="mt-auto p-4 pt-0">
                    <form method="POST" action="{{ route('admin.reports.export') }}">
                        @csrf
                        <input type="hidden" name="report_type" value="status">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-4 text-sm font-bold text-white transition-all hover:bg-slate-800 hover:shadow-lg active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Export Stats
                        </button>
                    </form>
                </div>
            </div>

            {{-- Department Reports --}}
            <div class="group flex flex-col rounded-3xl border border-slate-200 bg-white p-1 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50">
                <div class="flex-1 p-7">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Institutional</span>
                    </div>

                    <h3 class="text-xl font-bold text-slate-900">Departments</h3>
                    <p class="mt-1 text-sm text-slate-500">Distribution across academic units.</p>

                    <div class="mt-8 max-h-[280px] space-y-3 overflow-y-auto custom-scrollbar pr-1">
                        @forelse($departmentReports as $department)
                        <div class="rounded-2xl border border-slate-100 p-4 transition-all hover:border-indigo-200 hover:bg-indigo-50/30">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-800 truncate pr-2">{{ $department->name }}</span>
                                <span class="shrink-0 rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 uppercase tracking-tight">{{ $department->code ?: '??' }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500 font-medium">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ $department->students_count }}
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $department->faculty_count }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="py-10 text-center">
                            <p class="text-sm font-medium text-slate-400">No departments configured.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-auto p-4 pt-0">
                    <form method="POST" action="{{ route('admin.reports.export') }}">
                        @csrf
                        <input type="hidden" name="report_type" value="department">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-4 text-sm font-bold text-white transition-all hover:bg-indigo-700 hover:shadow-lg active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Export Details
                        </button>
                    </form>
                </div>
            </div>

            {{-- Document Compliance --}}
            <div class="group flex flex-col rounded-3xl border border-slate-200 bg-white p-1 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50">
                <div class="flex-1 p-7">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Compliance</span>
                    </div>

                    <h3 class="text-xl font-bold text-slate-900">Document Compliance</h3>
                    <p class="mt-1 text-sm text-slate-500">Monitoring requirements & reviews.</p>

                    <div class="mt-8 space-y-3">
                        <div class="flex items-center justify-between rounded-2xl bg-indigo-50/50 px-4 py-3.5 border border-indigo-50/50">
                            <span class="text-sm font-semibold text-slate-700">Participating Students</span>
                            <span class="text-base font-bold text-indigo-700">{{ $documentCompliance['students_with_documents'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between rounded-2xl bg-rose-50 px-4 py-3.5 border border-rose-100 transition-colors hover:bg-rose-100/60">
                            <span class="text-sm font-semibold text-rose-800">Missing Submissions</span>
                            <span class="text-base font-bold text-rose-700">{{ $documentCompliance['missing_submissions'] }}</span>
                        </div>

                        <div class="pt-2">
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($documentCompliance['by_status'] as $status => $count)
                                <div class="rounded-2xl border border-slate-50 bg-slate-50/50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $status }}</p>
                                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $count }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-auto p-4 pt-0">
                    <form method="POST" action="{{ route('admin.reports.export') }}">
                        @csrf
                        <input type="hidden" name="report_type" value="documents">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-4 text-sm font-bold text-white transition-all hover:bg-emerald-700 hover:shadow-lg active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Export Compliance
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts.admin>

