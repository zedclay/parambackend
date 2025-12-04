<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminAnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $announcements]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'title.fr' => 'required|string',
            'title.ar' => 'nullable|string',
            'content' => 'required|array',
            'content.fr' => 'required|string',
            'content.ar' => 'nullable|string',
            'is_published' => 'sometimes|boolean',
            'target_audience' => 'sometimes|in:all,students,specific_specialite',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'author_id' => $request->user()->id,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
            'target_audience' => $request->target_audience ?? 'all',
        ]);

        return response()->json(['success' => true, 'data' => $announcement, 'message' => 'Announcement created successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|array',
            'content' => 'sometimes|array',
            'is_published' => 'sometimes|boolean',
            'target_audience' => 'sometimes|in:all,students,specific_specialite',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed', 'details' => $validator->errors()]], 422);
        }

        $updateData = $request->only(['title', 'content', 'target_audience']);
        if ($request->has('is_published')) {
            $updateData['is_published'] = $request->is_published;
            $updateData['published_at'] = $request->is_published ? ($announcement->published_at ?? now()) : null;
        }
        $announcement->update($updateData);

        return response()->json(['success' => true, 'data' => $announcement->fresh(), 'message' => 'Announcement updated successfully.']);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
        return response()->json(['success' => true, 'message' => 'Announcement deleted successfully.']);
    }
}
