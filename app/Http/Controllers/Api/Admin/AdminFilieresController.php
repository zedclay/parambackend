<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
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
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        // Security: Validate actual file content
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!in_array($mimeType, $allowedMimes)) {
                            $fail('Invalid image file type. Only JPEG and PNG images are allowed.');
                        }

                        if ($value->getSize() > 10485760) {
                            $fail('Image size must not exceed 10MB.');
                        }
                    }
                }
            ],
            'image_url' => 'nullable|string', // For backward compatibility
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $imageUrl = $request->image_url;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Ensure the filieres directory exists
            $directory = 'filieres';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            $filename = $directory . '/' . Str::slug($request->name['fr']) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $filename);
            // Construct URL: /storage/filieres/...
            $imageUrl = '/storage/' . $filename;
        }

        $filiere = Filiere::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name['fr']),
            'description' => $request->description,
            'image_url' => $imageUrl,
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
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        // Security: Validate actual file content
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!in_array($mimeType, $allowedMimes)) {
                            $fail('Invalid image file type. Only JPEG and PNG images are allowed.');
                        }

                        if ($value->getSize() > 10485760) {
                            $fail('Image size must not exceed 10MB.');
                        }
                    }
                }
            ],
            'image_url' => 'nullable|string', // For backward compatibility
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'remove_image' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $updateData = $request->only(['name', 'description', 'order', 'is_active']);
        if ($request->has('name')) {
            $updateData['slug'] = Str::slug($request->name['fr'] ?? $filiere->name['fr']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($filiere->image_url) {
                $oldPath = str_replace('/storage/', '', $filiere->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Upload new image
            $image = $request->file('image');
            
            // Ensure the filieres directory exists
            $directory = 'filieres';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            $filename = $directory . '/' . Str::slug($updateData['slug'] ?? $filiere->slug) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $filename);
            // Construct URL: /storage/filieres/...
            $updateData['image_url'] = '/storage/' . $filename;
        } elseif ($request->has('remove_image') && $request->remove_image) {
            // Remove image if requested
            if ($filiere->image_url) {
                $oldPath = str_replace('/storage/', '', $filiere->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $updateData['image_url'] = null;
        } elseif ($request->has('image_url')) {
            // Allow direct image_url update for backward compatibility
            $updateData['image_url'] = $request->image_url;
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
