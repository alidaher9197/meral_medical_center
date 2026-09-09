<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['doctor_id', 'specialty_id', 'license_image'])]
class DoctorSpeialty extends Model
{
    //
}
