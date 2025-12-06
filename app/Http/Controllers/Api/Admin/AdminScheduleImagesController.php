<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleImage;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminScheduleImagesController extends Controller
{
    /**
     * Get schedule image for a specific semester.
     */
    public function show(Request $request, $semesterId)
    {
        try {
            $scheduleImage = ScheduleImage::where('semester_id', $semesterId)
                ->where('is_active', true)
                ->with(['semester.year.speciality', 'uploader'])
                ->first();

            if (!$scheduleImage) {
                Log::info('No schedule image found for semester', ['semester_id' => $semesterId]);
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No schedule image found for this semester.',
                ]);
            }

            Log::info('Schedule image found', [
                'schedule_image_id' => $scheduleImage->id,
                'semester_id' => $semesterId,
                'image_path' => $scheduleImage->image_path,
                'file_exists' => $scheduleImage->fileExists(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $scheduleImage->id,
                    'semester_id' => $scheduleImage->semester_id,
                    'image_path' => $scheduleImage->image_path,
                    'image_url' => $scheduleImage->image_url,
                    'original_filename' => $scheduleImage->original_filename,
                    'uploaded_by' => $scheduleImage->uploaded_by,
                    'uploader_name' => $scheduleImage->uploader->name ?? null,
                    'is_active' => $scheduleImage->is_active,
                    'file_exists' => $scheduleImage->fileExists(),
                    'file_size' => $scheduleImage->file_size,
                    'created_at' => $scheduleImage->created_at,
                    'updated_at' => $scheduleImage->updated_at,
                    'semester' => $scheduleImage->semester ? [
                        'id' => $scheduleImage->semester->id,
                        'year_id' => $scheduleImage->semester->year_id,
                        'semester_number' => $scheduleImage->semester->semester_number,
                        'name' => $scheduleImage->semester->name,
                        'academic_year' => $scheduleImage->semester->academic_year,
                        'year' => $scheduleImage->semester->year ? [
                            'id' => $scheduleImage->semester->year->id,
                            'speciality_id' => $scheduleImage->semester->year->speciality_id,
                            'year_number' => $scheduleImage->semester->year->year_number,
                            'name' => $scheduleImage->semester->year->name,
                            'speciality' => $scheduleImage->semester->year->speciality ? [
                                'id' => $scheduleImage->semester->year->speciality->id,
                                'filiere_id' => $scheduleImage->semester->year->speciality->filiere_id,
                                'name' => $scheduleImage->semester->year->speciality->name,
                            ] : null,
                        ] : null,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching schedule image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_ERROR',
                    'message' => 'Error fetching schedule image.',
                ],
            ], 500);
        }
    }

    /**
     * Upload or update schedule image for a semester.
     */
    public function store(Request $request, $semesterId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:10240', // Max 10MB
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            // Verify semester exists
            $semester = Semester::findOrFail($semesterId);
            
            // Check if image already exists for this semester
            $existingImage = ScheduleImage::where('semester_id', $semesterId)->first();

            // Handle file upload
            $file = $request->file('image');
            $originalFilename = $file->getClientOriginalName();
            $storedFilename = 'schedule_' . $semesterId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('schedule_images', $storedFilename, 'public');

            if (!$imagePath || !Storage::disk('public')->exists($imagePath)) {
                Log::error('Failed to store schedule image', [
                    'semester_id' => $semesterId,
                    'filename' => $storedFilename,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UPLOAD_FAILED',
                        'message' => 'Failed to store image. Please try again.',
                    ],
                ], 500);
            }

            // Delete old image if exists
            if ($existingImage && $existingImage->image_path) {
                if (Storage::disk('public')->exists($existingImage->image_path)) {
                    Storage::disk('public')->delete($existingImage->image_path);
                }
            }

            // Create or update schedule image
            if ($existingImage) {
                // Update existing
                $existingImage->image_path = $imagePath;
                $existingImage->original_filename = $originalFilename;
                $existingImage->uploaded_by = $request->user()->id;
                $existingImage->is_active = true;
                $existingImage->save();
                $scheduleImage = $existingImage;
            } else {
                // Create new
                $scheduleImage = ScheduleImage::create([
                    'semester_id' => $semesterId,
                    'image_path' => $imagePath,
                    'original_filename' => $originalFilename,
                    'uploaded_by' => $request->user()->id,
                    'is_active' => true,
                ]);
            }

            // Verify it was saved
            $scheduleImage->refresh();
            $dbImagePath = DB::table('schedule_images')->where('id', $scheduleImage->id)->value('image_path');

            Log::info('Schedule image uploaded successfully', [
                'schedule_image_id' => $scheduleImage->id,
                'semester_id' => $semesterId,
                'image_path' => $imagePath,
                'verified_in_db' => $dbImagePath,
                'match' => $dbImagePath === $imagePath,
            ]);

            // Load relationships
            $scheduleImage->load(['semester.year.speciality', 'uploader']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $scheduleImage->id,
                    'semester_id' => $scheduleImage->semester_id,
                    'image_path' => $scheduleImage->image_path,
                    'image_url' => $scheduleImage->image_url,
                    'original_filename' => $scheduleImage->original_filename,
                    'uploaded_by' => $scheduleImage->uploaded_by,
                    'uploader_name' => $scheduleImage->uploader->name ?? null,
                    'is_active' => $scheduleImage->is_active,
                    'file_exists' => $scheduleImage->fileExists(),
                    'file_size' => $scheduleImage->file_size,
                    'created_at' => $scheduleImage->created_at,
                    'updated_at' => $scheduleImage->updated_at,
                    'semester' => $scheduleImage->semester ? [
                        'id' => $scheduleImage->semester->id,
                        'year_id' => $scheduleImage->semester->year_id,
                        'semester_number' => $scheduleImage->semester->semester_number,
                        'name' => $scheduleImage->semester->name,
                        'academic_year' => $scheduleImage->semester->academic_year,
                        'year' => $scheduleImage->semester->year ? [
                            'id' => $scheduleImage->semester->year->id,
                            'speciality_id' => $scheduleImage->semester->year->speciality_id,
                            'year_number' => $scheduleImage->semester->year->year_number,
                            'name' => $scheduleImage->semester->year->name,
                            'speciality' => $scheduleImage->semester->year->speciality ? [
                                'id' => $scheduleImage->semester->year->speciality->id,
                                'filiere_id' => $scheduleImage->semester->year->speciality->filiere_id,
                                'name' => $scheduleImage->semester->year->speciality->name,
                            ] : null,
                        ] : null,
                    ] : null,
                ],
                'message' => 'Schedule image uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading schedule image: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UPLOAD_ERROR',
                    'message' => 'Error uploading image: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Delete schedule image for a semester.
     */
    public function destroy(Request $request, $semesterId)
    {
        try {
            $scheduleImage = ScheduleImage::where('semester_id', $semesterId)->first();

            if (!$scheduleImage) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Schedule image not found.',
                    ],
                ], 404);
            }

            // Delete file from storage
            if ($scheduleImage->image_path && Storage::disk('public')->exists($scheduleImage->image_path)) {
                Storage::disk('public')->delete($scheduleImage->image_path);
            }

            // Delete record
            $scheduleImage->delete();

            Log::info('Schedule image deleted', [
                'semester_id' => $semesterId,
                'schedule_image_id' => $scheduleImage->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule image deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting schedule image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETE_ERROR',
                    'message' => 'Error deleting image.',
                ],
            ], 500);
        }
    }
}

