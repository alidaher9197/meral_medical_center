<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminPermissionRequest;
use App\Http\Requests\UpdateAdminPermissionRequest;
use App\Models\AdminPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPermissionController extends Controller
{
    /**
     * Get permissions assigned to admins.
     *
     * Optional:
     * ?admin_id=5
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AdminPermission::class);

        $query = AdminPermission::with([
            'admin.role',
            'permission'
        ]);

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        $permissions = $query->get();

        return response()->json([
            'message' => 'Admin permissions retrieved successfully.',
            'admin_permissions' => $permissions,
        ], 200);
    }


    /**
     * Assign a permission to an admin.
     */
    public function store(StoreAdminPermissionRequest $request)
    {
        Gate::authorize('create', AdminPermission::class);

        $admin = User::with('role')->findOrFail($request->admin_id);

        // Only normal admins can receive these permissions.
        if (!$admin->role || $admin->role->name !== 'admin') {
            return response()->json([
                'message' => 'Permissions can only be assigned to admin users.'
            ], 422);
        }

        // Prevent duplicate permission assignment.
        $alreadyExists = AdminPermission::where('admin_id', $admin->id)
            ->where('permission_id', $request->permission_id)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'This permission is already assigned to this admin.'
            ], 422);
        }

        $adminPermission = AdminPermission::create([
            'admin_id' => $admin->id,
            'permission_id' => $request->permission_id,
        ]);

        $adminPermission->load([
            'admin.role',
            'permission'
        ]);

        return response()->json([
            'message' => 'Permission assigned successfully.',
            'admin_permission' => $adminPermission,
        ], 201);
    }


    /**
     * Get one admin permission assignment.
     */
    public function show(AdminPermission $adminPermission)
    {
        Gate::authorize('view', $adminPermission);

        $adminPermission->load([
            'admin.role',
            'permission'
        ]);

        return response()->json([
            'message' => 'Admin permission retrieved successfully.',
            'admin_permission' => $adminPermission,
        ], 200);
    }


    /**
     * Change an admin's assigned permission.
     */
    public function update(
        UpdateAdminPermissionRequest $request,
        AdminPermission $adminPermission
    ) {
        Gate::authorize('update', $adminPermission);

        $data = $request->validated();

        if (isset($data['admin_id'])) {
            $admin = User::with('role')->findOrFail($data['admin_id']);

            if (!$admin->role || $admin->role->name !== 'admin') {
                return response()->json([
                    'message' => 'Permissions can only be assigned to admin users.'
                ], 422);
            }
        }

        $adminPermission->update($data);

        $adminPermission->load([
            'admin.role',
            'permission'
        ]);

        return response()->json([
            'message' => 'Admin permission updated successfully.',
            'admin_permission' => $adminPermission,
        ], 200);
    }


    /**
     * Remove a permission from an admin.
     */
    public function destroy(AdminPermission $adminPermission)
    {
        Gate::authorize('delete', $adminPermission);

        $adminPermission->delete();

        return response()->json([
            'message' => 'Permission removed successfully.',
        ], 200);
    }
}