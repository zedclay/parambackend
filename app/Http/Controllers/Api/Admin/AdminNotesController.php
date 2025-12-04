<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminNotesController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::with(['module', 'speciality', 'uploader', 'assignedStudent']);

        if ($request->has('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->has('specialite_id')) {
            $query->where('specialite_id', $request->specialite_id);
        }

        if ($request->has('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        $notes = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:51200', // 50MB max
            'module_id' => 'nullable|exists:modules,id',
            'specialite_id' => 'nullable|exists:specialities,id',
            'assigned_student_id' => 'nullable|exists:users,id',
            'visibility' => 'required|in:private,module,specialite',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $file = $request->file('file');
        $storedFilename = $file->hashName();
        // Store in public disk so files are accessible via web
        $filePath = $file->storeAs('notes', $storedFilename, 'public');

        $note = Note::create([
            'title' => $request->title,
            'description' => $request->description,
            'filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'module_id' => $request->module_id,
            'specialite_id' => $request->specialite_id,
            'assigned_student_id' => $request->assigned_student_id,
            'visibility' => $request->visibility,
            'uploader_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $note->load(['module', 'speciality', 'uploader']),
            'message' => 'Note uploaded successfully.',
        ], 201);
    }

    public function show($id)
    {
        $note = Note::with(['module', 'speciality', 'uploader', 'assignedStudent'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
            'specialite_id' => 'nullable|exists:specialities,id',
            'assigned_student_id' => 'nullable|exists:users,id',
            'visibility' => 'sometimes|in:private,module,specialite',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $note->update($request->only([
            'title',
            'description',
            'module_id',
            'specialite_id',
            'assigned_student_id',
            'visibility',
        ]));

        return response()->json([
            'success' => true,
            'data' => $note->fresh()->load(['module', 'speciality', 'uploader']),
            'message' => 'Note updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);

        // Delete file from storage
        Storage::disk('local')->delete($note->file_path);

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully.',
        ]);
    }

    public function assign(Request $request, $id)
    {
        $note = Note::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'exists:users,id',
            'module_id' => 'sometimes|exists:modules,id',
            'specialite_id' => 'sometimes|exists:specialities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $updateData = [];

        if ($request->has('student_ids') && count($request->student_ids) === 1) {
            $updateData['assigned_student_id'] = $request->student_ids[0];
            $updateData['visibility'] = 'private';
        } elseif ($request->has('module_id')) {
            $updateData['module_id'] = $request->module_id;
            $updateData['visibility'] = 'module';
            $updateData['assigned_student_id'] = null;
        } elseif ($request->has('specialite_id')) {
            $updateData['specialite_id'] = $request->specialite_id;
            $updateData['visibility'] = 'specialite';
            $updateData['assigned_student_id'] = null;
        }

        $note->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $note->fresh(),
            'message' => 'Note assigned successfully.',
        ]);
    }

    public function stats($id)
    {
        $note = Note::findOrFail($id);

        $downloads = DownloadLog::where('note_id', $id)
            ->with('student')
            ->orderBy('downloaded_at', 'desc')
            ->get();

        $uniqueDownloaders = $downloads->unique('student_id')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'note' => $note,
                'total_downloads' => $note->download_count,
                'unique_downloaders' => $uniqueDownloaders,
                'downloads' => $downloads,
            ],
        ]);
    }

    public function bulkUpload(Request $request)
    {
        // TODO: Implement bulk upload with ZIP + CSV
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'NOT_IMPLEMENTED',
                'message' => 'Bulk upload feature coming soon.',
            ],
        ], 501);
    }
}
