<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DoctorSchedule;

class DoctorSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('doctor_schedule.view');
    }

    public function view(User $user, DoctorSchedule $doctorSchedule): bool
    {
        return $user->hasPermission('doctor_schedule.view')
            || $user->id === $doctorSchedule->doctor_id;
    }

    public function create(User $user): bool
    {
        // Doctor can create a schedule for himself.
        if ($user->role->name === 'doctor') {
            return true;
        }

        // Users with permission can create schedules for any doctor.
        return $user->hasPermission('doctor_schedule.create');
    }

    public function update(
        User $user,
        DoctorSchedule $doctorSchedule
    ): bool {
        // Doctor can update only his own schedule.
        if (
            $user->role->name === 'doctor' &&
            $user->id === $doctorSchedule->doctor_id
        ) {
            return true;
        }

        return $user->hasPermission('doctor_schedule.update');
    }

    public function delete(
        User $user,
        DoctorSchedule $doctorSchedule
    ): bool {
        // Doctor can delete only his own schedule.
        if (
            $user->role->name === 'doctor' &&
            $user->id === $doctorSchedule->doctor_id
        ) {
            return true;
        }

        return $user->hasPermission('doctor_schedule.delete');
    }
}