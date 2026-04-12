@php
    $selectedRole = old('role', $userModel?->role);
@endphp

@if($errors->any())
<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    Please review the form fields below.
</div>
@endif

<div x-data="{ role: @js($selectedRole) }" class="space-y-5">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $userModel?->name) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $userModel?->email) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
            <select name="role" x-model="role" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select role</option>
                @foreach($roles as $role)
                <option value="{{ $role }}">{{ \App\Enums\UserRole::labels()[$role] }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Available roles: admission, department, faculty, student.</p>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
            <select name="department_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Unassigned</option>
                @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $userModel?->department_id) === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs" :class="role === 'faculty' ? 'text-amber-600 font-medium' : 'text-slate-500'">
                <span x-show="role === 'faculty'">Department is required for faculty users.</span>
                <span x-show="role !== 'faculty'">Department is optional for non-faculty roles.</span>
            </p>
            @error('department_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Password {{ $userModel ? '(Optional)' : '' }}</label>
            <input type="password" name="password" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ $userModel ? '' : 'required' }}>
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Confirm Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ $userModel ? '' : 'required' }}>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ $userModel ? 'Update User' : 'Create User' }}</button>
    </div>
</div>
