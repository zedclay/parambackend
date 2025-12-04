<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentNotesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Note::where(function ($q) use ($user) {
            // General notes (semester/year) - visible to all students
            $q->where(function ($generalQuery) {
                $generalQuery->whereNull('module_id')
                    ->whereNull('specialite_id')
                    ->whereNull('assigned_student_id')
                    ->where('visibility', 'specialite'); // General notes use 'specialite' visibility but with null specialite_id
            })
                // Notes assigned directly to student
                ->orWhere('assigned_student_id', $user->id)
                // Notes assigned to modules student is enrolled in
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('visibility', 'module')
                        ->whereNotNull('module_id')
                        ->whereIn('module_id', $user->enrolledModules()->pluck('modules.id'));
                })
                // Notes assigned to specialities student is enrolled in
                ->orWhere(function ($q3) use ($user) {
                    $q3->where('visibility', 'specialite')
                        ->whereNotNull('specialite_id')
                        ->whereIn('specialite_id', $user->enrolledModules()
                            ->with('speciality')
                            ->get()
                            ->pluck('speciality.specialite_id')
                            ->unique());
                });
        });

        // Filters
        if ($request->has('general') && $request->general === 'true') {
            // Filter only general notes (semester/year notes)
            $query->whereNull('module_id')
                ->whereNull('specialite_id')
                ->whereNull('assigned_student_id');
        }

        if ($request->has('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->has('specialite_id')) {
            $query->where('specialite_id', $request->specialite_id);
        }

        if ($request->has('file_type')) {
            if ($request->file_type === 'pdf') {
                $query->where('mime_type', 'application/pdf');
            } elseif ($request->file_type === 'image') {
                $query->whereIn('mime_type', ['image/jpeg', 'image/jpg', 'image/png']);
            }
        }

        $notes = $query->with(['module', 'speciality'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function show($id, Request $request)
    {
        $user = $request->user();
        $note = Note::findOrFail($id);

        // Check if student has access
        $hasAccess = $this->checkAccess($note, $user);
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'You do not have access to this note.',
                ],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $note->load(['module', 'speciality']),
        ]);
    }

    public function preview($id, Request $request)
    {
        $user = $request->user();
        $note = Note::findOrFail($id);

        // Check access
        $hasAccess = $this->checkAccess($note, $user);
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'You do not have access to this note.',
                ],
            ], 403);
        }

        // Check if file exists in storage
        $publicDisk = Storage::disk('public');
        $localDisk = Storage::disk('local');
        $fileExists = $publicDisk->exists($note->file_path) || $localDisk->exists($note->file_path);

        if (!$fileExists) {
            // File doesn't exist (seeded data without actual files)
            // Return a message indicating file needs to be uploaded
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'Le fichier n\'a pas encore été téléversé. Veuillez contacter l\'administrateur.',
                ],
            ], 404);
        }

        // Always use route to serve files (works with Apache and Laravel server)
        $url = route('api.student.notes.serve', ['id' => $note->id]);

        return response()->json([
            'success' => true,
            'data' => [
                'preview_url' => $url,
                'mime_type' => $note->mime_type,
            ],
        ]);
    }

    public function download($id, Request $request)
    {
        $user = $request->user();
        $note = Note::findOrFail($id);

        // Check access
        $hasAccess = $this->checkAccess($note, $user);
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'You do not have access to this note.',
                ],
            ], 403);
        }

        // Log download
        DownloadLog::create([
            'note_id' => $note->id,
            'student_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Increment download count
        $note->increment('download_count');

        // Check if file exists in storage
        $publicDisk = Storage::disk('public');
        $localDisk = Storage::disk('local');
        $fileExists = $publicDisk->exists($note->file_path) || $localDisk->exists($note->file_path);

        if (!$fileExists) {
            // File doesn't exist (seeded data without actual files)
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'Le fichier n\'a pas encore été téléversé. Veuillez contacter l\'administrateur.',
                ],
            ], 404);
        }

        // Always use route to serve files (works with Apache and Laravel server)
        // Add download parameter for download disposition
        $url = route('api.student.notes.serve', ['id' => $note->id]) . '?download=true';

        return response()->json([
            'success' => true,
            'data' => [
                'download_url' => $url,
                'filename' => $note->filename,
            ],
        ]);
    }

    private function checkAccess(Note $note, $user): bool
    {
        // General notes (semester/year) - visible to all students
        if ($note->module_id === null && $note->specialite_id === null && $note->assigned_student_id === null) {
            return true;
        }

        // Direct assignment
        if ($note->assigned_student_id === $user->id) {
            return true;
        }

        // Module-level access
        if ($note->visibility === 'module' && $note->module_id) {
            return $user->enrolledModules()->where('modules.id', $note->module_id)->exists();
        }

        // Speciality-level access
        if ($note->visibility === 'specialite' && $note->specialite_id) {
            $userSpecialiteIds = $user->enrolledModules()
                ->with('speciality')
                ->get()
                ->pluck('speciality.specialite_id')
                ->unique();
            return $userSpecialiteIds->contains($note->specialite_id);
        }

        return false;
    }

    public function serve($id, Request $request)
    {
        $user = $request->user();
        $note = Note::findOrFail($id);

        // Check access
        $hasAccess = $this->checkAccess($note, $user);
        if (!$hasAccess) {
            abort(403, 'Access denied');
        }

        // Check if file exists and serve it
        $publicDisk = Storage::disk('public');
        $localDisk = Storage::disk('local');

        $file = null;
        $disk = null;

        if ($publicDisk->exists($note->file_path)) {
            $file = $publicDisk->get($note->file_path);
            $disk = $publicDisk;
        } elseif ($localDisk->exists($note->file_path)) {
            $file = $localDisk->get($note->file_path);
            $disk = $localDisk;
        }

        if (!$file) {
            abort(404, 'File not found: ' . $note->file_path);
        }

        // Determine content disposition based on request (preview vs download)
        $disposition = $request->query('download') === 'true' ? 'attachment' : 'inline';

        return response($file, 200, [
            'Content-Type' => $note->mime_type,
            'Content-Disposition' => $disposition . '; filename="' . $note->filename . '"',
            'Content-Length' => strlen($file),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
