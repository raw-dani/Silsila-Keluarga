<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SliderSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Get all admin users
     */
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'sub_admin'])
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($admins);
    }

    /**
     * Create a new admin user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'role' => 'required|in:admin,sub_admin'
        ], [
            'password.min' => 'Password must be at least 12 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Admin user created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ]
        ], 201);
    }

    /**
     * Delete an admin user
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();

        // Prevent deleting the current authenticated user
        if ($currentUser && $user->id === $currentUser->id) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        // Check if there are other admins (both admin and sub_admin roles)
        $adminCount = User::whereIn('role', ['admin', 'sub_admin'])->count();
        if ($adminCount <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last admin user. At least one admin account must remain.'
            ], 403);
        }

        // If deleting a sub_admin, allow it (since admin can manage sub_admins)
        // If deleting an admin, ensure there's at least one admin left
        if ($user->role === 'admin') {
            $pureAdminCount = User::where('role', 'admin')->count();
            if ($pureAdminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the last admin account. At least one admin account must remain.'
                ], 403);
            }
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get admin user details
     */
    public function show($id)
    {
        $user = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'created_at')
            ->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Upload slider image
     */
    public function uploadSliderImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            'slide_index' => 'required|integer|min:0|max:3'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Generate unique filename
            $filename = 'slide_' . $request->slide_index . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store in storage/app/public/slider-images
            $path = $file->storeAs('slider-images', $filename, 'public');

            // Get the public URL
            $imageUrl = asset('storage/' . $path);

            return response()->json([
                'message' => 'Gambar slider berhasil diupload',
                'image_path' => $imageUrl,
                'filename' => $filename
            ]);
        }

        return response()->json([
            'message' => 'File gambar tidak ditemukan'
        ], 400);
    }

    /**
     * Get slider data
     */
    public function getSliderData()
    {
        // Get slider data from database, ordered by slide_index
        $slides = SliderSetting::orderBy('slide_index')->get();

        // If no data exists, return default data
        if ($slides->isEmpty()) {
            $defaultSlides = [
                [
                    'title' => 'Kelola Data Keluarga',
                    'description' => 'Pantau dan kelola informasi lengkap anggota keluarga Anda dengan mudah',
                    'image' => null
                ],
                [
                    'title' => 'Sistem Approval Modern',
                    'description' => 'Permintaan perubahan data melalui sistem approval yang aman dan terstruktur',
                    'image' => null
                ],
                [
                    'title' => 'Visualisasi Pohon Keluarga',
                    'description' => 'Lihat struktur keluarga dalam bentuk pohon yang mudah dipahami',
                    'image' => null
                ],
                [
                    'title' => 'Keamanan & Privasi',
                    'description' => 'Data keluarga Anda aman dengan sistem keamanan modern',
                    'image' => null
                ]
            ];

            return response()->json($defaultSlides);
        }

        // Format the data for frontend
        $formattedSlides = $slides->map(function ($slide) {
            return [
                'title' => $slide->title,
                'description' => $slide->description,
                'image' => $slide->image_path ? asset('storage/' . $slide->image_path) : null,
                'is_visible' => $slide->is_visible
            ];
        });

        return response()->json($formattedSlides);
    }

    /**
     * Save slider data
     */
    public function saveSliderData(Request $request)
    {
        try {
            $request->validate([
                'slides' => 'required|array|min:4|max:4',
                'slides.*.title' => 'required|string|max:255',
                'slides.*.description' => 'required|string|max:1000',
                'slides.*.image' => 'nullable|string|url',
                'slides.*.is_visible' => 'boolean'
            ]);

        $slides = $request->slides;

        foreach ($slides as $index => $slideData) {
            // Extract image path from full URL
            $imagePath = null;
            if ($slideData['image']) {
                $storageUrl = asset('storage/');
                if (strpos($slideData['image'], $storageUrl) === 0) {
                    $imagePath = str_replace($storageUrl, '', $slideData['image']);
                }
            }

            SliderSetting::updateOrCreate(
                ['slide_index' => $index],
                [
                    'title' => $slideData['title'],
                    'description' => $slideData['description'],
                    'image_path' => $imagePath,
                    'is_visible' => $slideData['is_visible'] ?? true
                ]
            );
        }

            return response()->json([
                'message' => 'Pengaturan slider berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan slider: ' . $e->getMessage()
            ], 500);
        }
    }
}
