<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminModulesController extends Controller
{
    public function index()
    {
        $modules = Module::with(['speciality.filiere'])->orderBy('order')->get();
        return response()->json(['success' => true, 'data' => $modules]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'specialite_id' => 'required|exists:specialities,id',
            'code' => 'required|string|max:50',
            'title' => 'required|array',
            'title.fr' => 'required|string',
            'title.ar' => 'nullable|string',
            'description' => 'nullable|array',
            'credits' => 'nullable|integer',
            'hours' => 'nullable|integer',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $module = Module::create([
            'specialite_id' => $request->specialite_id,
            'code' => $request->code,
            'title' => $request->title,
            'description' => $request->description,
            'credits' => $request->credits,
            'hours' => $request->hours,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $module, 'message' => 'Module created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'specialite_id' => 'sometimes|exists:specialities,id',
            'code' => 'sometimes|string|max:50',
            'title' => 'sometimes|array',
            'description' => 'nullable|array',
            'credits' => 'nullable|integer',
            'hours' => 'nullable|integer',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $module->update($request->only(['specialite_id', 'code', 'title', 'description', 'credits', 'hours', 'order', 'is_active']));
        return response()->json(['success' => true, 'data' => $module->fresh(), 'message' => 'Module updated successfully.']);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
        return response()->json(['success' => true, 'message' => 'Module deleted successfully.']);
    }
}
