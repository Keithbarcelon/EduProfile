@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
    $sectionMap = collect($profileSections ?? [])->mapWithKeys(fn ($section, $key) => [
        is_array($section) ? (string) ($section['section_key'] ?? $key) : (string) $key => is_array($section) ? $section : [],
    ])->all();

    $sectionEnabled = function (string $key, bool $default = true) use ($sectionMap): bool {
        if (! array_key_exists($key, $sectionMap)) {
            return $default;
        }

        return (bool) ($sectionMap[$key]['enabled'] ?? $default);
    };

    $sectionLabel = function (string $key, string $default) use ($sectionMap): string {
        return (string) ($sectionMap[$key]['label'] ?? $default);
    };
@endphp
<x-layouts.admin :pageTitle="'Edit Student'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.students.index') }}" class="tenant-link transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Edit — {{ $student->full_name }}</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="tenant-hero admin-soft-ring rounded-3xl px-6 py-6 text-white sm:px-8">
            <p class="tenant-hero-kicker text-xs font-semibold uppercase tracking-[0.2em]">Student Registry</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Finalize Student Profile</h2>
            <p class="tenant-hero-body mt-2 max-w-2xl text-sm">Linked account details are prefilled. Complete and refine the student information, then save to finalize enrollment records.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-center gap-3">
                <div class="tenant-primary-soft-bg tenant-primary-text w-10 h-10 rounded-full flex items-center justify-center font-semibold shrink-0">
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

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Profile Sections</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                        @foreach(['basic_info' => 'Basic Info', 'academic_info' => 'Academic Info', 'family_background' => 'Family Background', 'custom_fields' => 'Custom Fields', 'documents' => 'Documents', 'status_history' => 'Status History', 'interventions' => 'Interventions'] as $sectionKey => $defaultLabel)
                            @if($sectionEnabled($sectionKey, $sectionKey === 'basic_info'))
                            <span class="rounded-full bg-white px-2.5 py-1 font-semibold text-slate-700 ring-1 ring-slate-200">{{ $sectionLabel($sectionKey, $defaultLabel) }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if($sectionEnabled('basic_info', true))
                {{-- Identity --}}
                <div>
                      <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">{{ $sectionLabel('basic_info', 'Basic Info') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                Student ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('student_id') border-red-400 @enderror">
                            @error('student_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('first_name') border-red-400 @enderror">
                            @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('middle_name') border-red-400 @enderror">
                            @error('middle_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('last_name') border-red-400 @enderror">
                            @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $student->suffix) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('suffix') border-red-400 @enderror">
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
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('user_id') border-red-400 @enderror">
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
                                   class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('email') border-red-400 @enderror">
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                   class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea name="address" rows="2"
                                  class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white resize-none">{{ old('address', $student->address) }}</textarea>
                    </div>
                </div>

                {{-- Personal --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">Personal</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                                <option value="active"    @selected(old('status', $student->status) === 'active')>Active</option>
                                <option value="inactive"  @selected(old('status', $student->status) === 'inactive')>Inactive</option>
                                <option value="graduated" @selected(old('status', $student->status) === 'graduated')>Graduated</option>
                                <option value="dropped"   @selected(old('status', $student->status) === 'dropped')>Dropped</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status Category</label>
                            <select name="status_category" id="status_category"
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                                <option value="regular" @selected(old('status_category', $student->status_category) === 'regular')>Regular</option>
                                <option value="affirmative" @selected(old('status_category', $student->status_category) === 'affirmative')>Affirmative</option>
                                <option value="probation" @selected(old('status_category', $student->status_category) === 'probation')>Probation</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date Enrolled</label>
                            <input type="date" name="enrolled_at"
                                   value="{{ old('enrolled_at', $student->enrolled_at?->toDateString()) }}"
                                   class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                            <select name="gender"
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                                <option value="">Prefer not to say</option>
                                <option value="Male"   @selected(old('gender', $student->gender) === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender', $student->gender) === 'Female')>Female</option>
                                <option value="Other"  @selected(old('gender', $student->gender) === 'Other')>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Birthdate</label>
                            <input type="date" name="birthdate"
                                   value="{{ old('birthdate', $student->birthdate?->toDateString()) }}"
                                   class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('birthdate') border-red-400 @enderror">
                            @error('birthdate')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                @if($sectionEnabled('academic_info', true))
                {{-- Academic --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">{{ $sectionLabel('academic_info', 'Academic Info') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Course <span class="text-red-500">*</span>
                            </label>
                            <select name="course"
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('course') border-red-400 @enderror">
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
                                    class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('year_level') border-red-400 @enderror">
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
                                   class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Linked Department</label>
                            <select name="department_id" class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('department_id') border-red-400 @enderror">
                                <option value="">Unassigned</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $student->department_id) === (string) $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Advisor</label>
                            <input type="text" value="{{ old('custom_fields.adviser_name', $customFieldValueMap['adviser_name'] ?? '') }}" disabled class="tenant-focus-ring w-full text-sm rounded-xl border-slate-200 bg-slate-50 text-slate-400" placeholder="Use custom field in Academic section">
                        </div>
                    </div>
                </div>
                @endif

                @if($sectionEnabled('family_background', true))
                {{-- Family / Emergency --}}
                <div>
                      <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">{{ $sectionLabel('family_background', 'Family Background') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Name</label>
                            <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('guardian_name') border-red-400 @enderror">
                            @error('guardian_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Guardian Contact</label>
                            <input type="text" name="guardian_contact" value="{{ old('guardian_contact', $student->guardian_contact) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('guardian_contact') border-red-400 @enderror">
                            @error('guardian_contact')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('emergency_contact_name') border-red-400 @enderror">
                            @error('emergency_contact_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $student->emergency_contact_number) }}"
                            class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('emergency_contact_number') border-red-400 @enderror">
                            @error('emergency_contact_number')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($customFieldDefinitions))
                @foreach($customFieldDefinitionsBySection as $sectionKey => $sectionFields)
                @if($sectionEnabled((string) $sectionKey, $sectionKey === 'custom_fields'))
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-4">{{ $sectionLabel((string) $sectionKey, str((string) $sectionKey)->replace('_', ' ')->title()) }} · Custom Fields</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($sectionFields as $field)
                            @php
                                $fieldKey = (string) ($field['field_key'] ?? '');
                                $fieldLabel = (string) ($field['label'] ?? $fieldKey);
                                $fieldType = (string) ($field['field_type'] ?? 'text');
                                $isRequired = (bool) ($field['is_required'] ?? false);
                                $fieldOptions = collect((array) ($field['options'] ?? []))->map(fn ($value) => trim((string) $value))->filter()->values();
                                $visibleStatuses = collect((array) ($field['visible_statuses'] ?? []))->map(fn ($value) => strtolower(trim((string) $value)))->filter()->values();
                                $currentValue = old('custom_fields.' . $fieldKey, $customFieldValueMap[$fieldKey] ?? '');
                            @endphp
                            @if($fieldKey !== '')
                            <div class="{{ $fieldType === 'textarea' ? 'sm:col-span-2' : '' }} tenant-custom-field" data-visible-statuses="{{ $visibleStatuses->implode(',') }}">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ $fieldLabel }}
                                    @if($isRequired)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if($fieldType === 'select')
                                    <select name="custom_fields[{{ $fieldKey }}]" class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('custom_fields.' . $fieldKey) border-red-400 @enderror">
                                        <option value="">Select an option</option>
                                        @foreach($fieldOptions as $option)
                                            <option value="{{ $option }}" @selected((string) $currentValue === (string) $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($fieldType === 'textarea')
                                    <textarea name="custom_fields[{{ $fieldKey }}]" rows="3" class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('custom_fields.' . $fieldKey) border-red-400 @enderror">{{ $currentValue }}</textarea>
                                @else
                                    <input type="{{ $fieldType === 'number' ? 'number' : ($fieldType === 'date' ? 'date' : 'text') }}"
                                           name="custom_fields[{{ $fieldKey }}]"
                                           value="{{ $currentValue }}"
                                           class="tenant-focus-ring w-full text-sm rounded-xl border-slate-300 bg-white @error('custom_fields.' . $fieldKey) border-red-400 @enderror">
                                @endif

                                @error('custom_fields.' . $fieldKey)
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                @endif

                <script>
                    (function () {
                        const statusCategorySelect = document.getElementById('status_category');
                        const fields = Array.from(document.querySelectorAll('.tenant-custom-field'));

                        if (!statusCategorySelect || fields.length === 0) {
                            return;
                        }

                        const applyVisibility = () => {
                            const current = String(statusCategorySelect.value || '').toLowerCase();

                            fields.forEach((field) => {
                                const csv = String(field.getAttribute('data-visible-statuses') || '').trim();
                                if (csv === '') {
                                    field.classList.remove('hidden');
                                    return;
                                }

                                const allowed = csv.split(',').map((v) => v.trim().toLowerCase()).filter(Boolean);
                                const visible = allowed.includes(current);
                                field.classList.toggle('hidden', !visible);
                            });
                        };

                        statusCategorySelect.addEventListener('change', applyVisibility);
                        applyVisibility();
                    })();
                </script>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                    <div></div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.students.show', $student) }}"
                           class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="tenant-primary-btn px-5 py-2.5 text-sm font-medium rounded-xl transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
