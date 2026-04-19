<x-layouts.admin :pageTitle="'Tenant Customization'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('developer.tenants.show', $tenant) }}" class="hover:text-gray-600 dark:hover:text-gray-200">{{ $tenant->name }}</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Customization</span>
    </x-slot>

    <div class="space-y-5">
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
            <h2 class="text-base font-semibold text-cyan-800 dark:text-cyan-200">{{ $tenant->name }} Customization</h2>
            <p class="mt-1 text-sm text-cyan-700/90 dark:text-cyan-300">Configure modules, profile sections, custom fields, documents, and workflow in one place.</p>
            <p class="mt-2 text-xs text-cyan-700/90 dark:text-cyan-300">Central customization governs tenant structure and allowed capabilities. Tenant Admin handles day-to-day operations inside the provisioned structure.</p>
        </div>

        <form method="POST" action="{{ route('developer.tenants.customization.update', $tenant) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <section class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-3 shadow-xl">
                <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Customization Tabs</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5" id="customization-tabs">
                    <button type="button" data-tab-target="modules" class="customization-tab-btn rounded-lg border border-cyan-500 bg-cyan-600 px-3 py-2 text-xs font-semibold text-white">Modules</button>
                    <button type="button" data-tab-target="profile-sections" class="customization-tab-btn rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700">Profile Sections</button>
                    <button type="button" data-tab-target="custom-fields" class="customization-tab-btn rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700">Custom Fields</button>
                    <button type="button" data-tab-target="documents" class="customization-tab-btn rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700">Documents</button>
                    <button type="button" data-tab-target="workflow" class="customization-tab-btn rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700">Workflow</button>
                </div>
            </section>

            <section data-tab-panel="modules" class="customization-tab-panel rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Enabled Modules</h3>
                @php
                    $coreModules = $modules->filter(fn ($module) => (bool) $module->is_core)->values();
                    $optionalModules = $modules->filter(fn ($module) => !(bool) $module->is_core && (string) $module->key !== 'support_updates')->values();
                @endphp

                <div class="mt-4 rounded-xl border border-slate-700 bg-slate-800/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">Core Backbone (Always Enabled)</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($coreModules as $module)
                    @php
                        $isEnabled = array_key_exists($module->id, $moduleMap)
                            ? (bool) $moduleMap[$module->id]
                            : (bool) $module->default_enabled;
                        $isEnabled = true;
                    @endphp
                    <label class="flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-3 text-sm text-slate-100">
                        <input type="hidden" name="modules[{{ $module->id }}]" value="1">
                        <input type="checkbox" checked disabled class="mt-0.5 rounded border-slate-500 bg-slate-900 text-cyan-500 opacity-80">
                        <span>
                            <span class="font-semibold">{{ $module->name }}</span>
                            <span class="mt-0.5 block text-xs text-slate-400">
                                {{ str($module->category)->replace('_', ' ')->title() }} · Core · Always enabled
                            </span>
                        </span>
                    </label>
                    @endforeach
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-slate-700 bg-slate-800/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Optional Controls (Tenant Configurable)</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($optionalModules as $module)
                    @php
                        $isEnabled = array_key_exists($module->id, $moduleMap)
                            ? (bool) $moduleMap[$module->id]
                            : (bool) $module->default_enabled;
                    @endphp
                    <label class="flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-3 text-sm text-slate-100">
                        <input type="hidden" name="modules[{{ $module->id }}]" value="0">
                        <input type="checkbox" name="modules[{{ $module->id }}]" value="1" @checked($isEnabled) class="mt-0.5 rounded border-slate-500 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                        <span>
                            <span class="font-semibold">{{ $module->name }}</span>
                            <span class="mt-0.5 block text-xs text-slate-400">{{ str($module->category)->replace('_', ' ')->title() }}</span>
                        </span>
                    </label>
                    @empty
                    <p class="text-xs text-slate-400">No optional modules available.</p>
                    @endforelse
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-slate-700 bg-slate-800/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">Platform Utility</p>
                    <p class="mt-2 text-xs text-slate-300"><span class="font-semibold text-slate-100">Support and Updates</span> is managed as a platform utility and stays enabled by default.</p>
                </div>
            </section>

            <section data-tab-panel="profile-sections" class="customization-tab-panel hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Student Profile Sections</h3>
                <p class="mt-1 text-xs text-slate-400">Enable or hide profile cards/tabs per tenant and customize section labels.</p>

                <div class="mt-4 space-y-3" id="profile-sections-builder">
                    @foreach($profileSections as $index => $section)
                    <div class="grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-800/70 p-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Section Key</label>
                            <input type="text" name="profile_sections[{{ $index }}][section_key]" value="{{ $section['section_key'] }}" readonly class="w-full rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-300">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Label</label>
                            <input type="text" name="profile_sections[{{ $index }}][label]" value="{{ old('profile_sections.' . $index . '.label', $section['label']) }}" class="w-full rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Enabled</label>
                            <div class="flex h-[30px] items-center gap-2 rounded-md border border-slate-600 bg-slate-900 px-2">
                                <input type="hidden" name="profile_sections[{{ $index }}][enabled]" value="0">
                                <input type="checkbox" name="profile_sections[{{ $index }}][enabled]" value="1" @checked((bool) old('profile_sections.' . $index . '.enabled', $section['enabled'])) class="rounded border-slate-500 bg-slate-900 text-cyan-500 focus:ring-cyan-500">
                                <span class="text-xs text-slate-300">Visible</span>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sort Order</label>
                            <input type="number" min="1" max="999" name="profile_sections[{{ $index }}][sort_order]" value="{{ old('profile_sections.' . $index . '.sort_order', $section['sort_order']) }}" class="w-full rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <section data-tab-panel="custom-fields" class="customization-tab-panel hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Phase 2: Student Custom Fields</h3>
                <p class="mt-1 text-xs text-slate-400">Simple Mode: add rows below. You only need Label + Required for most setups.</p>

                <div class="mt-4 space-y-3" id="custom-fields-builder"></div>

                <div class="mt-3 flex items-center gap-2">
                    <button type="button" id="add-custom-field" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Add Field</button>
                    <button type="button" id="toggle-custom-fields-advanced" class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-600">Show Advanced</button>
                </div>

                <div id="custom-fields-advanced-wrap" class="mt-3 hidden">
                    <p class="text-xs text-slate-400">Advanced (optional): Label|type|required(1/0)|option1,option2|visible_statuses(optional csv)|section_key</p>
                    <textarea id="custom_fields_text" name="custom_fields_text" rows="6" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 font-mono text-xs text-slate-100 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Nickname|text|0|||custom_fields&#10;Adviser Name|text|1||probation,affirmative|academic_info&#10;Learning Mode|select|1|Online,Hybrid,On-site||academic_info">{{ $customFieldLines }}</textarea>
                </div>
            </section>

            <section data-tab-panel="documents" class="customization-tab-panel hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Phase 2: Required Documents</h3>
                <p class="mt-1 text-xs text-slate-400">Simple Mode: add required documents and assign scope quickly.</p>

                <div class="mt-4 space-y-3" id="documents-builder"></div>

                <div class="mt-3 flex items-center gap-2">
                    <button type="button" id="add-document" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Add Document</button>
                    <button type="button" id="toggle-documents-advanced" class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-600">Show Advanced</button>
                </div>

                <div id="documents-advanced-wrap" class="mt-3 hidden">
                    <p class="text-xs text-slate-400">Advanced (optional): Document Name|status(*,regular,affirmative,probation)|required(1/0)</p>
                    <textarea id="document_requirements_text" name="document_requirements_text" rows="6" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 font-mono text-xs text-slate-100 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Letter of Explanation|*|1&#10;Commitment Form|affirmative|1&#10;Intervention Contract|probation|1">{{ $documentRequirementLines }}</textarea>
                </div>
            </section>

            <section data-tab-panel="workflow" class="customization-tab-panel hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Phase 3: Status Workflow Steps</h3>
                <p class="mt-1 text-xs text-slate-400">Simple Mode: line order is your approval order.</p>

                <div class="mt-4 space-y-3" id="workflow-builder"></div>

                <div class="mt-3 flex items-center gap-2">
                    <button type="button" id="add-workflow-step" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Add Step</button>
                    <button type="button" id="toggle-workflow-advanced" class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-600">Show Advanced</button>
                </div>

                <div id="workflow-advanced-wrap" class="mt-3 hidden">
                    <p class="text-xs text-slate-400">Advanced (optional): role_slug|Step Name</p>
                    <textarea id="workflow_steps_text" name="workflow_steps_text" rows="6" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 font-mono text-xs text-slate-100 focus:border-cyan-500 focus:ring-cyan-500" placeholder="department|Department Review&#10;tenant_admin|Final Approval">{{ $workflowStepLines }}</textarea>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('developer.tenants.show', $tenant) }}" class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">Back</a>
                <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Save Customization</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const form = document.querySelector('form[action*="customization"]');
            if (!form) return;

            const customFieldsText = document.getElementById('custom_fields_text');
            const documentsText = document.getElementById('document_requirements_text');
            const workflowText = document.getElementById('workflow_steps_text');

            const customFieldsBuilder = document.getElementById('custom-fields-builder');
            const documentsBuilder = document.getElementById('documents-builder');
            const workflowBuilder = document.getElementById('workflow-builder');
            const tabButtons = Array.from(document.querySelectorAll('.customization-tab-btn'));
            const tabPanels = Array.from(document.querySelectorAll('.customization-tab-panel'));

            const statusOptions = ['regular', 'affirmative', 'probation'];
            const roles = ['admission', 'department', 'faculty', 'tenant_admin'];

            function escapeHtml(input) {
                return String(input || '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function normalizeLines(raw) {
                return String(raw || '')
                    .split(/\r\n|\n|\r/)
                    .map((line) => line.trim())
                    .filter(Boolean);
            }

            function parseCustomFieldLines(raw) {
                return normalizeLines(raw).map((line) => {
                    const parts = line.split('|').map((v) => v.trim());

                    // legacy with order+key
                    if (parts.length >= 7 && /^\d+$/.test(parts[0])) {
                        return {
                            label: parts[2] || '',
                            type: (parts[3] || 'text').toLowerCase(),
                            required: ['1', 'true', 'yes'].includes((parts[4] || '').toLowerCase()),
                            options: parts[5] || '',
                            visible: parts[6] || '',
                            section: (parts[7] || 'custom_fields').toLowerCase(),
                        };
                    }

                    // legacy with key
                    if (parts.length >= 6 && !['text', 'number', 'date', 'select', 'textarea'].includes((parts[1] || '').toLowerCase())) {
                        return {
                            label: parts[1] || '',
                            type: (parts[2] || 'text').toLowerCase(),
                            required: ['1', 'true', 'yes'].includes((parts[3] || '').toLowerCase()),
                            options: parts[4] || '',
                            visible: parts[5] || '',
                            section: (parts[6] || 'custom_fields').toLowerCase(),
                        };
                    }

                    // simple
                    return {
                        label: parts[0] || '',
                        type: (parts[1] || 'text').toLowerCase(),
                        required: ['1', 'true', 'yes'].includes((parts[2] || '').toLowerCase()),
                        options: parts[3] || '',
                        visible: parts[4] || '',
                        section: (parts[5] || 'custom_fields').toLowerCase(),
                    };
                });
            }

            function parseDocumentLines(raw) {
                return normalizeLines(raw).map((line) => {
                    const parts = line.split('|').map((v) => v.trim());

                    if (parts.length >= 3 && ['*', 'regular', 'affirmative', 'probation'].includes((parts[0] || '').toLowerCase())) {
                        return {
                            name: parts[1] || '',
                            status: (parts[0] || '*').toLowerCase(),
                            required: !['0', 'false', 'no'].includes((parts[2] || '').toLowerCase()),
                        };
                    }

                    return {
                        name: parts[0] || '',
                        status: (parts[1] || '*').toLowerCase(),
                        required: !['0', 'false', 'no'].includes((parts[2] || '1').toLowerCase()),
                    };
                });
            }

            function parseWorkflowLines(raw) {
                return normalizeLines(raw).map((line, idx) => {
                    const parts = line.split('|').map((v) => v.trim());

                    if (parts.length >= 3 && /^\d+$/.test(parts[0])) {
                        return { role: (parts[1] || '').toLowerCase(), name: parts[2] || '' };
                    }

                    return { role: (parts[0] || '').toLowerCase(), name: parts[1] || `Step ${idx + 1}` };
                });
            }

            function addCustomFieldRow(data = { label: '', type: 'text', required: false, options: '', visible: '', section: 'custom_fields' }) {
                const wrapper = document.createElement('div');
                wrapper.className = 'grid grid-cols-1 gap-2 rounded-lg border border-slate-700 bg-slate-800/70 p-3 md:grid-cols-7';
                wrapper.innerHTML = `
                    <input type="text" data-cf="label" value="${escapeHtml(data.label)}" placeholder="Label" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                    <select data-cf="type" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        ${['text','number','date','select','textarea'].map((type) => `<option value="${type}" ${type === data.type ? 'selected' : ''}>${type}</option>`).join('')}
                    </select>
                    <label class="inline-flex items-center gap-1 text-xs text-slate-200"><input data-cf="required" type="checkbox" ${data.required ? 'checked' : ''}> Required</label>
                    <input type="text" data-cf="options" value="${escapeHtml(data.options)}" placeholder="Options (for select)" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                    <input type="text" data-cf="visible" value="${escapeHtml(data.visible)}" placeholder="Visible statuses (optional)" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                    <select data-cf="section" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        ${['basic_info','academic_info','family_background','custom_fields','documents','status_history','interventions'].map((section) => `<option value="${section}" ${section === (data.section || 'custom_fields') ? 'selected' : ''}>${section}</option>`).join('')}
                    </select>
                    <button type="button" data-remove class="rounded-md bg-rose-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">Remove</button>
                `;
                wrapper.querySelector('[data-remove]')?.addEventListener('click', () => wrapper.remove());
                customFieldsBuilder?.appendChild(wrapper);
            }

            function addDocumentRow(data = { name: '', status: '*', required: true }) {
                const wrapper = document.createElement('div');
                wrapper.className = 'grid grid-cols-1 gap-2 rounded-lg border border-slate-700 bg-slate-800/70 p-3 md:grid-cols-4';
                wrapper.innerHTML = `
                    <input type="text" data-doc="name" value="${escapeHtml(data.name)}" placeholder="Document Name" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                    <select data-doc="status" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        ${['*','regular','affirmative','probation'].map((status) => `<option value="${status}" ${status === data.status ? 'selected' : ''}>${status}</option>`).join('')}
                    </select>
                    <label class="inline-flex items-center gap-1 text-xs text-slate-200"><input data-doc="required" type="checkbox" ${data.required ? 'checked' : ''}> Required</label>
                    <button type="button" data-remove class="rounded-md bg-rose-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">Remove</button>
                `;
                wrapper.querySelector('[data-remove]')?.addEventListener('click', () => wrapper.remove());
                documentsBuilder?.appendChild(wrapper);
            }

            function addWorkflowRow(data = { role: 'department', name: '' }) {
                const wrapper = document.createElement('div');
                wrapper.className = 'grid grid-cols-1 gap-2 rounded-lg border border-slate-700 bg-slate-800/70 p-3 md:grid-cols-3';
                wrapper.innerHTML = `
                    <select data-wf="role" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                        ${roles.map((role) => `<option value="${role}" ${role === data.role ? 'selected' : ''}>${role}</option>`).join('')}
                    </select>
                    <input type="text" data-wf="name" value="${escapeHtml(data.name)}" placeholder="Step name" class="rounded-md border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100">
                    <button type="button" data-remove class="rounded-md bg-rose-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">Remove</button>
                `;
                wrapper.querySelector('[data-remove]')?.addEventListener('click', () => wrapper.remove());
                workflowBuilder?.appendChild(wrapper);
            }

            function hydrateFromTextareas() {
                customFieldsBuilder.innerHTML = '';
                documentsBuilder.innerHTML = '';
                workflowBuilder.innerHTML = '';

                const customFields = parseCustomFieldLines(customFieldsText?.value || '');
                const docs = parseDocumentLines(documentsText?.value || '');
                const workflow = parseWorkflowLines(workflowText?.value || '');

                (customFields.length ? customFields : [{ label: '', type: 'text', required: false, options: '', visible: '' }]).forEach(addCustomFieldRow);
                (docs.length ? docs : [{ name: '', status: '*', required: true }]).forEach(addDocumentRow);
                (workflow.length ? workflow : [{ role: 'department', name: 'Department Review' }, { role: 'tenant_admin', name: 'Final Approval' }]).forEach(addWorkflowRow);
            }

            function writeBuildersToTextareas() {
                const customLines = Array.from(customFieldsBuilder.querySelectorAll(':scope > div')).map((row) => {
                    const label = row.querySelector('[data-cf="label"]')?.value?.trim() || '';
                    const type = row.querySelector('[data-cf="type"]')?.value?.trim() || 'text';
                    const required = row.querySelector('[data-cf="required"]')?.checked ? '1' : '0';
                    const options = row.querySelector('[data-cf="options"]')?.value?.trim() || '';
                    const visible = row.querySelector('[data-cf="visible"]')?.value?.trim() || '';
                    const section = row.querySelector('[data-cf="section"]')?.value?.trim() || 'custom_fields';
                    if (!label) return '';
                    return [label, type, required, options, visible, section].join('|');
                }).filter(Boolean);

                const docLines = Array.from(documentsBuilder.querySelectorAll(':scope > div')).map((row) => {
                    const name = row.querySelector('[data-doc="name"]')?.value?.trim() || '';
                    const status = row.querySelector('[data-doc="status"]')?.value?.trim() || '*';
                    const required = row.querySelector('[data-doc="required"]')?.checked ? '1' : '0';
                    if (!name) return '';
                    return [name, status, required].join('|');
                }).filter(Boolean);

                const wfLines = Array.from(workflowBuilder.querySelectorAll(':scope > div')).map((row) => {
                    const role = row.querySelector('[data-wf="role"]')?.value?.trim() || '';
                    const name = row.querySelector('[data-wf="name"]')?.value?.trim() || '';
                    if (!role) return '';
                    return [role, name].join('|');
                }).filter(Boolean);

                if (customFieldsText) customFieldsText.value = customLines.join('\n');
                if (documentsText) documentsText.value = docLines.join('\n');
                if (workflowText) workflowText.value = wfLines.join('\n');
            }

            function hookToggle(buttonId, wrapId) {
                const button = document.getElementById(buttonId);
                const wrap = document.getElementById(wrapId);
                if (!button || !wrap) return;

                button.addEventListener('click', () => {
                    wrap.classList.toggle('hidden');
                    button.textContent = wrap.classList.contains('hidden') ? 'Show Advanced' : 'Hide Advanced';
                });
            }

            document.getElementById('add-custom-field')?.addEventListener('click', () => addCustomFieldRow());
            document.getElementById('add-document')?.addEventListener('click', () => addDocumentRow());
            document.getElementById('add-workflow-step')?.addEventListener('click', () => addWorkflowRow());

            function activateTab(tabKey) {
                const target = String(tabKey || '').trim();
                if (!target) {
                    return;
                }

                tabPanels.forEach((panel) => {
                    const panelKey = panel.getAttribute('data-tab-panel');
                    panel.classList.toggle('hidden', panelKey !== target);
                });

                tabButtons.forEach((button) => {
                    const isActive = button.getAttribute('data-tab-target') === target;
                    button.classList.toggle('bg-cyan-600', isActive);
                    button.classList.toggle('border-cyan-500', isActive);
                    button.classList.toggle('text-white', isActive);
                    button.classList.toggle('bg-slate-800', !isActive);
                    button.classList.toggle('border-slate-600', !isActive);
                    button.classList.toggle('text-slate-200', !isActive);
                });
            }

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-tab-target');
                    activateTab(target);
                });
            });

            hookToggle('toggle-custom-fields-advanced', 'custom-fields-advanced-wrap');
            hookToggle('toggle-documents-advanced', 'documents-advanced-wrap');
            hookToggle('toggle-workflow-advanced', 'workflow-advanced-wrap');

            hydrateFromTextareas();
            activateTab('modules');

            form.addEventListener('submit', () => {
                writeBuildersToTextareas();
            });
        })();
    </script>
</x-layouts.admin>
