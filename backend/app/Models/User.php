<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'password', 'profile_image', 'phone', 'role_id','status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function phonePrefix()
    {
        return $this->belongsTo(PhonePrefix::class);
    }

    public function doctorSpecialties(): HasMany
{
    return $this->hasMany(
        DoctorSpecialty::class,
        'doctor_id'
    );
}

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'admin_permissions',
            'admin_id',
            'permission_id'
        );
    }

    public function adminPermissions(): HasMany
    {
        return $this->hasMany(AdminPermission::class, 'admin_id');
    }

    public function hasPermission(string $permission): bool
{
    if ($this->role?->name === 'head_admin') {
        return true;
    }

    return $this->permissions()
        ->where('name', $permission)
        ->exists();
}
    public function doctorSchedule():HasMany
    {
        return $this->HasMany(DoctorSchedule::class);
    }

        public function doctorAppointments()
    {
        return $this->hasMany(
            Appointment::class,
            'doctor_id'
        );
    }

    public function patientAppointments()
    {
        return $this->hasMany(
            Appointment::class,
            'patient_id'
        );
    }
}
