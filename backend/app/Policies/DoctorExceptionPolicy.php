<?php

namespace App\Policies;

use App\Models\DoctorException;
use App\Models\User;

class DoctorExceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('doctor_exception.view');
    }

    public function view(User $user, DoctorException $doctorException): bool
    {
        // Doctor can view his own exception
        if (
            $user->role?->name === 'doctor' &&
            $doctorException->doctor_id === $user->id
        ) {
            return true;
        }

        // Admin/head_admin with permission
        return $user->hasPermission('doctor_exception.view');
    }

    public function create(User $user, int $doctorId): bool
    {
        $doctor = User::with('role')->find($doctorId);

        // The selected user must be a doctor
        if (!$doctor || $doctor->role?->name !== 'doctor') {
            return false;
        }
        // Doctor can create an exception for himself
        if (
            $user->role?->name === 'doctor' &&
            $user->id === $doctorId
        ) {
            return true;
        }

        // Admin/head_admin with permission
        return $user->hasPermission('doctor_exception.create');
    }

    public function update(User $user, DoctorException $doctorException): bool
    {
        // Doctor can update his own exception
        if (
            $user->role?->name === 'doctor' &&
            $doctorException->doctor_id === $user->id
        ) {
            return true;
        }

        // Admin/head_admin with permission
        return $user->hasPermission('doctor_exception.update');
    }

    public function delete(User $user, DoctorException $doctorException): bool
    {
        // Doctor can delete his own exception
        if (
            $user->role?->name === 'doctor' &&
            $doctorException->doctor_id === $user->id
        ) {
            return true;
        }

        // Admin/head_admin with permission
        return $user->hasPermission('doctor_exception.delete');
    }
}