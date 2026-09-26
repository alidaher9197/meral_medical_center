<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],

            'permission_id' => [
                'sometimes',
                'integer',
                'exists:permissions,id',
            ],
        ];
    }
}