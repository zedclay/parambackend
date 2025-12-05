<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->year_id || !$user->group_id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_INFO',
                    'message' => 'Student must be assigned to a year and group'
                ]
            ], 400);
        }

        $semesterId = $request->get('semester_id');
        
        // If no semester specified, get current semester
        if (!$semesterId) {
            $currentSemester = Semester::where('year_id', $user->year_id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
            
            if (!$currentSemester) {
                // Get the first available semester
                $currentSemester = Semester::where('year_id', $user->year_id)
                    ->orderBy('semester_number')
                    ->first();
            }
            
            $semesterId = $currentSemester?->id;
        }

        if (!$semesterId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'semester' => null,
                ]
            ]);
        }

        $semester = Semester::with(['year.speciality'])->findOrFail($semesterId);
        $planning = Planning::where('semester_id', $semesterId)
            ->where('is_published', true)
            ->first();

        if (!$planning) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'semester' => $semester,
                    'planning' => null,
                ]
            ]);
        }

        // Get items for student's group or items without specific group
        $items = $planning->items()
            ->where(function($query) use ($user) {
                $query->where('group_id', $user->group_id)
                      ->orWhereNull('group_id');
            })
            ->with(['module', 'group'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'semester' => $semester,
                'planning' => $planning,
            ]
        ]);
    }
}

