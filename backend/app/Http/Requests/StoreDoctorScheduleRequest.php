<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\DoctorSchedule;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'days_of_week' => [
                'required',
                'integer',
                'between:0,6',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'slot_duration' => [
                'nullable',
                'integer',
                'in:15,30,45,60',
            ],

            'is_available' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' =>
                'The doctor is required.',

            'doctor_id.integer' =>
                'The doctor ID must be an integer.',

            'doctor_id.exists' =>
                'The selected doctor does not exist.',

            'days_of_week.required' =>
                'The day of the week is required.',

            'days_of_week.integer' =>
                'The day of the week must be an integer.',

            'days_of_week.between' =>
                'The day of the week must be between 0 and 6.',

            'start_time.required' =>
                'The start time is required.',

            'start_time.date_format' =>
                'The start time must use HH:MM format.',

            'end_time.required' =>
                'The end time is required.',

            'end_time.date_format' =>
                'The end time must use HH:MM format.',

            'end_time.after' =>
                'The end time must be after the start time.',

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

                $doctorId = $this->input('doctor_id');
                $day = $this->input('days_of_week');
                $startTime = $this->input('start_time');
                $endTime = $this->input('end_time');
                $slotDuration = $this->input('slot_duration', 15);

                if (
                    !$doctorId ||
                    !$day ||
                    !$startTime ||
                    !$endTime
                ) {
                    return;
                }

                $slot = (int) $slotDuration;

                /*
                |--------------------------------------------------------------------------
                | Convert times to minutes
                |--------------------------------------------------------------------------
                */

                $startMinutes =
                    ((int) substr($startTime, 0, 2) * 60)
                    + (int) substr($startTime, 3, 2);

                $endMinutes =
                    ((int) substr($endTime, 0, 2) * 60)
                    + (int) substr($endTime, 3, 2);

                /*
                |--------------------------------------------------------------------------
                | Check start time is a multiple of slot duration
                |--------------------------------------------------------------------------
                */

                if ($startMinutes % $slot !== 0) {
                    $validator->errors()->add(
                        'start_time',
                        "The start time must be a multiple of {$slot} minutes."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Check end time is a multiple of slot duration
                |--------------------------------------------------------------------------
                */

                if ($endMinutes % $slot !== 0) {
                    $validator->errors()->add(
                        'end_time',
                        "The end time must be a multiple of {$slot} minutes."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Check overlapping schedules
                |--------------------------------------------------------------------------
                */

                $overlappingSchedule = DoctorSchedule::where(
                    'doctor_id',
                    $doctorId
                )
                    ->where('days_of_week', $day)
                    ->where(function ($query) use ($startTime, $endTime) {

                        $query->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    })
                    ->exists();

                if ($overlappingSchedule) {
                    $validator->errors()->add(
                        'start_time',
                        'The doctor already has a schedule that overlaps this time on this day.'
                    );
                }
            }
        ];
    }
}