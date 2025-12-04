<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::where('is_published', true)
            ->where('published_at', '<=', now())
            ->where(function ($query) {
                $query->where('target_audience', 'all')
                    ->orWhere('target_audience', 'students');
            })
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        $enrolledModules = $user->enrolledModules()->with('speciality.filiere')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'announcements' => $announcements,
                'enrolled_modules' => $enrolledModules,
            ],
        ]);
    }

    public function modules(Request $request)
    {
        $user = $request->user();
        $modules = $user->enrolledModules()->with(['speciality.filiere'])->get();

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }
}
