<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function index(Request $request)
    {
        $query = Module::where('is_active', true)
            ->with(['speciality.filiere']);

        if ($request->has('specialite_id')) {
            $query->where('specialite_id', $request->specialite_id);
        }

        $modules = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    public function show($id)
    {
        $module = Module::with(['speciality.filiere'])->findOrFail($id);

        if (!$module->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Module not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $module,
        ]);
    }
}
