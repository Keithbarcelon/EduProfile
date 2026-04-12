@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Edit Student'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Edit — {{ $student->full_name }}</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-emerald-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">Student Registry</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Finalize Student Profile</h2>
            <p class="mt-2 max-w-2xl text-sm text-cyan-100">Linked account details are prefilled. Complete and refine the student information, then save to finalize enrollment records.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-800">{{ $student->full_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $student->student_id }}</p>
                </div>
                <span class="sm:ml-auto inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Linked Profile</span>
            </div>

            <form id="update-student-form" method="POST" action="{{ route('admin.students.update', $student) }}" class="p-6 space-y-6">
                @csrf
                @method('PATCH')

                {{-- Identity --}}
                <div>
                      <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Identity</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                Student ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('student_id') border-red-400 @enderror">
                            @error('student_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('first_name') border-red-400 @enderror">
                            @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('middle_name') border-red-400 @enderror">
                            @error('middle_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('last_name') border-red-400 @enderror">
                            @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $student->suffix) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('suffix') border-red-400 @enderror">
                            @error('suffix')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Contact</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Linked Student User Account</label>
                            <select name="user_id"
                                    class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('user_id') border-red-400 @enderror">
                                <option value="">No linked user account</option>
                                @foreach($studentUsers as $studentUser)
                                <option value="{{ $studentUser->id }}" @selected((string) old('user_id', $student->user_id) === (string) $studentUser->id)>
                                    {{ $studentUser->name }} ({{ $studentUser->email }})
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Only student role user accounts from this tenant can be linked.</p>
                            @error('user_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $student->email) }}"
                                   class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-400 @enderror">
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                   class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea name="address" rows="2"
                                  class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('address', $student->address) }}</textarea>
                    </div>
                </div>

                {{-- Academic --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Academic</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Course <span class="text-red-500">*</span>
                            </label>
                            <select name="course"
                                    class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('course') border-red-400 @enderror">
                                @php
                                    $selectedCourse = old('course', $student->course);
                                    $courseOptions = ['BSCS','BSIT','BSECE','BSN','BSED','BSBA','BSA','BSCE','BSME','BSEE'];
                                @endphp
                                @if($selectedCourse && !in_array($selectedCourse, $courseOptions, true))
                                <option value="{{ $selectedCourse }}" selected>{{ $selectedCourse }}</option>
                                @endif
                                @foreach(['BSCS','BSIT','BSECE','BSN','BSED','BSBA','BSA','BSCE','BSME','BSEE'] as $c)
                                <option value="{{ $c }}" @selected(old('course', $student->course) === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                            @error('course')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Year Level <span class="text-red-500">*</span>
                            </label>
                            <select name="year_level"
                                    class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('year_level') border-red-400 @enderror">
                                @foreach([1,2,3,4,5] as $y)
                                <option value="{{ $y }}" @selected(old('year_level', $student->year_level) == $y)>Year {{ $y }}</option>
                                @endforeach
                            </select>
                            @error('year_level')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Section</label>
                            <input type="text" name="section" value="{{ old('section', $student->section) }}"
                                   class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="active"    @selected(old('status', $student->status) === 'active')>Active</option>
                                <option value="inactive"  @selected(old('status', $student->status) === 'inactive')>Inactive</option>
                                <option value="graduated" @selected(old('status', $student->status) === 'graduated')>Graduated</option>
                                <option value="dropped"   @selected(old('status', $student->status) === 'dropped')>Dropped</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date Enrolled</label>
                            <input type="date" name="enrolled_at"
                                   value="{{ old('enrolled_at', $student->enrolled_at?->toDateString()) }}"
                                   class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                            <select name="gender"
                                    class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Prefer not to say</option>
                                <option value="Male"   @selected(old('gender', $student->gender) === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender', $student->gender) === 'Female')>Female</option>
                                <option value="Other"  @selected(old('gender', $student->gender) === 'Other')>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Personal --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Personal</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Birthdate</label>
                            <input type="date" name="birthdate"
                                   value="{{ old('birthdate', $student->birthdate?->toDateString()) }}"
                                   class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('birthdate') border-red-400 @enderror">
                            @error('birthdate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Family / Emergency --}}
                <div>
                      <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Family and Emergency</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Name</label>
                            <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('guardian_name') border-red-400 @enderror">
                            @error('guardian_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Contact</label>
                            <input type="text" name="guardian_contact" value="{{ old('guardian_contact', $student->guardian_contact) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('guardian_contact') border-red-400 @enderror">
                            @error('guardian_contact')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('emergency_contact_name') border-red-400 @enderror">
                            @error('emergency_contact_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $student->emergency_contact_number) }}"
                            class="w-full text-sm rounded-xl border-slate-300 bg-white focus:ring-indigo-500 focus:border-indigo-500 @error('emergency_contact_number') border-red-400 @enderror">
                            @error('emergency_contact_number')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                    <button type="submit"
                            form="delete-student-form"
                            onclick="return confirm('Permanently delete {{ addslashes($student->full_name) }}?')"
                            class="px-4 py-2.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-colors">
                        Delete Student
                    </button>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.students.show', $student) }}"
                           class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-student-form" method="POST" action="{{ route('admin.students.destroy', $student) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-layouts.admin>
