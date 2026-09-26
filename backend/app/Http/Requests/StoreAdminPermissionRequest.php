<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'permission_id' => [
                'required',
                'integer',
                'exists:permissions,id',
            ],
        ];
    }
}