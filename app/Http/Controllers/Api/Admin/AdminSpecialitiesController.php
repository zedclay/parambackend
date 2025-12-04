<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminSpecialitiesController extends Controller
{
    public function index()
    {
        $specialities = Speciality::with('filiere')->orderBy('order')->get();
        return response()->json(['success' => true, 'data' => $specialities]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filiere_id' => 'required|exists:filieres,id',
            'name' => 'required|array',
            'name.fr' => 'required|string',
            'name.ar' => 'nullable|string',
            'description' => 'nullable|array',
            'duration' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $speciality = Speciality::create([
            'filiere_id' => $request->filiere_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name['fr']),
            'description' => $request->description,
            'duration' => $request->duration,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $speciality, 'message' => 'Spécialité created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $speciality = Speciality::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'filiere_id' => 'sometimes|exists:filieres,id',
            'name' => 'sometimes|array',
            'description' => 'nullable|array',
            'duration' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $updateData = $request->only(['filiere_id', 'name', 'description', 'duration', 'order', 'is_active']);
        if ($request->has('name')) {
            $updateData['slug'] = Str::slug($request->name['fr'] ?? $speciality->name['fr']);
        }
        $speciality->update($updateData);

        return response()->json(['success' => true, 'data' => $speciality->fresh(), 'message' => 'Spécialité updated successfully.']);
    }

    public function destroy($id)
    {
        $speciality = Speciality::findOrFail($id);
        $speciality->delete();
        return response()->json(['success' => true, 'message' => 'Spécialité deleted successfully.']);
    }
}
