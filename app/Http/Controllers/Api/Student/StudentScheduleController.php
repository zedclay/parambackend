<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\Semester;
use App\Models\Year;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentScheduleController extends Controller
{
    /**
     * Get available specialties for schedule selection
     */
    public function getSpecialities(Request $request)
    {
        $specialities = Speciality::where('is_active', true)
            ->with('filiere')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $specialities,
        ]);
    }

    /**
     * Get available years for a specialty
     */
    public function getYears(Request $request, $specialityId)
    {
        $years = Year::where('speciality_id', $specialityId)
            ->where('is_active', true)
            ->orderBy('year_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $years,
        ]);
    }

    /**
     * Get available semesters for a year
     */
    public function getSemesters(Request $request, $yearId)
    {
        $semesters = Semester::where('year_id', $yearId)
            ->orderBy('semester_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $semesters,
        ]);
    }

    /**
     * Get schedule for selected specialty, year, and semester
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get parameters from request
        $specialityId = $request->get('speciality_id');
        $yearId = $request->get('year_id');
        $semesterId = $request->get('semester_id');

        // If no parameters provided, return empty with available options
        if (!$specialityId || !$yearId || !$semesterId) {
            // Get all specialties for dropdown
            $specialities = Speciality::where('is_active', true)
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'semester' => null,
                    'planning' => null,
                    'specialities' => $specialities,
                ]
            ]);
        }

        // Validate that year belongs to specialty
        $year = Year::where('id', $yearId)
            ->where('speciality_id', $specialityId)
            ->firstOrFail();

        // Validate that semester belongs to year
        $semester = Semester::where('id', $semesterId)
            ->where('year_id', $yearId)
            ->with(['year.speciality'])
            ->firstOrFail();

        // Get planning for the semester
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

        // Get all items for the planning (no group filter - show all)
        $items = $planning->items()
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
