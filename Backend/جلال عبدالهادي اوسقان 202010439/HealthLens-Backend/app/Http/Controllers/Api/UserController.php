<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'username'   => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username,' . $user->id],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'     => ['nullable', 'in:male,female'],
            'password'   => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $userData = $user->toArray();
        if (!empty($userData['avatar'])) {
            $userData['avatar'] = url('storage/' . ltrim($userData['avatar'], '/'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $userData,
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Upload or replace user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png'],
        ]);

        // Delete old avatar media & file if exists
        $oldAvatar = $user->media()->where('type', 'avatar')->first();
        if ($oldAvatar) {
            Storage::disk('public')->delete($oldAvatar->file_path);
            $oldAvatar->delete();
        }

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');

        $media = $user->media()->create([
            'type'       => 'avatar',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        // Optional: also store direct avatar path on users table
        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => $media->url,
            ],
        ]);
    }
}
