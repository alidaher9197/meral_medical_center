<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Specialty;
class SpecialtyPolicy
{   
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Specialty $specialty): bool
    {
        return true;
    }
    public function create(User $user): bool
    {
        return $user->role->name === 'head_admin';
    }
    public function update(User $user, Specialty $specialty): bool
    {
        return $user->role->name === 'head_admin';
    }
    public function delete(User $user, Specialty $specialty): bool
    {
        return $user->role->name === 'head_admin';
    }
}
