<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAppointmentRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use App\Models\Appointment;
use App\Models\DoctorException;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Return available appointment times for a doctor
     * from today until one month from today.
     */
    // public function index()
    // {
    //     $user = Auth::user();

    //     $query = Appointment::with(['doctor', 'patient'])->latest();

    //     if ($user->role?->name === 'patient') {
    //         $query->where('patient_id', $user->id);
    //     } elseif ($user->role?->name === 'doctor') {
    //         $query->where('doctor_id', $user->id);
    //     } elseif (!in_array($user->role?->name, ['admin', 'head_admin'], true)) {
    //         return response()->json(['message' => 'Unauthorized.'], 403);
    //     }

    //     return response()->json([
    //         'data' => $query->get(),
    //     ]);
    // }

    public function availableTimes(int $doctorId)
    {
        // Get doctor
        $doctor = User::with('role')->findOrFail($doctorId);

        // Doctor must be approved and have doctor role
        if (
            $doctor->status !== 'approved' ||
            $doctor->role?->name !== 'doctor'
        ) {
            return response()->json([
                'message' => 'Doctor is not available for appointments.'
            ], 403);
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonth();

        // Get doctor's normal schedules
        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('is_available', true)
            ->get();

        // Get doctor's exceptions for this period
        $exceptions = DoctorException::where('doctor_id', $doctorId)
            ->whereBetween('exception_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get()
            ->keyBy(function ($exception) {
                return $exception->exception_date->format('d-m-Y');
            });

        // Get doctor's existing appointments
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereNotIn('status', [
                'cancelled',
                'rejected',
            ])
            ->get()
            ->groupBy(function ($appointment) {
                return $appointment->appointment_date->format('d-m-Y');
            });

        $result = [];

        // Loop through every day
        for (
            $date = $startDate->copy();
            $date->lte($endDate);
            $date->addDay()
        ) {
            $dateString = $date->format('d-m-Y');

            /*
             * Check doctor exception.
             *
             * If doctor is unavailable for the whole day,
             * don't add this date to the result.
             */
            $exception = $exceptions->get($dateString);

            if ($exception && $exception->is_unavailable) {
                continue;
            }

            /*
             * Get normal schedule for this day.
             */
            $dayOfWeek = $date->dayOfWeek;

            $schedule = $schedules->first(function ($schedule) use ($dayOfWeek) {
                return $schedule->days_of_week == $dayOfWeek;
            });

            /*
             * If there is no schedule and no special exception,
             * the doctor doesn't work on this day.
             *
             * Don't return this date.
             */
            if (!$schedule && !$exception) {
                continue;
            }

            /*
             * Determine working hours.
             *
             * Special exception schedule overrides
             * the normal schedule.
             */
            if ($exception && !$exception->is_unavailable) {

                $startTime = $exception->start_time;
                $endTime = $exception->end_time;

            } else {

                $startTime = $schedule->start_time;
                $endTime = $schedule->end_time;
            }

            /*
             * Get booked appointment times.
             */
            $bookedTimes = $appointments
                ->get($dateString, collect())
                ->pluck('appointment_time')
                ->map(function ($time) {
                    return Carbon::parse($time)->format('H:i');
                })
                ->toArray();

            /*
             * Generate 15-minute slots.
             */
            $availableTimes = [];

            $currentTime = Carbon::parse(
                $dateString . ' ' . $startTime
            );

            $endDateTime = Carbon::parse(
                $dateString . ' ' . $endTime
            );

            while ($currentTime->lt($endDateTime)) {

                $time = $currentTime->format('H:i');

                if (!in_array($time, $bookedTimes)) {
                    $availableTimes[] = $time;
                }

                $currentTime->addMinutes(15);
            }

            /*
             * Don't return times that already passed today.
             */
            if ($date->isToday()) {

                $now = Carbon::now();

                $availableTimes = array_values(
                    array_filter(
                        $availableTimes,
                        function ($time) use ($dateString, $now) {

                            $slotDateTime = Carbon::parse(
                                $dateString . ' ' . $time
                            );

                            return $slotDateTime->gt($now);
                        }
                    )
                );
            }

            /*
             * If all slots are booked or already passed,
             * don't return this date.
             */
            if (empty($availableTimes)) {
                continue;
            }

            /*
             * Only return dates that actually have
             * available appointment times.
             */
            $result[] = [
                'date' => $dateString,
                'available_times' => $availableTimes,
            ];
        }

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
            ],

            'from' => $startDate->format('d-m-Y'),

            'to' => $endDate->format('d-m-Y'),

            'data' => $result,
        ]);
    }

    public function store(StoreAppointmentRequest $request)
{
    $patient = Auth::user();

    // Only patients can reserve appointments
    if ($patient->id === $request->doctor_id) {
        return response()->json([
            'message' => 'doctor is making appointment to himself.'
        ], 403);
    }

    // Get doctor
    $doctor = User::with('role')
        ->find($request->doctor_id);

    // Check doctor exists and is approved
    if (
        !$doctor ||
        $doctor->role?->name !== 'doctor' ||
        $doctor->status !== 'approved'
    ) {
        return response()->json([
            'message' => 'The selected doctor is not available.'
        ], 422);
    }

    $date = Carbon::parse($request->appointment_date);
    $time = Carbon::createFromFormat(
        'H:i',
        $request->appointment_time
    );

    /*
     * Don't allow a time in the past.
     */
    $appointmentDateTime = Carbon::parse(
        $request->appointment_date . ' ' .
        $request->appointment_time
    );

    if ($appointmentDateTime->lte(Carbon::now())) {
        return response()->json([
            'message' => 'You cannot reserve an appointment in the past.'
        ], 422);
    }

    /*
     * Check doctor's exception.
     */
    $exception = DoctorException::where('doctor_id', $doctor->id)
        ->whereDate('exception_date', $date)
        ->first();

    /*
     * Doctor is completely unavailable.
     */
    if ($exception && $exception->is_unavailable) {
        return response()->json([
            'message' => 'The doctor is unavailable on this date.'
        ], 422);
    }

    /*
     * Find doctor's normal schedule.
     *
     * Your days_of_week is:
     *
     * 1 = Monday
     * 2 = Tuesday
     * ...
     * 7 = Sunday
     */
    $dayOfWeek = $date->dayOfWeekIso;

    $schedule = DoctorSchedule::where('doctor_id', $doctor->id)
        ->where('days_of_week', $dayOfWeek)
        ->where('is_available', true)
        ->first();

    /*
     * Determine working hours.
     *
     * DoctorException with is_unavailable = false
     * overrides the normal schedule.
     */
    if ($exception && !$exception->is_unavailable) {

        $startTime = $exception->start_time;
        $endTime = $exception->end_time;

    } elseif ($schedule) {

        $startTime = $schedule->start_time;
        $endTime = $schedule->end_time;

    } else {

        return response()->json([
            'message' => 'The doctor does not work on this day.'
        ], 422);
    }

    /*
     * Check that requested time is inside
     * the doctor's working hours.
     */
    $requestedTime = Carbon::createFromFormat(
        'H:i',
        $request->appointment_time
    );

    $workingStart = Carbon::createFromFormat(
        'H:i',
        Carbon::parse($startTime)->format('H:i')
    );

    $workingEnd = Carbon::createFromFormat(
        'H:i',
        Carbon::parse($endTime)->format('H:i')
    );

    if (
        $requestedTime->lt($workingStart) ||
        $requestedTime->gte($workingEnd)
    ) {
        return response()->json([
            'message' => 'The selected time is outside the doctor working hours.'
        ], 422);
    }

    /*
     * Appointment times must be every 15 minutes.
     *
     * Example:
     * 08:00
     * 08:15
     * 08:30
     * 08:45
     */
    $minutesFromStart = $workingStart->diffInMinutes(
        $requestedTime
    );

    if ($minutesFromStart % 15 !== 0) {
        return response()->json([
            'message' => 'Appointments must be booked in 15-minute intervals.'
        ], 422);
    }

    /*
     * Check if this slot is already reserved.
     *
     * Cancelled and rejected appointments do not block
     * the slot.
     */
    $alreadyBooked = Appointment::where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', $date)
        ->whereTime('appointment_time', $requestedTime->format('H:i:s'))
        ->whereNotIn('status', [
            'cancelled',
            'rejected',
        ])
        ->exists();

    if ($alreadyBooked) {
        return response()->json([
            'message' => 'This appointment time is already reserved.'
        ], 409);
    }

    /*
     * Create appointment.
     */
    $appointment = Appointment::create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date->format('d-m-Y'),
        'appointment_time' => $requestedTime->format('H:i:s'),
        'status' => 'pending',
        'notes' => $request->notes,
    ]);

    return response()->json([
        'message' => 'Appointment reserved successfully and is waiting for doctor approval.',
        'data' => new \App\Http\Resources\AppointmentResource(
            $appointment->load(['doctor', 'patient'])
        ),
    ], 201);
}

