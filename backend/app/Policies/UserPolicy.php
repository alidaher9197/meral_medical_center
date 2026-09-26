<?php

namespace App\Policies;

use App\Models\User;

use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
{
    return $user->role->name === 'head_admin';
}

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $targetUser): bool
    {
    // User can update their own profile
    if ($user->id === $targetUser->id) {
        return true;
    }

    // Updating a doctor
    if ($targetUser->role->name === 'doctor') {
        return $user->hasPermission('doctor.view') || $user->hasPermission('user.view');
    }

    // Updating a patient
    if ($targetUser->role->name === 'patient') {
        return $user->hasPermission('patient.view') || $user->hasPermission('user.view') ;
    }

    return false;
}

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $targetUser): bool
{   
    if ($user->role->name === 'head_admin' || $user->id === $targetUser->id) {
        return true;
    }
    

    // Updating a doctor
    if ($targetUser->role->name === 'doctor') {
        return $user->hasPermission('doctor.update') || $user->hasPermission('user.update');
    }

    // Updating a patient
    if ($targetUser->role->name === 'patient') {
        return $user->hasPermission('patient.update') || $user->hasPermission('user.update');
    }

    return false;
}
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
    public function updatePassword(User $user, User $targetUser): bool
    {
        // User can change their own password
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Head Admin can reset another user's password
        if ($user->role->name === 'head_admin') {
            return true;
        }

        return false;
    }
    public function showPatients(User $user): bool
    {
        
        // Check if the user has permission to view patients
        return $user->hasPermission('patient.view');
    }
    public function showDoctors(User $user): bool
    {
        if ($user->status !== 'approved') {
        return false;
        }
        return true;
    }
    public function showAdmins(User $user): bool
{
    return $user->role->name === 'head_admin';
}
    public function updateStatus(User $user, User $targetUser): bool
{   
    if ($user->role->name === 'head_admin') {
        return true;
    }
    // Updating a doctor
    if ($targetUser->role->name === 'doctor') {
        return $user->hasPermission('doctor.update') || $user->hasPermission('user.update');
    }

    // Updating a patient
    if ($targetUser->role->name === 'patient') {
        return $user->hasPermission('patient.update') || $user->hasPermission('user.update');
    }

    return false;

}
}
