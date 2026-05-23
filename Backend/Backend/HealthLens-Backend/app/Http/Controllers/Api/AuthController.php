<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'username'   => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password'   => ['required', 'string', 'min:6', 'confirmed'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'     => ['nullable', 'in:male,female'],
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'username'   => $data['username'],
            'password'   => Hash::make($data['password']),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender'     => $data['gender'] ?? null,
            'plan'       => 'free',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = $user->toArray();
        if (!empty($userData['avatar'])) {
            $userData['avatar'] = url('storage/' . ltrim($userData['avatar'], '/'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'user'  => $userData,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user (username + password)
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid username or password.'],
            ]);
        }

        // Optional: revoke old tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        $userData = $user->toArray();
        if (!empty($userData['avatar'])) {
            $userData['avatar'] = url('storage/' . ltrim($userData['avatar'], '/'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'data' => [
                'user'  => $userData,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout current user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Transform avatar path to full URL if it exists
        $userData = $user->toArray();
        if (!empty($userData['avatar'])) {
            $userData['avatar'] = url('storage/' . ltrim($userData['avatar'], '/'));
        }
        
        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    /**
     * Delete user account (hard delete)
     * Requires password confirmation for security
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify password for security
        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect. Account deletion cancelled.',
            ], 422);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user media files
        foreach ($user->media as $media) {
            if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        }

        // Delete user (cascade will handle: chats, messages, surgery_analyses, surgery_reports)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully. All your data has been permanently removed.',
        ]);
    }
}
