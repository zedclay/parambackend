<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    public function show($id)
    {
        $announcement = Announcement::where('id', $id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $announcement,
        ]);
    }
}
