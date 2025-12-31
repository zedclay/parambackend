<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\DownloadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminDownloadsController extends Controller
{
    public function index()
    {
        $downloads = Download::with(['author', 'images'])->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $downloads]);
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
            $storedImageFilename = 'download_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('downloads/images', $storedImageFilename, 'public');
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
            $storedFileFilename = 'download_' . time() . '_' . hash('sha256', uniqid()) . '.' . $extension;
            $filePath = $file->storeAs('downloads/files', $storedFileFilename, 'public');
            $fileFilename = $fileFilename . '.' . $extension;
        }

        $title = $request->title;
        if (isset($title['fr'])) {
            $title['fr'] = strip_tags(trim($title['fr']));
        }
        if (isset($title['ar'])) {
            $title['ar'] = strip_tags(trim($title['ar']));
        }

        $download = Download::create([
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
                $storedImageFilename = 'download_' . $download->id . '_' . time() . '_' . $order . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
                $imgPath = $image->storeAs('downloads/images', $storedImageFilename, 'public');

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $image->getRealPath());
                finfo_close($finfo);

                DownloadImage::create([
                    'download_id' => $download->id,
                    'image_path' => $imgPath,
                    'image_filename' => $imgFilename . '.' . $image->getClientOriginalExtension(),
                    'mime_type' => $mimeType,
                    'file_size' => $image->getSize(),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $download->load(['author', 'images']), 'message' => 'Download created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $download = Download::findOrFail($id);
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
            if ($download->image_path && Storage::disk('public')->exists($download->image_path)) {
                Storage::disk('public')->delete($download->image_path);
            }
            $updateData['image_path'] = null;
            $updateData['image_filename'] = null;
        } elseif ($request->hasFile('image')) {
            if ($download->image_path && Storage::disk('public')->exists($download->image_path)) {
                Storage::disk('public')->delete($download->image_path);
            }
            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'download_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $updateData['image_path'] = $image->storeAs('downloads/images', $storedImageFilename, 'public');
            $updateData['image_filename'] = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        // Handle file
        if ($request->has('remove_file') && $request->remove_file) {
            if ($download->file_path && Storage::disk('public')->exists($download->file_path)) {
                Storage::disk('public')->delete($download->file_path);
            }
            $updateData['file_path'] = null;
            $updateData['file_filename'] = null;
        } elseif ($request->hasFile('file')) {
            if ($download->file_path && Storage::disk('public')->exists($download->file_path)) {
                Storage::disk('public')->delete($download->file_path);
            }
            $file = $request->file('file');
            $fileFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileFilename);
            $fileFilename = substr($fileFilename, 0, 100);
            $extension = $file->getClientOriginalExtension();
            $storedFileFilename = 'download_' . time() . '_' . hash('sha256', uniqid()) . '.' . $extension;
            $updateData['file_path'] = $file->storeAs('downloads/files', $storedFileFilename, 'public');
            $updateData['file_filename'] = $fileFilename . '.' . $extension;
        }

        if ($request->has('is_published')) {
            $updateData['is_published'] = $request->is_published;
            $updateData['published_at'] = $request->is_published ? ($download->published_at ?? now()) : null;
        }

        $download->update($updateData);

        // Handle multiple images removal
        if ($request->has('remove_images') && is_array($request->remove_images)) {
            foreach ($request->remove_images as $imageId) {
                $downloadImage = DownloadImage::where('id', $imageId)->where('download_id', $download->id)->first();
                if ($downloadImage) {
                    if (Storage::disk('public')->exists($downloadImage->image_path)) {
                        Storage::disk('public')->delete($downloadImage->image_path);
                    }
                    $downloadImage->delete();
                }
            }
        }

        // Handle new multiple images
        if ($request->hasFile('images')) {
            $maxOrder = DownloadImage::where('download_id', $download->id)->max('order') ?? -1;
            $order = $maxOrder + 1;
            foreach ($request->file('images') as $image) {
                $imgFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $imgFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imgFilename);
                $imgFilename = substr($imgFilename, 0, 100);
                $storedImageFilename = 'download_' . $download->id . '_' . time() . '_' . $order . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
                $imgPath = $image->storeAs('downloads/images', $storedImageFilename, 'public');
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $image->getRealPath());
                finfo_close($finfo);
                DownloadImage::create([
                    'download_id' => $download->id,
                    'image_path' => $imgPath,
                    'image_filename' => $imgFilename . '.' . $image->getClientOriginalExtension(),
                    'mime_type' => $mimeType,
                    'file_size' => $image->getSize(),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $download->fresh()->load(['author', 'images']), 'message' => 'Download updated successfully.']);
    }

    public function destroy($id)
    {
        $download = Download::findOrFail($id);
        if ($download->image_path && Storage::disk('public')->exists($download->image_path)) {
            Storage::disk('public')->delete($download->image_path);
        }
        if ($download->file_path && Storage::disk('public')->exists($download->file_path)) {
            Storage::disk('public')->delete($download->file_path);
        }
        foreach ($download->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }
        $download->delete();
        return response()->json(['success' => true, 'message' => 'Download deleted successfully.']);
    }
}
