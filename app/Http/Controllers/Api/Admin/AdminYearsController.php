<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Year;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminYearsController extends Controller
{
    public function index(Request $request)
    {
        $query = Year::with(['speciality']);

        if ($request->has('speciality_id')) {
            $query->where('speciality_id', $request->speciality_id);
        }

        $years = $query->orderBy('order')->get();
        return response()->json(['success' => true, 'data' => $years]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'speciality_id' => 'required|exists:specialities,id',
            'year_number' => 'required|integer|min:1|max:5',
            'name' => 'required|array',
            'name.fr' => 'required|string',
            'name.ar' => 'nullable|string',
            'description' => 'nullable|array',
            'order' => 'nullable|integer',
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

        // Check if year already exists for this speciality
        $exists = Year::where('speciality_id', $request->speciality_id)
            ->where('year_number', $request->year_number)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_YEAR',
                    'message' => 'This year already exists for this speciality'
                ]
            ], 409);
        }

        $year = Year::create([
            'speciality_id' => $request->speciality_id,
            'year_number' => $request->year_number,
            'name' => $request->name,
            'description' => $request->description,
            'order' => $request->order ?? $request->year_number,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $year->load('speciality'),
            'message' => 'Year created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $year = Year::with(['speciality', 'semesters', 'groups'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $year]);
    }

    public function update(Request $request, $id)
    {
        $year = Year::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|array',
            'name.fr' => 'required_with:name|string',
            'description' => 'nullable|array',
            'order' => 'nullable|integer',
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

        $updateData = $request->only(['name', 'description', 'order', 'is_active']);
        $year->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $year->fresh()->load('speciality'),
            'message' => 'Year updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $year = Year::findOrFail($id);
        
        // Check if year has students
        if ($year->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'HAS_STUDENTS',
                    'message' => 'Cannot delete year with assigned students'
                ]
            ], 409);
        }

        $year->delete();
        return response()->json([
            'success' => true,
            'message' => 'Year deleted successfully.'
        ]);
    }
}

