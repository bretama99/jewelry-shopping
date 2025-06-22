<?php
// File: app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index()
    {
        return view('auth.profile');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'passport_id_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload profile picture.
     */
    public function uploadPicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048' // 2MB max
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a valid image file (JPEG, PNG, JPG, GIF, WebP) under 2MB.'
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                $oldPicturePath = public_path('images/users/' . $user->profile_picture);
                if (file_exists($oldPicturePath)) {
                    unlink($oldPicturePath);
                }
            }

            // Create users directory if it doesn't exist
            $uploadPath = public_path('images/users');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate unique filename
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // Move file to public/images/users/
            $file->move($uploadPath, $filename);

            // Update user record
            $user->update(['profile_picture' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully!',
                'url' => asset('images/users/' . $filename)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove profile picture.
     */
    public function removePicture()
    {
        try {
            $user = Auth::user();
            
            if ($user->profile_picture) {
                // Delete file from disk
                $picturePath = public_path('images/users/' . $user->profile_picture);
                if (file_exists($picturePath)) {
                    unlink($picturePath);
                }

                // Update user record
                $user->update(['profile_picture' => null]);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile picture removed successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No profile picture to remove.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove profile picture. Please try again.'
            ], 500);
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password. Please try again.'
            ], 500);
        }
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => ['boolean'],
            'order_updates' => ['boolean'],
            'marketing_emails' => ['boolean'],
            'timezone' => ['string', 'max:50'],
            'language' => ['string', 'max:5'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            // Get current preferences and merge with new ones
            $currentPreferences = $user->preferences ?? [];
            $newPreferences = array_merge($currentPreferences, $validator->validated());
            
            $user->update(['preferences' => $newPreferences]);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully!',
                'preferences' => $newPreferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user activity log.
     */
    public function getActivity()
    {
        try {
            $user = Auth::user();
            
            // This would typically come from a dedicated activity log table
            $activities = [
                [
                    'type' => 'login',
                    'description' => 'Logged in',
                    'timestamp' => $user->last_login_at ?? $user->updated_at,
                    'icon' => 'fas fa-sign-in-alt',
                    'color' => 'success'
                ],
                [
                    'type' => 'profile_update',
                    'description' => 'Profile updated',
                    'timestamp' => $user->updated_at,
                    'icon' => 'fas fa-user-edit',
                    'color' => 'primary'
                ],
                [
                    'type' => 'account_created',
                    'description' => 'Account created',
                    'timestamp' => $user->created_at,
                    'icon' => 'fas fa-user-plus',
                    'color' => 'info'
                ]
            ];

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity log.'
            ], 500);
        }
    }

    /**
     * Delete user account (soft delete).
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'confirmation' => ['required', 'in:DELETE']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect.'
            ], 422);
        }

        try {
            // Soft delete the user account
            $user->delete();

            // Log out the user
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.',
                'redirect' => route('external.home')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again.'
            ], 500);
        }
    }
}