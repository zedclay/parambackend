<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;

class HeroSlidesController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slides,
        ]);
    }
}
