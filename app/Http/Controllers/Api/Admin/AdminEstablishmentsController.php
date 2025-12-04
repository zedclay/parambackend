<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminEstablishmentsController extends Controller
{
    public function index()
    {
        $establishments = Establishment::with('speciality')->get();
        return response()->json(['success' => true, 'data' => $establishments]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'specialite_id' => 'required|exists:specialities,id',
            'name' => 'required|array',
            'name.fr' => 'required|string',
            'name.ar' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $establishment = Establishment::create($request->all());
        return response()->json(['success' => true, 'data' => $establishment, 'message' => 'Establishment created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $establishment = Establishment::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'specialite_id' => 'sometimes|exists:specialities,id',
            'name' => 'sometimes|array',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $establishment->update($request->only(['specialite_id', 'name', 'address', 'contact_email', 'contact_phone']));
        return response()->json(['success' => true, 'data' => $establishment->fresh(), 'message' => 'Establishment updated successfully.']);
    }

    public function destroy($id)
    {
        $establishment = Establishment::findOrFail($id);
        $establishment->delete();
        return response()->json(['success' => true, 'message' => 'Establishment deleted successfully.']);
    }
}
