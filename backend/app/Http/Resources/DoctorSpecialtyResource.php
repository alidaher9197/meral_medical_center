<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorSpecialtyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $canSeeCertificate =
            $user &&
            (
                $user->role->name === 'head_admin' ||
                $user->role->name === 'admin' ||
                $user->id === $this->doctor_id
            );

        return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'specialty_id' => $this->specialty_id,

            'certificate_image' => $canSeeCertificate
                ? ($this->certificate_image
                    ? asset('storage/' . $this->certificate_image)
                    : null)
                : null,
        ];
    }
}