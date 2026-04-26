<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-6 grid gap-6 md:grid-cols-2">
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">Department Name</label>
            <input type="text" name="name" value="{{ old('name', $department?->name) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Department Code</label>
            <input type="text" name="code" value="{{ old('code', $department?->code) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="4" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $department?->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="mb-3 block text-sm font-medium text-slate-700">Assign Faculty</label>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($facultyMembers as $faculty)
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                    <input type="checkbox" name="faculty_ids[]" value="{{ $faculty->id }}" @checked(in_array($faculty->id, old('faculty_ids', $selectedFacultyIds))) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $faculty->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $faculty->email }}</p>
                    </div>
                </label>
            @endforeach
        </div>
        @error('faculty_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('admin.departments.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $department ? 'Update Department' : 'Create Department' }}</button>
</div>
