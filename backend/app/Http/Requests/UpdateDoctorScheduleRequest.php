<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],

            'days_of_week' => [
                'sometimes',
                'integer',
                'between:0,6',
            ],

            'start_time' => [
                'sometimes',
                'date_format:H:i',
            ],

            'end_time' => [
                'sometimes',
                'date_format:H:i',
            ],

            'slot_duration' => [
                'sometimes',
                'integer',
                'in:15,30,45,60',
            ],

            'is_available' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.integer' =>
                'The doctor ID must be an integer.',

            'doctor_id.exists' =>
                'The selected doctor does not exist.',

            'days_of_week.integer' =>
                'The day of the week must be an integer.',

            'days_of_week.between' =>
                'The day of the week must be between 0 and 6.',

            'start_time.date_format' =>
                'The start time must use HH:MM format.',

            'end_time.date_format' =>
                'The end time must use HH:MM format.',

            'slot_duration.integer' =>
                'The slot duration must be an integer.',

            'slot_duration.in' =>
                'The slot duration must be 15, 30, 45, or 60 minutes.',

            'is_available.boolean' =>
                'The availability status must be true or false.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                $schedule = $this->route('doctor_schedule');

                /*
                |----------------------------------------------------------
                | Get new values if supplied.
                | Otherwise use the existing database values.
                |----------------------------------------------------------
                */

                $startTime = $this->input(
                    'start_time',
                    $schedule?->start_time
                );

                $endTime = $this->input(
                    'end_time',
                    $schedule?->end_time
                );

                $slotDuration = $this->input(
                    'slot_duration',
                    $schedule?->slot_duration
                );

                if (
                    !$startTime ||
                    !$endTime ||
                    !$slotDuration
                ) {
                    return;
                }

                $slot = (int) $slotDuration;

                // Convert start time to minutes
                $startMinutes =
                    ((int) substr($startTime, 0, 2) * 60)
                    + (int) substr($startTime, 3, 2);

                // Convert end time to minutes
                $endMinutes =
                    ((int) substr($endTime, 0, 2) * 60)
                    + (int) substr($endTime, 3, 2);

                // Check start time
                if ($startMinutes % $slot !== 0) {
                    $validator->errors()->add(
                        'start_time',
                        "The start time must be a multiple of {$slot} minutes."
                    );
                }

                // Check end time
                if ($endMinutes % $slot !== 0) {
                    $validator->errors()->add(
                        'end_time',
                        "The end time must be a multiple of {$slot} minutes."
                    );
                }

                // Check end time is after start time
                if ($endMinutes <= $startMinutes) {
                    $validator->errors()->add(
                        'end_time',
                        'The end time must be after the start time.'
                    );
                }
            }
        ];
    }
}