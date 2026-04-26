<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentRequirementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(DocumentRequirement::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required_for_status' => 'required|string',
        ]);

        $requirement = DocumentRequirement::create([
            'school_id' => auth()->user()->school_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'required_for_status' => $validated['required_for_status'],
            'created_by' => auth()->id(),
        ]);

        return response()->json($requirement, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $requirement = DocumentRequirement::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'required_for_status' => 'sometimes|required|string',
        ]);

        $requirement->update($validated);

        return response()->json($requirement);
    }

    public function destroy(int $id): JsonResponse
    {
        DocumentRequirement::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
