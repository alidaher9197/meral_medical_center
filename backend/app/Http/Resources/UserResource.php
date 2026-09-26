<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            "phone" => $this->phone,
            "role_id" => $this->role_id,
            'role'=> $this->whenLoaded('role', function () {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                ];
            }),
            'profile_image' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'doctor_specialties' => $this->whenLoaded(
    'doctorSpecialties',
    function () {
        return $this->doctorSpecialties->map(function ($item) {
            return [
                'id' => $item->id,
                'specialty' => [
                    'id' => $item->specialty->id,
                    'name' => $item->specialty->name,
                ],
            ];
        });
    }
),
        ];
    }
}
