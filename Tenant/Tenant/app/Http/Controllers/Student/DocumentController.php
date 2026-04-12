<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $user = Auth::user();
        $student = $user->resolveStudentProfile();

        if (! $student) {
            abort(403, 'No student profile found for this account.');
        }

        $documents = $student->documents()->latest()->get();

        return view('student.documents.index', compact('documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $user = Auth::user();
        $student = $user?->resolveStudentProfile();

        if (! $student || (int) $student->user_id !== (int) $user->id) {
            abort(403, 'No student profile found for this account.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('file')->store('documents/' . $student->id, 'public');

        Document::create([
            'school_id' => (int) app('currentSchool')->id,
            'student_id' => $student->id,
            'name' => $request->name,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Document uploaded successfully.');
    }
}
