<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class StoreDoctorExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'exception_date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('doctor_exceptions', 'exception_date')
                    ->where(function ($query) {
                        return $query->where(
                            'doctor_id',
                            $this->doctor_id ?? Auth::id()
                        );
                    }),
            ],

            'is_unavailable' => [
                'required',
                'boolean',
            ],

            'start_time' => [
                'nullable',
                'date_format:H:i',
                'required_if:is_unavailable,false',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
                'required_if:is_unavailable,false',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}