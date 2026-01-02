<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;

class FilieresController extends Controller
{
    public function index()
    {
        $filieres = Filiere::where('is_active', true)
            ->orderBy('order')
            ->get();

        // Log image_url for each filiere for debugging
        foreach ($filieres as $filiere) {
            \Log::info('Public API - Filiere image_url', [
                'id' => $filiere->id,
                'name' => $filiere->name['fr'] ?? 'N/A',
                'image_url' => $filiere->image_url
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $filieres,
        ]);
    }

    public function show($id)
    {
        $filiere = Filiere::with(['specialities' => function ($query) {
            $query->where('is_active', true)->orderBy('order');
        }])->findOrFail($id);

        if (!$filiere->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Filière not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $filiere,
        ]);
    }
}
