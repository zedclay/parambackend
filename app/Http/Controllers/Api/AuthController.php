<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user (student or admin)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
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

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => __('messages.login_failed'),
                ],
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => __('messages.account_inactive'),
                ],
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'locale' => $user->locale,
            'must_change_password' => $user->must_change_password,
        ];

        // Include year and group data for students
        if ($user->role === 'student') {
            $userData['year_id'] = $user->year_id;
            $userData['group_id'] = $user->group_id;

            // Load year relationship with name
            if ($user->year_id) {
                $user->load('year');
                $userData['year'] = $user->year ? [
                    'id' => $user->year->id,
                    'year_number' => $user->year->year_number,
                    'name' => $user->year->name,
                ] : null;
            }

            // Load group relationship
            if ($user->group_id) {
                $user->load('group');
                $userData['group'] = $user->group ? [
                    'id' => $user->group->id,
                    'name' => $user->group->name,
                    'code' => $user->group->code,
                ] : null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'token' => $token,
            ],
            'message' => __('messages.login_success'),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'locale' => $user->locale,
            'must_change_password' => $user->must_change_password,
        ];

        // Include year and group data for students
        if ($user->role === 'student') {
            $userData['year_id'] = $user->year_id;
            $userData['group_id'] = $user->group_id;

            // Load year relationship with name
            if ($user->year_id) {
                $user->load('year');
                $userData['year'] = $user->year ? [
                    'id' => $user->year->id,
                    'year_number' => $user->year->year_number,
                    'name' => $user->year->name,
                ] : null;
            }

            // Load group relationship
            if ($user->group_id) {
                $user->load('group');
                $userData['group'] = $user->group ? [
                    'id' => $user->group->id,
                    'name' => $user->group->name,
                    'code' => $user->group->code,
                ] : null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
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

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'RESET_LINK_FAILED',
                'message' => 'Unable to send reset link.',
            ],
        ], 500);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
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

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->must_change_password = false;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'PASSWORD_RESET_FAILED',
                'message' => 'Invalid or expired reset token.',
            ],
        ], 400);
    }
}
