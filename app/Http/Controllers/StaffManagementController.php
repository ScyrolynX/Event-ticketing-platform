<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class StaffManagementController extends Controller
{
    /**
     * List every staff account (any user with at least one role).
     * Plain customers (no role at all) are excluded.
     */
    public function index()
    {
        $staff = User::role(['Admin', 'Event Manager', 'Box Office'])
            ->with('roles')
            ->get();

        return response()->json(['staff' => $staff]);
    }

    /**
     * Create a brand new staff account with a chosen role, in one step.
     * Only Admin can reach this route, enforced by route middleware.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Event Manager,Box Office',
        ])->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return response()->json(['user' => $user->load('roles')], 201);
    }

    /**
     * Change an existing staff member's role, or remove staff access
     * entirely (turning them back into a plain customer).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'nullable|in:Admin,Event Manager,Box Office',
        ]);

        $user->syncRoles($validated['role'] ? [$validated['role']] : []);

        return response()->json(['user' => $user->load('roles')]);
    }
}