public function approve(Appointment $appointment)
{
    $doctor = Auth::user();

    // Only doctors can approve
    if ($doctor->role?->name !== 'doctor') {
        return response()->json([
            'message' => 'Only doctors can approve appointments.'
        ], 403);
    }

    // Doctor can only approve his own appointments
    if ($appointment->doctor_id !== $doctor->id) {
        return response()->json([
            'message' => 'You can only approve your own appointments.'
        ], 403);
    }

    // Only pending appointments can be approved
    if ($appointment->status !== 'pending') {
        return response()->json([
            'message' => 'Only pending appointments can be approved.'
        ], 422);
    }

    $appointment->update([
        'status' => 'approved',
    ]);

    return response()->json([
        'message' => 'Appointment approved successfully.',
        'data' => new \App\Http\Resources\AppointmentResource(
            $appointment->load(['doctor', 'patient'])
        ),
    ]);
}
public function reject(Appointment $appointment)
{
    $doctor = Auth::user();

    // Only doctors can reject
    if ($doctor->role?->name !== 'doctor') {
        return response()->json([
            'message' => 'Only doctors can reject appointments.'
        ], 403);
    }

    // Doctor can only reject his own appointments
    if ($appointment->doctor_id !== $doctor->id) {
        return response()->json([
            'message' => 'You can only reject your own appointments.'
        ], 403);
    }

    // Only pending appointments can be rejected
    if ($appointment->status !== 'pending') {
        return response()->json([
            'message' => 'Only pending appointments can be rejected.'
        ], 422);
    }

    $appointment->update([
        'status' => 'rejected',
    ]);

    return response()->json([
        'message' => 'Appointment rejected successfully.',
        'data' => new \App\Http\Resources\AppointmentResource(
            $appointment->load(['doctor', 'patient'])
        ),
    ]);
}
public function cancel(Appointment $appointment)
{
    $patient = Auth::user();

    // Only patients can cancel
    if ($patient->role?->name !== 'patient') {
        return response()->json([
            'message' => 'Only patients can cancel appointments.'
        ], 403);
    }

    // Patient can only cancel his own appointment
    if ($appointment->patient_id !== $patient->id) {
        return response()->json([
            'message' => 'You can only cancel your own appointments.'
        ], 403);
    }

    // Cannot cancel already cancelled appointment
    if ($appointment->status === 'cancelled') {
        return response()->json([
            'message' => 'This appointment is already cancelled.'
        ], 422);
    }

    // Cannot cancel rejected appointment
    if ($appointment->status === 'rejected') {
        return response()->json([
            'message' => 'This appointment has already been rejected.'
        ], 422);
    }

    // Cannot cancel completed appointment
    if ($appointment->status === 'completed') {
        return response()->json([
            'message' => 'A completed appointment cannot be cancelled.'
        ], 422);
    }

    $appointment->update([
        'status' => 'cancelled',
    ]);

    return response()->json([
        'message' => 'Appointment cancelled successfully.',
        'data' => new \App\Http\Resources\AppointmentResource(
            $appointment->load(['doctor', 'patient'])
        ),
    ]);
}
}