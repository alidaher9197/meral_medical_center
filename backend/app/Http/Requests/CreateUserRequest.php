<?php

namespace App\Http\Requests;

use App\Models\PhonePrefix;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role_id' => [
            'required',
            'integer',
            'exists:roles,id',
            'not_in:1',
            ],

            'phone' => [
                'required',
                'string',
                'digits:8',
                'unique:users,phone',

                function ($attribute, $value, $fail) {
                    if (!PhonePrefix::isValidPhonePrefix($value)) {
                        $fail('The phone number or its prefix is not valid.');
                    }
                },
            ],
            'profile_image' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:10240',
             ],
             'specialties' => [
    Rule::requiredIf(fn () => (int) $this->role_id === 3),
    'array',
    'min:1',

],

'specialties.*.id' => [
    'required',
    'integer',
    'distinct',
    'exists:specialties,id',
],

'specialties.*.image' => [
    'required',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:10240',
],
        ];
    }
}