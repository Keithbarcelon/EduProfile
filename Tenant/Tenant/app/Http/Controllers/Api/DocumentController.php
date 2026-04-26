<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'requirement_id' => 'required|exists:document_requirements,id',
            'file' => 'required|file',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $student = $user->resolveStudentProfile();

        if (!$student) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        try {
            $document = $this->documentService->handleUpload(
                $student, 
                (int) $request->requirement_id, 
                $request->file('file')
            );

            return response()->json($document->load('requirement'), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getStudentDocuments(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $student = $user->resolveStudentProfile();
        
        if (!$student) {
            return response()->json([], 200);
        }

        $documents = Document::where('student_id', $student->id)
            ->with('requirement')
            ->get();

        return response()->json($documents);
    }

    public function review(Request $request): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $document = Document::findOrFail($request->document_id);
        
        $document = $this->documentService->updateStatus(
            $document, 
            $request->status, 
            $request->remarks
        );

        return response()->json($document);
    }
}
