<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminAnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $announcements]);
    }

    public function store(Request $request)
    {
        // Security: Enhanced validation with file upload support
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'title.fr' => 'required|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'content' => 'required|array',
            'content.fr' => 'required|string',
            'content.ar' => 'nullable|string',
            'is_published' => 'sometimes|boolean',
            'target_audience' => 'sometimes|in:all,students,specific_specialite',
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
            'pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        // Security: Validate actual file content
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        if ($mimeType !== 'application/pdf') {
                            $fail('Invalid file type. Only PDF files are allowed.');
                        }

                        if ($value->getSize() > 10485760) {
                            $fail('PDF size must not exceed 10MB.');
                        }
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        // Handle image upload
        $imagePath = null;
        $imageFilename = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'announcement_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('announcements/images', $storedImageFilename, 'public');
            $imageFilename = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        // Handle PDF upload
        $pdfPath = null;
        $pdfFilename = null;
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            $pdfFilename = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
            $pdfFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pdfFilename);
            $pdfFilename = substr($pdfFilename, 0, 100);
            $storedPdfFilename = 'announcement_' . time() . '_' . hash('sha256', uniqid()) . '.pdf';
            $pdfPath = $pdf->storeAs('announcements/pdfs', $storedPdfFilename, 'public');
            $pdfFilename = $pdfFilename . '.pdf';
        }

        // Security: Sanitize text inputs
        $title = $request->title;
        if (isset($title['fr'])) {
            $title['fr'] = strip_tags(trim($title['fr']));
        }
        if (isset($title['ar'])) {
            $title['ar'] = strip_tags(trim($title['ar']));
        }

        $announcement = Announcement::create([
            'title' => $title,
            'content' => $request->content,
            'author_id' => $request->user()->id,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
            'target_audience' => $request->target_audience ?? 'all',
            'image_path' => $imagePath,
            'pdf_path' => $pdfPath,
            'image_filename' => $imageFilename,
            'pdf_filename' => $pdfFilename,
        ]);

        return response()->json(['success' => true, 'data' => $announcement->load('author'), 'message' => 'Announcement created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        // Security: Enhanced validation with file upload support
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|array',
            'title.fr' => 'sometimes|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'content' => 'sometimes|array',
            'is_published' => 'sometimes|boolean',
            'target_audience' => 'sometimes|in:all,students,specific_specialite',
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
            'pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $value->getRealPath());
                        finfo_close($finfo);

                        if ($mimeType !== 'application/pdf') {
                            $fail('Invalid file type. Only PDF files are allowed.');
                        }
                    }
                }
            ],
            'remove_image' => 'sometimes|boolean',
            'remove_pdf' => 'sometimes|boolean',
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
            $updateData['title'] = $title;
        }

        // Handle content update
        if ($request->has('content')) {
            $updateData['content'] = $request->content;
        }

        // Handle target audience
        if ($request->has('target_audience')) {
            $updateData['target_audience'] = $request->target_audience;
        }

        // Handle image upload/removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $updateData['image_path'] = null;
            $updateData['image_filename'] = null;
        } elseif ($request->hasFile('image')) {
            // Delete old image
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }

            $image = $request->file('image');
            $imageFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $imageFilename);
            $imageFilename = substr($imageFilename, 0, 100);
            $storedImageFilename = 'announcement_' . time() . '_' . hash('sha256', uniqid()) . '.' . $image->getClientOriginalExtension();
            $updateData['image_path'] = $image->storeAs('announcements/images', $storedImageFilename, 'public');
            $updateData['image_filename'] = $imageFilename . '.' . $image->getClientOriginalExtension();
        }

        // Handle PDF upload/removal
        if ($request->has('remove_pdf') && $request->remove_pdf) {
            if ($announcement->pdf_path && Storage::disk('public')->exists($announcement->pdf_path)) {
                Storage::disk('public')->delete($announcement->pdf_path);
            }
            $updateData['pdf_path'] = null;
            $updateData['pdf_filename'] = null;
        } elseif ($request->hasFile('pdf')) {
            // Delete old PDF
            if ($announcement->pdf_path && Storage::disk('public')->exists($announcement->pdf_path)) {
                Storage::disk('public')->delete($announcement->pdf_path);
            }

            $pdf = $request->file('pdf');
            $pdfFilename = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
            $pdfFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pdfFilename);
            $pdfFilename = substr($pdfFilename, 0, 100);
            $storedPdfFilename = 'announcement_' . time() . '_' . hash('sha256', uniqid()) . '.pdf';
            $updateData['pdf_path'] = $pdf->storeAs('announcements/pdfs', $storedPdfFilename, 'public');
            $updateData['pdf_filename'] = $pdfFilename . '.pdf';
        }

        // Handle publish status
        if ($request->has('is_published')) {
            $updateData['is_published'] = $request->is_published;
            $updateData['published_at'] = $request->is_published ? ($announcement->published_at ?? now()) : null;
        }

        $announcement->update($updateData);

        return response()->json(['success' => true, 'data' => $announcement->fresh()->load('author'), 'message' => 'Announcement updated successfully.']);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        // Security: Delete associated files
        if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        if ($announcement->pdf_path && Storage::disk('public')->exists($announcement->pdf_path)) {
            Storage::disk('public')->delete($announcement->pdf_path);
        }

        $announcement->delete();
        return response()->json(['success' => true, 'message' => 'Announcement deleted successfully.']);
    }
}
