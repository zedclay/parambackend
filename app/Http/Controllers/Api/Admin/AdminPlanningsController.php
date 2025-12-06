<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminPlanningsController extends Controller
{
    public function index(Request $request)
    {
        $query = Planning::with(['semester.year.speciality']);

        if ($request->has('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        $plannings = $query->orderBy('academic_year', 'desc')->get();
        return response()->json(['success' => true, 'data' => $plannings]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'semester_id' => 'required|exists:semesters,id|unique:plannings,semester_id',
            'academic_year' => 'required|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:10240', // 10MB max, PNG/JPG
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $storedFilename = 'planning_' . time() . '_' . $file->hashName();
            $imagePath = $file->storeAs('plannings', $storedFilename, 'public');
        }

        $planning = Planning::create([
            'semester_id' => $request->semester_id,
            'academic_year' => $request->academic_year,
            'image_path' => $imagePath,
            'is_published' => $request->is_published ?? false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $planning->load('semester.year.speciality'),
            'message' => 'Planning created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $planning = Planning::with(['semester.year.speciality', 'items.module', 'items.group'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $planning]);
    }

    public function update(Request $request, $id)
    {
        Log::info('Planning update method called', [
            'planning_id' => $id,
            'has_file' => $request->hasFile('image'),
            'request_method' => $request->method(),
        ]);

        $planning = Planning::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'academic_year' => 'sometimes|string',
            'is_published' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $updateData = $request->only(['academic_year', 'is_published']);

        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                // Delete old image if exists
                if ($planning->image_path && Storage::disk('public')->exists($planning->image_path)) {
                    Storage::disk('public')->delete($planning->image_path);
                }

                // Store new image
                $file = $request->file('image');
                $storedFilename = 'planning_' . time() . '_' . $file->hashName();

                // Ensure plannings directory exists
                Storage::disk('public')->makeDirectory('plannings');

                $imagePath = $file->storeAs('plannings', $storedFilename, 'public');

                Log::info('Planning image upload:', [
                    'planning_id' => $planning->id,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'image_path' => $imagePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'exists' => Storage::disk('public')->exists($imagePath)
                ]);

                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    // Set directly on model to ensure it's saved
                    $planning->image_path = $imagePath;
                    $updateData['image_path'] = $imagePath;
                    Log::info('✅ Image path set for update', [
                        'image_path' => $imagePath,
                        'file_exists' => Storage::disk('public')->exists($imagePath),
                        'file_size' => Storage::disk('public')->size($imagePath)
                    ]);
                } else {
                    Log::error('Image upload failed - file not stored', [
                        'image_path' => $imagePath,
                        'exists' => $imagePath ? Storage::disk('public')->exists($imagePath) : false
                    ]);
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'UPLOAD_FAILED',
                            'message' => 'Failed to store image. Please check server logs.'
                        ]
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Image upload error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UPLOAD_ERROR',
                        'message' => 'Error uploading image: ' . $e->getMessage()
                    ]
                ], 500);
            }
        }

        // Handle image deletion
        if ($request->has('delete_image') && $request->delete_image === true) {
            if ($planning->image_path && Storage::disk('public')->exists($planning->image_path)) {
                Storage::disk('public')->delete($planning->image_path);
            }
            $updateData['image_path'] = null;
            // Also set directly on model to ensure it's saved
            $planning->image_path = null;
        }

        // Update the planning
        Log::info('Updating planning', [
            'planning_id' => $planning->id,
            'update_data' => $updateData,
            'has_image_path' => isset($updateData['image_path']),
            'image_path_before_save' => $planning->image_path,
        ]);

        // Update other fields if present (except image_path which is already set on model)
        if (!empty($updateData)) {
            foreach ($updateData as $key => $value) {
                if ($key !== 'image_path') {
                    $planning->$key = $value;
                }
            }
        }

        // Double-check image_path is set before save
        if (isset($updateData['image_path']) && $planning->image_path !== $updateData['image_path']) {
            $planning->image_path = $updateData['image_path'];
            Log::warning('image_path was not set correctly, fixing it', [
                'expected' => $updateData['image_path'],
                'actual' => $planning->image_path,
            ]);
        }

        // Save explicitly to ensure image_path is persisted
        $saved = $planning->save();
        
        // If image_path is in updateData, force update directly in database as backup
        if (isset($updateData['image_path']) && !empty($updateData['image_path'])) {
            $directUpdate = DB::table('plannings')
                ->where('id', $planning->id)
                ->update(['image_path' => $updateData['image_path']]);
            
            Log::info('Direct DB update for image_path', [
                'planning_id' => $planning->id,
                'direct_update_result' => $directUpdate,
                'image_path' => $updateData['image_path'],
            ]);
        }
        
        // Verify the save worked by checking database directly
        $planning->refresh();
        $dbImagePath = DB::table('plannings')->where('id', $planning->id)->value('image_path');
        
        Log::info('Planning saved', [
            'planning_id' => $planning->id,
            'saved' => $saved,
            'image_path_after_save' => $planning->image_path,
            'image_path_in_db' => $dbImagePath,
            'match' => $planning->image_path === $dbImagePath,
        ]);
        
        // If image_path is still null after save, something is wrong
        if (isset($updateData['image_path']) && empty($planning->image_path) && empty($dbImagePath)) {
            Log::error('CRITICAL: image_path is empty after save and direct DB update!', [
                'planning_id' => $planning->id,
                'expected_image_path' => $updateData['image_path'],
                'actual_image_path' => $planning->image_path,
                'db_image_path' => $dbImagePath,
            ]);
        }

        // Reload with relationships and ensure fresh data from database
        $planning = $planning->load('semester.year.speciality');

        // Build response data explicitly to ensure image_path is included
        // Use database value as source of truth (most reliable)
        $responseImagePath = $dbImagePath ?? $planning->image_path ?? null;
        
        $responseData = [
            'id' => $planning->id,
            'semester_id' => $planning->semester_id,
            'academic_year' => $planning->academic_year,
            'image_path' => $responseImagePath, // Use DB value as source of truth
            'is_published' => $planning->is_published,
            'created_at' => $planning->created_at,
            'updated_at' => $planning->updated_at,
            'semester' => $planning->semester ? [
                'id' => $planning->semester->id,
                'year_id' => $planning->semester->year_id,
                'semester_number' => $planning->semester->semester_number,
                'name' => $planning->semester->name,
                'academic_year' => $planning->semester->academic_year,
                'year' => $planning->semester->year ? [
                    'id' => $planning->semester->year->id,
                    'speciality_id' => $planning->semester->year->speciality_id,
                    'year_number' => $planning->semester->year->year_number,
                    'name' => $planning->semester->year->name,
                    'speciality' => $planning->semester->year->speciality ? [
                        'id' => $planning->semester->year->speciality->id,
                        'filiere_id' => $planning->semester->year->speciality->filiere_id,
                        'name' => $planning->semester->year->speciality->name,
                    ] : null,
                ] : null,
            ] : null,
        ];

        // Final verification: ensure image_path is ALWAYS in response
        // Priority: 1. DB value (most reliable), 2. updateData (what we just set), 3. model value, 4. null
        $finalImagePath = null;
        
        if (!empty($dbImagePath)) {
            $finalImagePath = $dbImagePath;
            Log::info('Using DB value for image_path', ['value' => $finalImagePath]);
        } elseif (isset($updateData['image_path']) && !empty($updateData['image_path'])) {
            $finalImagePath = $updateData['image_path'];
            Log::info('Using updateData value for image_path', ['value' => $finalImagePath]);
        } elseif (!empty($planning->image_path)) {
            $finalImagePath = $planning->image_path;
            Log::info('Using model value for image_path', ['value' => $finalImagePath]);
        } else {
            Log::warning('image_path is null/empty in all sources', [
                'db_value' => $dbImagePath,
                'update_data_value' => $updateData['image_path'] ?? 'not set',
                'model_value' => $planning->image_path,
            ]);
        }
        
        // ALWAYS set image_path in response, even if null (so frontend knows it was processed)
        $responseData['image_path'] = $finalImagePath;
        
        // If we uploaded an image but finalImagePath is still null, that's a critical error
        if (isset($updateData['image_path']) && !empty($updateData['image_path']) && empty($finalImagePath)) {
            Log::error('CRITICAL: Image was uploaded but image_path is null in response!', [
                'planning_id' => $planning->id,
                'uploaded_path' => $updateData['image_path'],
                'db_value' => $dbImagePath,
                'model_value' => $planning->image_path,
            ]);
        }

        // Final check: Log the exact response that will be sent
        Log::info('Planning update response (FINAL):', [
            'planning_id' => $planning->id,
            'image_path_in_response' => $responseData['image_path'],
            'image_path_from_model' => $planning->image_path,
            'image_path_from_db' => $dbImagePath ?? 'not checked',
            'response_keys' => array_keys($responseData),
            'response_data_image_path_exists' => isset($responseData['image_path']),
            'response_data_image_path_not_null' => !is_null($responseData['image_path']),
            'response_data_image_path_not_empty' => !empty($responseData['image_path']),
        ]);

        // Build final response
        $finalResponse = [
            'success' => true,
            'data' => $responseData,
            'message' => 'Planning updated successfully.'
        ];

        // Log the exact JSON that will be sent (for debugging)
        Log::info('Exact JSON response (first 500 chars):', [
            'json_preview' => substr(json_encode($finalResponse), 0, 500),
            'has_image_path' => isset($finalResponse['data']['image_path']),
        ]);

        return response()->json($finalResponse);
    }

    public function destroy($id)
    {
        $planning = Planning::findOrFail($id);

        // Delete image if exists
        if ($planning->image_path && Storage::disk('public')->exists($planning->image_path)) {
            Storage::disk('public')->delete($planning->image_path);
        }

        $planning->delete();

        return response()->json([
            'success' => true,
            'message' => 'Planning deleted successfully.'
        ]);
    }

    public function publish($id)
    {
        $planning = Planning::findOrFail($id);
        $planning->update(['is_published' => true]);

        return response()->json([
            'success' => true,
            'data' => $planning->fresh(),
            'message' => 'Planning published successfully.'
        ]);
    }

    public function unpublish($id)
    {
        $planning = Planning::findOrFail($id);
        $planning->update(['is_published' => false]);

        return response()->json([
            'success' => true,
            'data' => $planning->fresh(),
            'message' => 'Planning unpublished successfully.'
        ]);
    }
}

