<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * Get all available permissions.
     */
    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::orderBy('name')->get();

        return response()->json([
            'message' => 'Permissions retrieved successfully.',
            'permissions' => $permissions,
        ], 200);
    }
}