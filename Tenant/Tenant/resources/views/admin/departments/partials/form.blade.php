<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Department Name</label>
        <input type="text" name="name" value="{{ old('name', $department?->name) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
        <input type="text" name="code" value="{{ old('code', $department?->code) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
    <textarea name="description" rows="4" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $department?->description) }}</textarea>
    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="mb-2 block text-sm font-medium text-slate-700">Assign Faculty</label>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        @forelse($facultyMembers as $faculty)
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
            <input type="checkbox" name="faculty_ids[]" value="{{ $faculty->id }}" @checked(in_array($faculty->id, old('faculty_ids', $selectedFacultyIds), true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span>
                <span class="block text-sm font-medium text-slate-800">{{ $faculty->name }}</span>
                <span class="block text-xs text-slate-500">{{ $faculty->email }}</span>
            </span>
        </label>
        @empty
        <p class="text-sm text-slate-400">No faculty users available for assignment.</p>
        @endforelse
    </div>
    @error('faculty_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="flex items-center justify-end gap-3 pt-2">
    <a href="{{ route('admin.departments.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $department ? 'Update Department' : 'Create Department' }}</button>
</div>
