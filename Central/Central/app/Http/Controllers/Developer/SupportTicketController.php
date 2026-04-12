<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->with('tenant:id,name,tenant_domain')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('developer.support-tickets.index', [
            'tickets' => $tickets,
            'tenants' => School::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('developer.support-tickets.create', [
            'tenants' => School::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTicket($request);
        SupportTicket::create($validated);

        return redirect()->route('developer.support-tickets.index')
            ->with('success', 'Support ticket created successfully.');
    }

    public function edit(SupportTicket $supportTicket): View
    {
        return view('developer.support-tickets.edit', [
            'ticket' => $supportTicket,
            'tenants' => School::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $validated = $this->validateTicket($request);
        $supportTicket->update($validated);

        return redirect()->route('developer.support-tickets.index')
            ->with('success', 'Support ticket updated successfully.');
    }

    public function destroy(SupportTicket $supportTicket): RedirectResponse
    {
        $supportTicket->delete();

        return redirect()->route('developer.support-tickets.index')
            ->with('success', 'Support ticket deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTicket(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['nullable', Rule::exists('schools', 'id')],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
        ]);
    }
}
