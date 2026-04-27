<x-layouts.admin :pageTitle="'My Documents'" :role="'Student'">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">My Documents</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please review the upload form and try again.
            </div>
        @endif

        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-6 text-white sm:px-8 shadow-xl shadow-indigo-900/20">
            <h2 class="admin-display text-2xl font-bold">Document Upload</h2>
            <p class="mt-2 max-w-2xl text-sm text-indigo-100">Upload your required school documents here. Monitor the status of your submissions.</p>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            {{-- Upload Form --}}
            <div class="admin-panel rounded-2xl border border-slate-100 bg-white p-6 shadow-sm xl:col-span-4">
                <h3 class="text-lg font-bold text-slate-800 mb-4">New Submission</h3>
                <form method="POST" action="{{ route('student.documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Document Name</label>
                        @if(($requiredDocumentNames ?? collect())->isNotEmpty())
                            <select name="name" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Select a required document</option>
                                @foreach($requiredDocumentNames as $requiredName)
                                <option value="{{ $requiredName }}" @selected(old('name') === $requiredName)>{{ $requiredName }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Required for your current status: {{ ucfirst($statusCategory ?? 'regular') }}.</p>
                        @else
                            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Birth Certificate" 
                                class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="mt-1 text-xs text-slate-500">No required document list is configured for your current status yet.</p>
                        @endif
                        @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select File(s)</label>
                        <div class="mt-1 relative cursor-pointer rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 pb-6 pt-5 transition-colors hover:border-indigo-400">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-slate-600">
                                    <span class="relative font-medium text-indigo-600 hover:text-indigo-500">Upload a file</span>
                                </div>
                                <p class="text-xs text-slate-500">PDF, DOC, DOCX, PNG, JPG up to 10MB each</p>
                            </div>
                            <input id="student_file_input" type="file" name="file[]" multiple required class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        </div>
                        <p id="selected_file_text" class="mt-2 text-xs text-slate-500">No files selected yet.</p>
                        @error('file')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('file.*')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="upload_preview_panel" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">Selected Files Preview</p>
                            <button id="clear_selected_file" type="button" class="rounded-md bg-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-300">Clear</button>
                        </div>
                        <div id="preview_items" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"></div>
                        <p class="mt-2 text-[11px] text-slate-500">Tip: for front/back uploads, confirm both files appear before submitting.</p>
                    </div>

                    <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-[11px] text-indigo-700">
                        You can upload multiple files in one submission (example: ID front and ID back).
                        Each selected file will be saved as a separate record under the same document name.
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                        Accepted formats: PDF, DOC, DOCX, PNG, JPG, JPEG. Maximum 10MB per file.
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-md shadow-indigo-200 transition-all">
                        Submit Document(s)
                    </button>
                </form>
            </div>

            {{-- History --}}
            <div class="admin-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm xl:col-span-8">
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
                                <th class="px-6 py-3 text-left">File</th>
                                <th class="px-6 py-3 text-left">Action</th>
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
                                <td class="px-6 py-4">
                                    <a href="{{ route('student.documents.download', $doc) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                                        View File
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if($doc->status === 'approved')
                                        <span class="text-[11px] font-semibold text-slate-500">Locked after approval</span>
                                    @else
                                        <form method="POST" action="{{ route('student.documents.destroy', $doc) }}" onsubmit="return confirm('Remove this uploaded file?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">
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

    <template id="preview_item_template">
        <div class="rounded-lg border border-slate-200 bg-white p-2">
            <div class="preview-media mb-2"></div>
            <p class="preview-name truncate text-xs font-semibold text-slate-700"></p>
            <p class="preview-meta text-[11px] text-slate-500"></p>
        </div>
    </template>

    <script>
        (function () {
            const fileInput = document.getElementById('student_file_input');
            const selectedFileText = document.getElementById('selected_file_text');
            const previewPanel = document.getElementById('upload_preview_panel');
            const previewItems = document.getElementById('preview_items');
            const clearButton = document.getElementById('clear_selected_file');
            const previewTemplate = document.getElementById('preview_item_template');

            if (!fileInput || !previewItems || !previewTemplate) {
                return;
            }

            const previewUrls = [];

            function bytesToSize(bytes) {
                if (!Number.isFinite(bytes) || bytes <= 0) {
                    return '0 B';
                }

                const units = ['B', 'KB', 'MB', 'GB'];
                const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                const value = bytes / Math.pow(1024, i);
                return `${value.toFixed(i === 0 ? 0 : 2)} ${units[i]}`;
            }

            function clearPreviewState(resetInput = false) {
                while (previewUrls.length > 0) {
                    const url = previewUrls.pop();
                    URL.revokeObjectURL(url);
                }

                previewItems.innerHTML = '';
                previewPanel?.classList.add('hidden');

                if (selectedFileText) {
                    selectedFileText.textContent = 'No files selected yet.';
                }

                if (resetInput) {
                    fileInput.value = '';
                }
            }

            function buildPreviewItem(file) {
                const node = previewTemplate.content.firstElementChild.cloneNode(true);
                const mediaContainer = node.querySelector('.preview-media');
                const nameNode = node.querySelector('.preview-name');
                const metaNode = node.querySelector('.preview-meta');
                const previewUrl = URL.createObjectURL(file);

                previewUrls.push(previewUrl);

                if (file.type.startsWith('image/')) {
                    mediaContainer.innerHTML = `<img src="${previewUrl}" alt="${file.name}" class="h-28 w-full rounded-md border border-slate-200 bg-slate-50 object-contain p-1">`;
                } else if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                    mediaContainer.innerHTML = `<iframe src="${previewUrl}" class="h-28 w-full rounded-md border border-slate-200 bg-white"></iframe>`;
                } else {
                    mediaContainer.innerHTML = '<div class="flex h-28 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-[11px] text-slate-500">No inline preview</div>';
                }

                nameNode.textContent = file.name;
                metaNode.textContent = `${file.type || 'Unknown type'} • ${bytesToSize(file.size)}`;

                return node;
            }

            fileInput.addEventListener('change', function () {
                const files = Array.from(fileInput.files || []);

                if (files.length === 0) {
                    clearPreviewState(false);
                    return;
                }

                clearPreviewState(false);

                if (selectedFileText) {
                    selectedFileText.textContent = `${files.length} file(s) selected`;
                }

                files.forEach((file) => {
                    previewItems.appendChild(buildPreviewItem(file));
                });

                previewPanel?.classList.remove('hidden');
            });

            clearButton?.addEventListener('click', function () {
                clearPreviewState(true);
            });
        })();
    </script>
</x-layouts.admin>
