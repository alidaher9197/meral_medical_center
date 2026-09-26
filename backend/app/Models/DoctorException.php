<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorException extends Model
{
    protected $fillable = [
        'doctor_id',
        'exception_date',
        'is_unavailable',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'is_unavailable' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}