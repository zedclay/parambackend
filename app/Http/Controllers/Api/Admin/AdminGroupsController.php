<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminGroupsController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::with(['speciality', 'year']);

        if ($request->has('speciality_id')) {
            $query->where('speciality_id', $request->speciality_id);
        }

        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        $groups = $query->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $groups]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'speciality_id' => 'required|exists:specialities,id',
            'year_id' => 'required|exists:years,id',
            'name' => 'required|string|max:50',
            'capacity' => 'nullable|integer|min:1',
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

        // Check if group already exists for this year and speciality
        $exists = Group::where('speciality_id', $request->speciality_id)
            ->where('year_id', $request->year_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_GROUP',
                    'message' => 'This group already exists for this year and speciality'
                ]
            ], 409);
        }

        // Generate unique code
        $code = "SPEC{$request->speciality_id}-Y{$request->year_id}-{$request->name}";

        $group = Group::create([
            'speciality_id' => $request->speciality_id,
            'year_id' => $request->year_id,
            'name' => $request->name,
            'code' => $code,
            'capacity' => $request->capacity,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $group->load(['speciality', 'year']),
            'message' => 'Group created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $group = Group::with(['speciality', 'year', 'students'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $group]);
    }

    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
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

        $updateData = $request->only(['name', 'capacity', 'is_active']);
        
        // Update code if name changed
        if ($request->has('name')) {
            $updateData['code'] = "SPEC{$group->speciality_id}-Y{$group->year_id}-{$request->name}";
        }

        $group->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $group->fresh()->load(['speciality', 'year']),
            'message' => 'Group updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        
        // Check if group has students
        if ($group->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'HAS_STUDENTS',
                    'message' => 'Cannot delete group with assigned students'
                ]
            ], 409);
        }

        $group->delete();
        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully.'
        ]);
    }
}

