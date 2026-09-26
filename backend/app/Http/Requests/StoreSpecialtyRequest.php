<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:specialties,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The specialty name is required.',
            'name.string' => 'The specialty name must be a string.',
            'name.max' => 'The specialty name may not be greater than 255 characters.',
            'name.unique' => 'This specialty already exists.',
        ];
    }
}

