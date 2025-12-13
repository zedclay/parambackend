<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminStudentsController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $students = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function store(Request $request)
    {
        // Security: Strong password requirements
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'regex:/[a-z]/',      // Must contain lowercase
                'regex:/[A-Z]/',      // Must contain uppercase
                'regex:/[0-9]/',      // Must contain number
                'regex:/[@$!%*#?&]/', // Must contain special character
            ],
            'must_change_password' => 'sometimes|boolean',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'must_change_password' => $request->must_change_password ?? true,
            'is_active' => true,
        ]);

        // TODO: Send email with credentials

        return response()->json([
            'success' => true,
            'data' => $student,
            'message' => 'Student created successfully.',
        ], 201);
    }

    public function show($id)
    {
        $student = User::with(['enrolledModules', 'assignedNotes'])->findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $student,
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'is_active' => 'sometimes|boolean',
            'year_id' => 'sometimes|nullable|exists:years,id',
            'group_id' => 'sometimes|nullable|exists:groups,id',
            'filiere_id' => 'sometimes|nullable|exists:filieres,id',
            'speciality_id' => 'sometimes|nullable|exists:specialities,id',
            'student_number' => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Security: Sanitize text inputs to prevent XSS
        $updateData = $request->only(['name', 'email', 'is_active', 'year_id', 'group_id', 'filiere_id', 'speciality_id', 'student_number']);

        // Sanitize name and student_number fields
        if (isset($updateData['name'])) {
            $updateData['name'] = strip_tags(trim($updateData['name']));
        }
        if (isset($updateData['student_number'])) {
            $updateData['student_number'] = strip_tags(trim($updateData['student_number']));
        }

        $student->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $student->fresh(),
            'message' => 'Student updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        // Deactivate instead of delete
        $student->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Student deactivated successfully.',
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        // Security: Strong password requirements
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'regex:/[a-z]/',      // Must contain lowercase
                'regex:/[A-Z]/',      // Must contain uppercase
                'regex:/[0-9]/',      // Must contain number
                'regex:/[@$!%*#?&]/', // Must contain special character
            ],
            'must_change_password' => 'sometimes|boolean',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $student->password = Hash::make($request->password);
        $student->must_change_password = $request->must_change_password ?? true;
        $student->save();

        // TODO: Send email with new password

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
        ]);
    }

    public function assignModules(Request $request, $id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $student->enrolledModules()->sync($request->module_ids);

        return response()->json([
            'success' => true,
            'message' => 'Modules assigned successfully.',
        ]);
    }

    public function activity($id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_USER',
                    'message' => 'User is not a student.',
                ],
            ], 400);
        }

        $downloads = DownloadLog::where('student_id', $id)
            ->with('note')
            ->orderBy('downloaded_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $downloads,
        ]);
    }
}
