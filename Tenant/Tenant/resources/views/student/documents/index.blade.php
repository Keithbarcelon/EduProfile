<x-layouts.admin :pageTitle="'My Documents'" :role="'Student'">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">My Documents</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-6 text-white sm:px-8 shadow-xl shadow-indigo-900/20">
            <h2 class="admin-display text-2xl font-bold">Document Upload</h2>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100">Upload your required school documents here. Monitor the status of your submissions.</p>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Upload Form --}}
            <div class="admin-panel rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-4">New Submission</h3>
                <form method="POST" action="{{ route('student.documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Name</label>
                        <input type="text" name="name" required placeholder="e.g. Birth Certificate" 
                               class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select File</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors bg-slate-50 cursor-pointer relative">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-slate-600">
                                    <span class="relative font-medium text-indigo-600 hover:text-indigo-500">Upload a file</span>
                                </div>
                                <p class="text-xs text-slate-500">PDF, PNG, JPG up to 10MB</p>
                            </div>
                            <input type="file" name="file" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-md shadow-indigo-200 transition-all">
                        Submit Document
                    </button>
                </form>
            </div>

            {{-- History --}}
            <div class="lg:col-span-2 admin-panel rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">My Submissions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Document</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($documents as $doc)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-800">{{ $doc->name }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase">{{ $doc->created_at->format('M d, Y') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = match($doc->status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default    => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $badge }}">
                                        {{ $doc->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs italic">
                                    {{ $doc->review_remarks ?? 'Pending review...' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">
                                    No documents submitted yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
