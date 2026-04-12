<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Remark;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RemarkController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('create', [Remark::class, $student]);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        Remark::create([
            'student_id' => $student->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Remark added successfully.');
    }

    public function destroy(Remark $remark): RedirectResponse
    {
        $this->authorize('delete', $remark);

        $remark->delete();

        return back()->with('success', 'Remark deleted successfully.');
    }
}
