<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;

class DownloadsController extends Controller
{
    public function index()
    {
        $downloads = Download::where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $downloads,
        ]);
    }

    public function show($id)
    {
        $download = Download::where('id', $id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $download,
        ]);
    }
}
