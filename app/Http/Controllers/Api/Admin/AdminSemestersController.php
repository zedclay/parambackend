<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminSemestersController extends Controller
{
    public function index(Request $request)
    {
        $query = Semester::with(['year.speciality']);

        if ($request->has('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->has('speciality_id')) {
            $query->whereHas('year', function($q) use ($request) {
                $q->where('speciality_id', $request->speciality_id);
            });
        }

        $semesters = $query->orderBy('academic_year', 'desc')
            ->orderBy('year_id')
            ->orderBy('semester_number')
            ->get();
        
        return response()->json(['success' => true, 'data' => $semesters]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_id' => 'required|exists:years,id',
            'semester_number' => 'required|integer|in:1,2',
            'name' => 'required|array',
            'name.fr' => 'required|string',
            'name.ar' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'academic_year' => 'required|string',
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

        // Check if semester already exists for this year
        $exists = Semester::where('year_id', $request->year_id)
            ->where('semester_number', $request->semester_number)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_SEMESTER',
                    'message' => 'This semester already exists for this year'
                ]
            ], 409);
        }

        $semester = Semester::create([
            'year_id' => $request->year_id,
            'semester_number' => $request->semester_number,
            'name' => $request->name,
            'start_date' => Carbon::parse($request->start_date),
            'end_date' => Carbon::parse($request->end_date),
            'academic_year' => $request->academic_year,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $semester->load('year.speciality'),
            'message' => 'Semester created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $semester = Semester::with(['year.speciality', 'planning.items'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $semester]);
    }

    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|array',
            'name.fr' => 'required_with:name|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'academic_year' => 'sometimes|string',
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

        $updateData = $request->only(['name', 'academic_year', 'is_active']);
        
        if ($request->has('start_date')) {
            $updateData['start_date'] = Carbon::parse($request->start_date);
        }
        
        if ($request->has('end_date')) {
            $updateData['end_date'] = Carbon::parse($request->end_date);
        }

        $semester->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $semester->fresh()->load('year.speciality'),
            'message' => 'Semester updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $semester = Semester::findOrFail($id);
        
        // Check if semester has planning
        if ($semester->planning) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'HAS_PLANNING',
                    'message' => 'Cannot delete semester with existing planning. Delete planning first.'
                ]
            ], 409);
        }

        $semester->delete();
        return response()->json([
            'success' => true,
            'message' => 'Semester deleted successfully.'
        ]);
    }
}

