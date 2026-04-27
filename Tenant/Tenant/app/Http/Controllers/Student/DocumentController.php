<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Support\TenantConfig;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

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
        $statusCategory = strtolower((string) ($student->status_category ?: 'regular'));
        $requiredDocumentNames = collect(TenantConfig::requiredDocumentNamesForStatus($statusCategory))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        return view('student.documents.index', compact('documents', 'requiredDocumentNames', 'statusCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $user = Auth::user();
        $student = $user?->resolveStudentProfile();

        if (! $student || (int) $student->user_id !== (int) $user->id) {
            abort(403, 'No student profile found for this account.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'array', 'min:1'],
            'file.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        try {
            $uploadedFiles = $request->file('file', []);

            foreach ($uploadedFiles as $uploadedFile) {
                $path = $uploadedFile->store('documents/' . $student->id, 'public');

                Document::create([
                    'school_id' => (int) app('currentSchool')->id,
                    'student_id' => $student->id,
                    'name' => $validated['name'],
                    'file_path' => $path,
                    'status' => 'pending',
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Student document upload failed.', [
                'user_id' => $user?->id,
                'student_id' => $student->id,
                'tenant_id' => app('currentSchool')->id ?? null,
                'message' => $exception->getMessage(),
            ]);

            throw new HttpResponseException(
                back()->withInput()->withErrors([
                    'file' => 'Upload failed. Please try again or contact support.',
                ])
            );
        }

        return redirect()->route('student.documents.index')
            ->with('success', count($request->file('file', [])) . ' file(s) uploaded successfully and now pending review.');
    }

    public function download(Document $document): BinaryFileResponse|Response
    {
        $this->authorize('view', $document);

        $path = trim((string) $document->file_path);

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Document file not found.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        if (strtolower((string) $document->status) === 'approved') {
            return back()->with('error', 'Approved documents cannot be removed.');
        }

        $path = trim((string) $document->file_path);
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $document->delete();

        return redirect()->route('student.documents.index')
            ->with('success', 'Document removed successfully.');
    }
}
