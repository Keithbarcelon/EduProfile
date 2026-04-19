@php
    $model = $appUpdateModel ?? null;
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Version</label>
            <input type="text" name="version" value="{{ old('version', $model?->version) }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Release Date</label>
            <input type="date" name="release_date" value="{{ old('release_date', optional($model?->release_date)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Title</label>
        <input type="text" name="title" value="{{ old('title', $model?->title) }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="5" class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('description', $model?->description) }}</textarea>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Release Document Path (optional URL or storage path)</label>
        <input type="text" name="release_document_path" value="{{ old('release_document_path', $model?->release_document_path) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500" placeholder="https://github.com/... or updates/release-v1.pdf">
    </div>

    <div>
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $model?->is_active ?? true))>
            Active update
        </label>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('developer.app-updates.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</a>
        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel ?? 'Save Update' }}</button>
    </div>
</div>
