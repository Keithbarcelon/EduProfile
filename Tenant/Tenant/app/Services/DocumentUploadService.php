<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentRequirement;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentUploadService
{
    /**
     * Upload a document for a student.
     */
    public function upload(Student $student, DocumentRequirement $requirement, UploadedFile $file): Document
    {
        $schoolId = $student->school_id;
        $studentId = $student->id;
        
        $path = $file->store("documents/{$schoolId}/{$studentId}", 'public');

        return Document::create([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'document_requirement_id' => $requirement->id,
            'name' => $requirement->name,
            'file_path' => $path,
            'status' => 'pending',
        ]);
    }

    /**
     * Review a document (Approve/Reject).
     */
    public function review(Document $document, string $status, ?string $remarks = null): Document
    {
        $document->update([
            'status' => $status,
            'review_remarks' => $remarks,
            'reviewed_by' => auth()->id(),
        ]);

        return $document;
    }

    /**
     * Delete a document.
     */
    public function delete(Document $document): bool
    {
        Storage::disk('public')->delete($document->file_path);
        return $document->delete();
    }
}
