@props([
    'roleModel' => null,
    'permissions' => collect(),
    'submitLabel' => 'Save Role',
])

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Role Name</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $roleModel?->name) }}"
                required
                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-rose-400 @enderror"
                placeholder="Registrar Officer"
            >
            @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Slug (optional)</label>
            <input
                type="text"
                name="slug"
                value="{{ old('slug', $roleModel?->slug) }}"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('slug') border-rose-400 @enderror"
                placeholder="registrar-officer"
            >
            <p class="mt-1 text-xs text-slate-500">Auto-generated if left blank.</p>
            @error('slug')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Description (optional)</label>
        <textarea
            name="description"
            rows="2"
            class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-rose-400 @enderror"
            placeholder="Manages admissions and student profile validation."
        >{{ old('description', $roleModel?->description) }}</textarea>
        @error('description')
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <p class="mb-2 text-sm font-semibold text-slate-700">Permission Checklist</p>
        <p class="mb-4 text-xs text-slate-500">Select the actions this role can perform.</p>

        <div class="space-y-4">
            @php
                $selectedPermissionIds = collect(old('permission_ids', $roleModel?->permissions?->pluck('id')->all() ?? []))
                    ->map(fn ($id) => (int) $id)
                    ->all();
            @endphp

            @foreach($permissions as $module => $items)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ $module }}</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($items as $permission)
                            <label class="flex items-start gap-2 rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="permission_ids[]"
                                    value="{{ $permission->id }}"
                                    @checked(in_array((int) $permission->id, $selectedPermissionIds, true))
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                >
                                <span>
                                    <span class="block font-semibold">{{ $permission->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $permission->slug }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @error('permission_ids')
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        @error('permission_ids.*')
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.roles.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
            Cancel
        </a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ $submitLabel }}
        </button>
    </div>
</div>
