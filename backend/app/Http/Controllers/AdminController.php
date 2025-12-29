<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Get all admin users
     */
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'created_at')
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
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin'
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

        // Check if there are other admins
        $adminCount = User::where('role', 'admin')->count();
        if ($adminCount <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last admin user'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Admin user deleted successfully'
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
}
