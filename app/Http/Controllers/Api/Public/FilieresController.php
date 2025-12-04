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
