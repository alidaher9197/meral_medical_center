<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function create(User $user): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->role->name === 'head_admin';
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->role->name === 'head_admin';
    }
}