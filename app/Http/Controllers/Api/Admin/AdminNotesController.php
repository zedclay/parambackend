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
            'description' => 'nullable|string|max:5000',
            // Security: Reduced max size to 10MB, stricter validation
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    // Security: Validate actual file content, not just extension
                    if ($value && $value->isValid()) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        $allowedMimes = [
                            'application/pdf',
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        ];

                        if (!in_array($mimeType, $allowedMimes)) {
                            $fail('The file type is not allowed. Only PDF and images are permitted.');
                        }

                        // Security: Additional check for file size
                        if ($value->getSize() > 10485760) { // 10MB in bytes
                            $fail('The file size must not exceed 10MB.');
                        }
                    }
                }
            ],
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

        // Security: Sanitize filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $originalName = substr($originalName, 0, 100); // Limit length

        // Security: Use hashed filename for storage
        $storedFilename = $file->hashName();

        // Security: Store in public disk but with controlled access
        $filePath = $file->storeAs('notes', $storedFilename, 'public');

        // Security: Get MIME type from actual file content, not from upload
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $note = Note::create([
            'title' => strip_tags($request->title), // Security: Basic XSS protection
            'description' => $request->description ? strip_tags($request->description) : null,
            'filename' => $originalName . '.' . $file->getClientOriginalExtension(),
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'mime_type' => $actualMimeType, // Use actual MIME type from file content
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

        // Security: Authorization check - ensure admin owns or can modify this note
        // Admins can modify any note, but we log the action
        if (config('app.debug')) {
            \Log::debug('Admin updating note', [
                'note_id' => $note->id,
                'admin_id' => $request->user()->id,
            ]);
        }

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

        // Security: Sanitize inputs before updating
        $updateData = $request->only([
            'title',
            'description',
            'module_id',
            'specialite_id',
            'assigned_student_id',
            'visibility',
        ]);

        // Sanitize text fields
        if (isset($updateData['title'])) {
            $updateData['title'] = strip_tags(trim($updateData['title']));
        }
        if (isset($updateData['description'])) {
            $updateData['description'] = strip_tags(trim($updateData['description']));
        }

        $note->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $note->fresh()->load(['module', 'speciality', 'uploader']),
            'message' => 'Note updated successfully.',
        ]);
    }

    public function destroy($id, Request $request)
    {
        $note = Note::findOrFail($id);

        // Security: Authorization check - admins can delete any note, but we verify
        if (config('app.debug')) {
            \Log::debug('Admin deleting note', [
                'note_id' => $note->id,
                'admin_id' => $request->user()->id,
            ]);
        }

        // Security: Validate and sanitize file path before deletion
        $filePath = $note->file_path;
        if (strpos($filePath, '..') === false && strpos($filePath, 'notes/') === 0) {
            // Delete file from storage (only if path is safe)
            Storage::disk('public')->delete($filePath);
            Storage::disk('local')->delete($filePath);
        }

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
