<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminHeroSlidesController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('order')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $slides]);
    }

    public function store(Request $request)
    {
        // Security: Enhanced validation with file upload support
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'title.fr' => 'required|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'subtitle' => 'sometimes|array',
            'subtitle.fr' => 'nullable|string|max:500',
            'subtitle.ar' => 'nullable|string|max:500',
            'subtitle.en' => 'nullable|string|max:500',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'gradient' => 'nullable|string|max:100',
            'image' => [
                'required',
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
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        // Handle image upload
        $image = $request->file('image');
        $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
        $imageFilename = substr($imageFilename, 0, 100);
        $storedImageFilename = 'hero_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('hero_slides', $storedImageFilename, 'public');
        $imageFilename = $imageFilename . '.' . $image->getClientOriginalExtension();

        // Security: Sanitize text inputs
        $title = $request->title;
        if (isset($title['fr'])) {
            $title['fr'] = strip_tags(trim($title['fr']));
        }
        if (isset($title['ar'])) {
            $title['ar'] = strip_tags(trim($title['ar']));
        }
        if (isset($title['en'])) {
            $title['en'] = strip_tags(trim($title['en']));
        }

        $subtitle = $request->subtitle ?? [];
        if (isset($subtitle['fr'])) {
            $subtitle['fr'] = strip_tags(trim($subtitle['fr']));
        }
        if (isset($subtitle['ar'])) {
            $subtitle['ar'] = strip_tags(trim($subtitle['ar']));
        }
        if (isset($subtitle['en'])) {
            $subtitle['en'] = strip_tags(trim($subtitle['en']));
        }

        $slide = HeroSlide::create([
            'title' => $title,
            'subtitle' => $subtitle,
            'image_path' => $imagePath,
            'image_filename' => $imageFilename,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
            'gradient' => $request->gradient,
        ]);

        return response()->json(['success' => true, 'data' => $slide, 'message' => 'Hero slide created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $slide = HeroSlide::findOrFail($id);

        // Security: Enhanced validation
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|array',
            'title.fr' => 'sometimes|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'subtitle' => 'sometimes|array',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'gradient' => 'nullable|string|max:100',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!in_array($mimeType, $allowedMimes)) {
                            $fail('Invalid image file type.');
                        }
                    }
                }
            ],
            'remove_image' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $updateData = [];

        // Handle title update
        if ($request->has('title')) {
            $title = $request->title;
            if (isset($title['fr'])) {
                $title['fr'] = strip_tags(trim($title['fr']));
            }
            if (isset($title['ar'])) {
                $title['ar'] = strip_tags(trim($title['ar']));
            }
            if (isset($title['en'])) {
                $title['en'] = strip_tags(trim($title['en']));
            }
            $updateData['title'] = $title;
        }

        // Handle subtitle update
        if ($request->has('subtitle')) {
            $subtitle = $request->subtitle;
            if (isset($subtitle['fr'])) {
                $subtitle['fr'] = strip_tags(trim($subtitle['fr']));
            }
            if (isset($subtitle['ar'])) {
                $subtitle['ar'] = strip_tags(trim($subtitle['ar']));
            }
            if (isset($subtitle['en'])) {
                $subtitle['en'] = strip_tags(trim($subtitle['en']));
            }
            $updateData['subtitle'] = $subtitle;
        }

        if ($request->has('order')) {
            $updateData['order'] = $request->order;
        }

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->is_active;
        }

        if ($request->has('gradient')) {
            $updateData['gradient'] = $request->gradient;
        }

        // Handle image upload/removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $updateData['image_path'] = null;
            $updateData['image_filename'] = null;
        } elseif ($request->hasFile('image')) {
            // Delete old image
            if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }

            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'hero_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $updateData['image_path'] = $image->storeAs('hero_slides', $storedImageFilename, 'public');
            $updateData['image_filename'] = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        $slide->update($updateData);

        return response()->json(['success' => true, 'data' => $slide->fresh(), 'message' => 'Hero slide updated successfully.']);
    }

    public function destroy($id)
    {
        $slide = HeroSlide::findOrFail($id);

        // Security: Delete associated file
        if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
            Storage::disk('public')->delete($slide->image_path);
        }

        $slide->delete();
        return response()->json(['success' => true, 'message' => 'Hero slide deleted successfully.']);
    }
}
