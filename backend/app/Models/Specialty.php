<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name'])]
class Specialty extends Model
{
    protected $fillable = [
        'name',
    ];

    public function doctor_specialties()
    {
        return $this->hasMany(DoctorSpecialty::class, 'specialty_id');
    }
    
}
