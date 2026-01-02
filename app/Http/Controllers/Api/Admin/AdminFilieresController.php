<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
            'name.en' => 'nullable|string',
            'description' => 'nullable|array',
            'description.fr' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
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

        $imageUrl = $request->input('image_url');

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Ensure the filieres directory exists
            $directory = 'filieres';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $storedFilename = Str::slug($request->name['fr']) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs($directory, $storedFilename, 'public');
            // Construct URL: /storage/filieres/...
            // Note: storeAs returns the full path including directory
            $imageUrl = '/storage/' . $imagePath;

            // Log for debugging
            \Log::info('Filiere image created', [
                'image_path' => $imagePath,
                'image_url' => $imageUrl,
                'file_exists' => Storage::disk('public')->exists($imagePath)
            ]);
        }

        $filiere = Filiere::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name['fr']),
            'description' => $request->description,
            'image_url' => $imageUrl,
            'order' => $request->input('order', 0),
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $filiere, 'message' => 'Filière created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $filiere = Filiere::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|array',
            'name.fr' => 'required_with:name|string',
            'name.ar' => 'nullable|string',
            'name.en' => 'nullable|string',
            'description' => 'nullable|array',
            'description.fr' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
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
            \Log::info('Filiere update validation failed', [
                'request_data' => $request->all(),
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        // Build update data - handle both JSON and FormData requests
        $updateData = [];

        // Handle name (multilingual array)
        if ($request->has('name')) {
            $nameData = $request->input('name');
            // If name comes as array (FormData or JSON), use it directly
            if (is_array($nameData)) {
                $updateData['name'] = $nameData;
                // Extract fr value for slug
                $frName = $nameData['fr'] ?? $filiere->name['fr'] ?? '';
                if ($frName) {
                    $updateData['slug'] = Str::slug($frName);
                }
            }
        }

        // Handle description (multilingual array)
        if ($request->has('description')) {
            $descData = $request->input('description');
            if (is_array($descData)) {
                $updateData['description'] = $descData;
            }
        }

        // Handle order
        if ($request->has('order')) {
            $updateData['order'] = $request->input('order', 0);
        }

        // Handle is_active
        if ($request->has('is_active')) {
            $updateData['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        // Handle image upload - MUST check hasFile first
        if ($request->hasFile('image')) {
            \Log::info('Filiere update: Image file detected', [
                'filiere_id' => $filiere->id,
                'has_file' => $request->hasFile('image'),
                'file_valid' => $request->file('image')->isValid()
            ]);

            // Delete old image if exists
            if ($filiere->image_url) {
                $oldPath = str_replace('/storage/', '', $filiere->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    \Log::info('Filiere update: Old image deleted', ['old_path' => $oldPath]);
                }
            }

            // Upload new image
            $image = $request->file('image');

            // Ensure the filieres directory exists
            $directory = 'filieres';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $slugToUse = isset($updateData['slug']) ? $updateData['slug'] : ($request->has('name') && is_array($request->input('name')) ? Str::slug($request->input('name')['fr']) : $filiere->slug);
            $storedFilename = Str::slug($slugToUse) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs($directory, $storedFilename, 'public');
            // Construct URL: /storage/filieres/...
            // Note: storeAs returns the full path including directory, so we need to construct correctly
            $updateData['image_url'] = '/storage/' . $imagePath;

            // Log for debugging
            \Log::info('Filiere image uploaded during update', [
                'filiere_id' => $filiere->id,
                'image_path' => $imagePath,
                'image_url' => $updateData['image_url'],
                'file_exists' => Storage::disk('public')->exists($imagePath),
                'update_data_keys' => array_keys($updateData)
            ]);
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

        // Log what will be updated
        \Log::info('Filiere update: Final update data', [
            'filiere_id' => $filiere->id,
            'update_data' => $updateData,
            'has_image_url' => isset($updateData['image_url'])
        ]);

        $filiere->update($updateData);

        // Refresh the model to get the latest data including image_url
        $filiere->refresh();

        // Log the final state
        \Log::info('Filiere update: After refresh', [
            'filiere_id' => $filiere->id,
            'image_url_in_db' => $filiere->image_url
        ]);

        return response()->json(['success' => true, 'data' => $filiere, 'message' => 'Filière updated successfully.']);
    }

    public function updateImage(Request $request, $id)
    {
        // Force logging to ensure we can track the request
        error_log("Filiere updateImage: Method entry - ID: $id");

        try {
            error_log("Filiere updateImage: Inside try block");
            \Log::info('Filiere updateImage: Method called', [
                'filiere_id' => $id,
                'has_file' => $request->hasFile('image'),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'all_files' => array_keys($request->allFiles())
            ]);
            error_log("Filiere updateImage: Log written");

            $filiere = Filiere::findOrFail($id);

            // Check if image file is present
            if (!$request->hasFile('image')) {
                \Log::error('Filiere updateImage: No image file in request', [
                    'filiere_id' => $id,
                    'all_files' => $request->allFiles(),
                    'request_keys' => array_keys($request->all())
                ]);
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'No image file provided'
                    ]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'image' => [
                    'required',
                    'image',
                    'mimes:jpeg,jpg,png',
                    'max:10240', // 10MB max
                ],
            ]);

            if ($validator->fails()) {
                \Log::error('Filiere image update validation failed', [
                    'filiere_id' => $id,
                    'errors' => $validator->errors()->toArray(),
                    'request_all' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validation failed',
                        'details' => $validator->errors()
                    ]
                ], 422);
            }

        $imageFile = $request->file('image');
        \Log::info('Filiere updateImage: Starting image upload', [
            'filiere_id' => $filiere->id,
            'has_file' => $request->hasFile('image'),
            'file_valid' => $imageFile ? $imageFile->isValid() : false,
            'file_size' => $imageFile ? $imageFile->getSize() : null,
            'file_mime' => $imageFile ? $imageFile->getMimeType() : null
        ]);

        // Delete old image if exists
        if ($filiere->image_url) {
            $oldPath = str_replace('/storage/', '', $filiere->image_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
                \Log::info('Filiere updateImage: Old image deleted', ['old_path' => $oldPath]);
            }
        }

        // Upload new image
        $image = $imageFile;

        // Ensure the filieres directory exists
        $directory = 'filieres';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $slugToUse = $filiere->slug;
        $storedFilename = Str::slug($slugToUse) . '-' . time() . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs($directory, $storedFilename, 'public');
        $imageUrl = '/storage/' . $imagePath;

        // Update only image_url
        $filiere->image_url = $imageUrl;
        $filiere->save();

        // Refresh the model
        $filiere->refresh();

        \Log::info('Filiere updateImage: Image uploaded successfully', [
            'filiere_id' => $filiere->id,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'file_exists' => Storage::disk('public')->exists($imagePath)
        ]);

            return response()->json([
                'success' => true,
                'data' => $filiere,
                'message' => 'Image mise à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            // Log to both Laravel log and error_log for redundancy
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();

            \Log::error('Filiere updateImage: Exception occurred', [
                'filiere_id' => $id ?? 'unknown',
                'error' => $errorMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $errorTrace
            ]);

            error_log("Filiere updateImage Exception: " . $errorMessage . " in " . $e->getFile() . ":" . $e->getLine());

            // Always include error message in response for debugging
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => $errorMessage ?: 'An error occurred while updating the image. Please check the server logs.',
                    'error_details' => $errorMessage,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        } catch (\Throwable $e) {
            // Catch any other throwable (PHP 7+)
            $errorMessage = $e->getMessage();
            \Log::error('Filiere updateImage: Throwable occurred', [
                'filiere_id' => $id ?? 'unknown',
                'error' => $errorMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            error_log("Filiere updateImage Throwable: " . $errorMessage);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'An unexpected error occurred. Please check the server logs.'
                ]
            ], 500);
        }
    }

    public function destroy($id)
    {
        $filiere = Filiere::findOrFail($id);
        $filiere->delete();
        return response()->json(['success' => true, 'message' => 'Filière deleted successfully.']);
    }
}
