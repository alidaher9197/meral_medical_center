<?php

namespace App\Policies;

use App\Models\AdminPermission;
use App\Models\User;

class AdminPermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function view(
        User $user,
        AdminPermission $adminPermission
    ): bool {
        return $user->role->name === 'head_admin';
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function update(
        User $user,
        AdminPermission $adminPermission
    ): bool {
        return $user->role->name === 'head_admin';
    }

    public function delete(
        User $user,
        AdminPermission $adminPermission
    ): bool {
        return $user->role->name === 'head_admin';
    }
}