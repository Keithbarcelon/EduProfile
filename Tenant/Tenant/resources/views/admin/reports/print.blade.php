<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Report — {{ $school->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; }
            .print-break-inside-avoid { page-break-inside: avoid; }
            .print-break-after-always { page-break-after: always; }
        }
        @page {
            margin: 2cm;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased p-8">

    <div class="max-w-5xl mx-auto bg-white p-10 shadow-sm border border-slate-200 min-h-[29.7cm]">
        
        {{-- Print Action --}}
        <div class="no-print mb-8 flex justify-between items-center bg-indigo-50 p-4 rounded-xl border border-indigo-100">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 p-2 rounded-lg text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-indigo-700 font-medium">This is a print-optimized view. Press <kbd class="bg-white px-1.5 py-0.5 rounded border border-indigo-200 text-xs">Ctrl + P</kbd> to save as PDF.</p>
            </div>
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print to PDF
            </button>
        </div>

        {{-- Report Header --}}
        <header class="flex justify-between items-start border-b-2 border-slate-900 pb-8 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 uppercase tracking-tighter">{{ $school->name }}</h1>
                <p class="text-slate-500 font-medium mt-1 uppercase tracking-[0.2em] text-xs">Institutional Status Report</p>
                <div class="mt-6 space-y-1">
                    <p class="text-sm text-slate-600"><span class="font-bold text-slate-800">Generated:</span> {{ now()->format('F d, Y — h:i A') }}</p>
                    <p class="text-sm text-slate-600"><span class="font-bold text-slate-800">Authority:</span> {{ $user->name }} ({{ ucfirst($user->role) }})</p>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-slate-900 text-white px-4 py-2 rounded font-bold text-xl mb-2">OFFICIAL</div>
                <p class="text-xs text-slate-400">Reference: RP-{{ now()->format('Ymd') }}-{{ rand(1000, 9999) }}</p>
            </div>
        </header>

        {{-- Executive Summary --}}
        <section class="mb-12">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-l-4 border-indigo-600 pl-3 mb-6">Executive Summary</h2>
            <div class="grid grid-cols-4 gap-6">
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Total Students</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($students->count()) }}</p>
                </div>
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Departments</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $departments->count() }}</p>
                </div>
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Documents Filed</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($students->sum(fn($s) => $s->documents->count())) }}</p>
                </div>
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Compliance Rate</p>
                    @php
                        $compliant = $students->filter(fn($s) => $s->documents->count() > 0)->count();
                        $rate = $students->count() > 0 ? ($compliant / $students->count()) * 100 : 0;
                    @endphp
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ round($rate, 1) }}%</p>
                </div>
            </div>
        </section>

        {{-- Department Breakdown --}}
        <section class="mb-12 print-break-inside-avoid">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-l-4 border-indigo-600 pl-3 mb-6">Departmental Analysis</h2>
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left rounded-l-lg">Department</th>
                        <th class="px-4 py-3 text-center">Code</th>
                        <th class="px-4 py-3 text-center">Students</th>
                        <th class="px-4 py-3 text-center">Faculty</th>
                        <th class="px-4 py-3 text-right rounded-r-lg">Ratio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($departments as $dept)
                    <tr>
                        <td class="px-4 py-4 font-bold text-slate-800">{{ $dept->name }}</td>
                        <td class="px-4 py-4 text-center text-slate-500 font-mono">{{ $dept->code ?? 'N/A' }}</td>
                        <td class="px-4 py-4 text-center font-semibold">{{ $dept->students_count }}</td>
                        <td class="px-4 py-4 text-center">{{ $dept->faculty_count }}</td>
                        <td class="px-4 py-4 text-right text-slate-500">
                            {{ $dept->faculty_count > 0 ? round($dept->students_count / $dept->faculty_count, 1) . ':1' : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Student Records --}}
        <section class="print-break-after-always">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-l-4 border-indigo-600 pl-3 mb-6">Detailed Student Directory</h2>
            <table class="w-full text-[11px] leading-tight">
                <thead class="border-b-2 border-slate-900">
                    <tr>
                        <th class="px-2 py-3 text-left uppercase tracking-tighter">Full Name</th>
                        <th class="px-2 py-3 text-left uppercase tracking-tighter">Email Address</th>
                        <th class="px-2 py-3 text-left uppercase tracking-tighter">Department</th>
                        <th class="px-2 py-3 text-center uppercase tracking-tighter">Category</th>
                        <th class="px-2 py-3 text-center uppercase tracking-tighter">Docs</th>
                        <th class="px-2 py-3 text-right uppercase tracking-tighter">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($students as $student)
                    <tr>
                        <td class="px-2 py-3 font-bold text-slate-900">{{ $student->full_name }}</td>
                        <td class="px-2 py-3 text-slate-500">{{ $student->email }}</td>
                        <td class="px-2 py-3 text-slate-600">{{ $student->department->name ?? 'Unassigned' }}</td>
                        <td class="px-2 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full font-bold uppercase text-[9px] 
                                {{ $student->status_category === 'regular' ? 'bg-emerald-100 text-emerald-700' : 
                                   ($student->status_category === 'affirmative' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $student->status_category }}
                            </span>
                        </td>
                        <td class="px-2 py-3 text-center font-mono">{{ $student->documents->count() }}</td>
                        <td class="px-2 py-3 text-right text-slate-400">{{ $student->created_at?->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Document Status --}}
        <section class="mt-12">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-l-4 border-indigo-600 pl-3 mb-6">Document Compliance Details</h2>
            @foreach($students->filter(fn($s) => $s->documents->isNotEmpty())->chunk(20) as $chunk)
            <div class="grid grid-cols-2 gap-x-10 gap-y-4">
                @foreach($chunk as $student)
                <div class="print-break-inside-avoid border-b border-slate-100 pb-2">
                    <p class="text-[10px] font-bold text-slate-900 uppercase truncate">{{ $student->full_name }}</p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($student->documents as $doc)
                        <div class="flex items-center gap-1 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded text-[8px]">
                            <span class="text-slate-600 font-medium">{{ $doc->document_type ?: 'DOC' }}</span>
                            <span class="w-1 h-1 rounded-full {{ $doc->status === 'approved' ? 'bg-emerald-500' : ($doc->status === 'pending' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                            <span class="text-slate-400 uppercase">{{ $doc->status }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </section>

        <footer class="mt-20 pt-10 border-t border-slate-200 text-center">
            <p class="text-[10px] text-slate-400 uppercase tracking-[0.3em]">End of Report — {{ $school->name }} Confidential</p>
        </footer>
    </div>

</body>
</html>