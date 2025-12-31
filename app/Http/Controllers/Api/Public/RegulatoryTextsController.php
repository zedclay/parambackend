<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\RegulatoryText;
use Illuminate\Http\Request;

class RegulatoryTextsController extends Controller
{
    public function index()
    {
        $regulatoryTexts = RegulatoryText::where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $regulatoryTexts,
        ]);
    }

    public function show($id)
    {
        $regulatoryText = RegulatoryText::where('id', $id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->with(['author', 'images'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $regulatoryText,
        ]);
    }
}
