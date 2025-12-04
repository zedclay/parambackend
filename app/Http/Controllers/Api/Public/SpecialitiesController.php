<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;

class SpecialitiesController extends Controller
{
    public function index(Request $request)
    {
        $query = Speciality::where('is_active', true)
            ->with(['filiere', 'establishments']);

        if ($request->has('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }

        $specialities = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $specialities,
        ]);
    }

    public function show($id)
    {
        $speciality = Speciality::with([
            'filiere',
            'establishments',
            'modules' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
        ])->findOrFail($id);

        if (!$speciality->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Spécialité not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $speciality,
        ]);
    }
}
