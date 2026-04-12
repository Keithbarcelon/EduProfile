@php
    $ticketModel = $ticket ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Tenant (optional)</label>
        <select name="tenant_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            <option value="">General</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected((string) old('tenant_id', $ticketModel?->tenant_id) === (string) $tenant->id)>
                    {{ $tenant->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Subject</label>
        <input type="text" name="subject" value="{{ old('subject', $ticketModel?->subject) }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Message</label>
        <textarea name="message" rows="5" required class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('message', $ticketModel?->message) }}</textarea>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $ticketModel?->status ?? 'open') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('developer.support-tickets.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</a>
        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel ?? 'Save Ticket' }}</button>
    </div>
</div>
