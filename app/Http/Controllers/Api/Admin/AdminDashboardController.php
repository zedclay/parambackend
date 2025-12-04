<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\DownloadLog;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('is_active', true)->count();
        $totalNotes = Note::count();
        $totalAnnouncements = Announcement::count();
        $recentUploads = Note::with(['uploader', 'module'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
                    'total_notes' => $totalNotes,
                    'total_announcements' => $totalAnnouncements,
                ],
                'recent_uploads' => $recentUploads,
            ],
        ]);
    }

    public function downloadAnalytics()
    {
        $totalDownloads = DownloadLog::count();
        $recentDownloads = DownloadLog::with(['note', 'student'])
            ->orderBy('downloaded_at', 'desc')
            ->limit(50)
            ->get();

        $topNotes = Note::orderBy('download_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_downloads' => $totalDownloads,
                'recent_downloads' => $recentDownloads,
                'top_notes' => $topNotes,
            ],
        ]);
    }

    public function studentAnalytics()
    {
        $activeStudents = User::where('role', 'student')
            ->where('is_active', true)
            ->count();

        $studentsWithDownloads = DownloadLog::distinct('student_id')->count('student_id');

        return response()->json([
            'success' => true,
            'data' => [
                'active_students' => $activeStudents,
                'students_with_downloads' => $studentsWithDownloads,
            ],
        ]);
    }

    public function auditLogs(Request $request)
    {
        $logs = \App\Models\AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
