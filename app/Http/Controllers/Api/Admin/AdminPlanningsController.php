<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\Semester;
use Illuminate\Http\Request;
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
                $imagePath = $file->storeAs('plannings', $storedFilename, 'public');
                
                if ($imagePath) {
                    $updateData['image_path'] = $imagePath;
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'UPLOAD_FAILED',
                            'message' => 'Failed to store image'
                        ]
                    ], 500);
                }
            } catch (\Exception $e) {
                \Log::error('Image upload error: ' . $e->getMessage());
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
        }

        $planning->update($updateData);
        
        // Refresh to get updated image_path
        $planning->refresh();

        return response()->json([
            'success' => true,
            'data' => $planning->load('semester.year.speciality'),
            'message' => 'Planning updated successfully.'
        ]);
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

