<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'doctor' => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->name,
            ],

            'day_of_week' => $this->days_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'slot_duration' => $this->slot_duration,
            'is_available' => $this->is_available,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}