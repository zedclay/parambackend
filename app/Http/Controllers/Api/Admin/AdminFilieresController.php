<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminFilieresController extends Controller
{
    public function index()
    {
        $filieres = Filiere::orderBy('order')->get();
        return response()->json(['success' => true, 'data' => $filieres]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|array',
            'name.fr' => 'required|string',
            'name.ar' => 'nullable|string',
            'description' => 'nullable|array',
            'image_url' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $filiere = Filiere::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name['fr']),
            'description' => $request->description,
            'image_url' => $request->image_url,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $filiere, 'message' => 'Filière created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $filiere = Filiere::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|array',
            'description' => 'nullable|array',
            'image_url' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $updateData = $request->only(['name', 'description', 'image_url', 'order', 'is_active']);
        if ($request->has('name')) {
            $updateData['slug'] = Str::slug($request->name['fr'] ?? $filiere->name['fr']);
        }
        $filiere->update($updateData);

        return response()->json(['success' => true, 'data' => $filiere->fresh(), 'message' => 'Filière updated successfully.']);
    }

    public function destroy($id)
    {
        $filiere = Filiere::findOrFail($id);
        $filiere->delete();
        return response()->json(['success' => true, 'message' => 'Filière deleted successfully.']);
    }
}
