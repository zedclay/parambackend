<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegulatoryText;
use App\Models\RegulatoryTextImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminRegulatoryTextsController extends Controller
{
    public function index()
    {
        $regulatoryTexts = RegulatoryText::with(['author', 'images'])->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $regulatoryTexts]);
    }
{
    public function index()
    {
        $regulatory_texts = RegulatoryText::with(['author', 'images'])->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $regulatory_texts]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'title.fr' => 'required|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'content' => 'nullable|array',
            'content.fr' => 'nullable|string',
            'content.ar' => 'nullable|string',
            'is_published' => 'sometimes|boolean',
            'target_audience' => 'sometimes|in:all,students,specific_specialite',
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240'],
            'file' => ['nullable', 'file', 'max:10240'], // Can be any file type
            'images.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        // Handle principal image
        $imagePath = null;
        $imageFilename = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'regulatory_text_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('regulatory_texts/images', $storedImageFilename, 'public');
            $imageFilename = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        // Handle file upload
        $filePath = null;
        $fileFilename = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileFilename);
            $fileFilename = substr($fileFilename, 0, 100);
            $extension = $file->getClientOriginalExtension();
            $storedFileFilename = 'regulatory_text_' . time() . '_' . hash('sha256', uniqid()) . '.' . $extension;
            $filePath = $file->storeAs('regulatory_texts/files', $storedFileFilename, 'public');
            $fileFilename = $fileFilename . '.' . $extension;
        }

        $title = $request->title;
        if (isset($title['fr'])) {
            $title['fr'] = strip_tags(trim($title['fr']));
        }
        if (isset($title['ar'])) {
            $title['ar'] = strip_tags(trim($title['ar']));
        }

        $regulatory_text = RegulatoryText::create([
            'title' => $title,
            'content' => $request->content ?? [],
            'author_id' => $request->user()->id,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
            'target_audience' => $request->target_audience ?? 'all',
            'image_path' => $imagePath,
            'image_filename' => $imageFilename,
            'file_path' => $filePath,
            'file_filename' => $fileFilename,
        ]);

        // Handle multiple images
        if ($request->hasFile('images')) {
            $order = 0;
            foreach ($request->file('images') as $image) {
                $imgFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imgFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imgFilename);
                $imgFilename = substr($imgFilename, 0, 100);
                $storedImageFilename = 'regulatory_text_' . $regulatory_text->id . '_' . time() . '_' . $order . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
                $imgPath = $image->storeAs('regulatory_texts/images', $storedImageFilename, 'public');

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $image->getRealPath());
                finfo_close($finfo);

                RegulatoryTextImage::create([
                    'regulatory_text_id' => $regulatory_text->id,
                    'image_path' => $imgPath,
                    'image_filename' => $imgFilename . '.' . $image->getClientOriginalExtension(),
                    'mime_type' => $mimeType,
                    'file_size' => $image->getSize(),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $regulatory_text->load(['author', 'images']), 'message' => 'RegulatoryText created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $regulatory_text = RegulatoryText::findOrFail($id);
        $updateData = [];

        if ($request->has('title')) {
            $title = $request->title;
            if (isset($title['fr'])) {
                $title['fr'] = strip_tags(trim($title['fr']));
            }
            if (isset($title['ar'])) {
                $title['ar'] = strip_tags(trim($title['ar']));
            }
            $updateData['title'] = $title;
        }

        if ($request->has('content')) {
            $updateData['content'] = $request->content;
        }

        if ($request->has('target_audience')) {
            $updateData['target_audience'] = $request->target_audience;
        }

        // Handle image
        if ($request->has('remove_image') && $request->remove_image) {
            if ($regulatory_text->image_path && Storage::disk('public')->exists($regulatory_text->image_path)) {
                Storage::disk('public')->delete($regulatory_text->image_path);
            }
            $updateData['image_path'] = null;
            $updateData['image_filename'] = null;
        } elseif ($request->hasFile('image')) {
            if ($regulatory_text->image_path && Storage::disk('public')->exists($regulatory_text->image_path)) {
                Storage::disk('public')->delete($regulatory_text->image_path);
            }
            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'regulatory_text_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $updateData['image_path'] = $image->storeAs('regulatory_texts/images', $storedImageFilename, 'public');
            $updateData['image_filename'] = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        // Handle file
        if ($request->has('remove_file') && $request->remove_file) {
            if ($regulatory_text->file_path && Storage::disk('public')->exists($regulatory_text->file_path)) {
                Storage::disk('public')->delete($regulatory_text->file_path);
            }
            $updateData['file_path'] = null;
            $updateData['file_filename'] = null;
        } elseif ($request->hasFile('file')) {
            if ($regulatory_text->file_path && Storage::disk('public')->exists($regulatory_text->file_path)) {
                Storage::disk('public')->delete($regulatory_text->file_path);
            }
            $file = $request->file('file');
            $fileFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileFilename);
            $fileFilename = substr($fileFilename, 0, 100);
            $extension = $file->getClientOriginalExtension();
            $storedFileFilename = 'regulatory_text_' . time() . '_' . hash('sha256', uniqid()) . '.' . $extension;
            $updateData['file_path'] = $file->storeAs('regulatory_texts/files', $storedFileFilename, 'public');
            $updateData['file_filename'] = $fileFilename . '.' . $extension;
        }

        if ($request->has('is_published')) {
            $updateData['is_published'] = $request->is_published;
            $updateData['published_at'] = $request->is_published ? ($regulatory_text->published_at ?? now()) : null;
        }

        $regulatory_text->update($updateData);

        // Handle multiple images removal
        if ($request->has('remove_images') && is_array($request->remove_images)) {
            foreach ($request->remove_images as $imageId) {
                $regulatory_textImage = RegulatoryTextImage::where('id', $imageId)->where('regulatory_text_id', $regulatory_text->id)->first();
                if ($regulatory_textImage) {
                    if (Storage::disk('public')->exists($regulatory_textImage->image_path)) {
                        Storage::disk('public')->delete($regulatory_textImage->image_path);
                    }
                    $regulatory_textImage->delete();
                }
            }
        }

        // Handle new multiple images
        if ($request->hasFile('images')) {
            $maxOrder = RegulatoryTextImage::where('regulatory_text_id', $regulatory_text->id)->max('order') ?? -1;
            $order = $maxOrder + 1;
            foreach ($request->file('images') as $image) {
                $imgFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imgFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imgFilename);
                $imgFilename = substr($imgFilename, 0, 100);
                $storedImageFilename = 'regulatory_text_' . $regulatory_text->id . '_' . time() . '_' . $order . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
                $imgPath = $image->storeAs('regulatory_texts/images', $storedImageFilename, 'public');
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $image->getRealPath());
                finfo_close($finfo);
                RegulatoryTextImage::create([
                    'regulatory_text_id' => $regulatory_text->id,
                    'image_path' => $imgPath,
                    'image_filename' => $imgFilename . '.' . $image->getClientOriginalExtension(),
                    'mime_type' => $mimeType,
                    'file_size' => $image->getSize(),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $regulatory_text->fresh()->load(['author', 'images']), 'message' => 'RegulatoryText updated successfully.']);
    }

    public function destroy($id)
    {
        $regulatory_text = RegulatoryText::findOrFail($id);
        if ($regulatory_text->image_path && Storage::disk('public')->exists($regulatory_text->image_path)) {
            Storage::disk('public')->delete($regulatory_text->image_path);
        }
        if ($regulatory_text->file_path && Storage::disk('public')->exists($regulatory_text->file_path)) {
            Storage::disk('public')->delete($regulatory_text->file_path);
        }
        foreach ($regulatory_text->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }
        $regulatory_text->delete();
        return response()->json(['success' => true, 'message' => 'RegulatoryText deleted successfully.']);
    }
}
