<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $exception = $this->route('doctorException');

        return [
            'exception_date' => [
                'sometimes',
                'date',
                'after_or_equal:today',
                Rule::unique('doctor_exceptions', 'exception_date')
                    ->where(function ($query) {
                        return $query->where(
                            'doctor_id',
                            $this->doctor_id ?? $this->route('doctorException')->doctor_id
                        );
                    })
                    ->ignore($exception?->id),
            ],

            'is_unavailable' => [
                'sometimes',
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