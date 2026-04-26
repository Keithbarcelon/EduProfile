<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentRequirement;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class DocumentService
{
    /**
     * Handle the complete upload process.
     */
    public function handleUpload(Student $student, int $requirementId, UploadedFile $file): Document
    {
        $this->validateFile($file);
        
        $requirement = DocumentRequirement::findOrFail($requirementId);
        $path = $this->storeFile($student, $file);

        return Document::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'requirement_id' => $requirementId,
            'name' => $requirement->name,
            'file_path' => $path,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Validate file type and size.
     */
    public function validateFile(UploadedFile $file): void
    {
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        
        if (!in_array($file->getMimeType(), $allowed)) {
            throw new Exception('Invalid file type. Only PDF, JPG, and PNG are allowed.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new Exception('File size exceeds 10MB limit.');
        }
    }

    /**
     * Store the file in a structured directory.
     */
    public function storeFile(Student $student, UploadedFile $file): string
    {
        return $file->store("documents/{$student->school_id}/{$student->id}", 'public');
    }

    /**
     * Update document status (Review).
     */
    public function updateStatus(Document $document, string $status, ?string $remarks = null): Document
    {
        $document->update([
            'status' => $status,
            'review_remarks' => $remarks,
            'reviewed_by' => auth()->id(),
        ]);

        return $document;
    }
}
