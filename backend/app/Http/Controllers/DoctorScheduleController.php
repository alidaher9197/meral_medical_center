<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Http\Resources\DoctorScheduleResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DoctorScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of doctor schedules.
     */
    public function index()
    {
        $this->authorize('viewAny', DoctorSchedule::class);

        $schedules = DoctorSchedule::with('doctor')->get();

        return DoctorScheduleResource::collection($schedules);
    }

    /**
     * Store a newly created doctor schedule.
     */
    public function store(StoreDoctorScheduleRequest $request)
{
    $this->authorize('create', DoctorSchedule::class);

    $data = $request->validated();

    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Doctor can only create a schedule for himself
    |--------------------------------------------------------------------------
    */

    if ($user->role->name === 'doctor') {

        if ((int) $data['doctor_id'] !== (int) $user->id) {
            return response()->json([
                'message' => 'You can only create a schedule for yourself.'
            ], 403);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create schedule
    |--------------------------------------------------------------------------
    */

    $schedule = DoctorSchedule::create($data);

    // Get the actual saved data from database
    $schedule->refresh();

    // Load doctor relationship
    $schedule->load('doctor');

    return new DoctorScheduleResource($schedule);
}

    /**
     * Display the specified doctor schedule.
     */
    public function show(DoctorSchedule $doctorSchedule)
    {
        $this->authorize('view', $doctorSchedule);

        $doctorSchedule->load('doctor');

        return new DoctorScheduleResource($doctorSchedule);
    }

    /**
     * Update the specified doctor schedule.
     */
    public function update(UpdateDoctorScheduleRequest $request,DoctorSchedule $doctorSchedule)
     {
        $this->authorize('update', $doctorSchedule);

    $data = $request->validated();

    $user = $request->user();

    // Doctor can only update his own schedule
    if ($user->role->name === 'doctor') {

        // If doctor_id was sent, make sure it belongs to the logged-in doctor
        if (
            isset($data['doctor_id']) &&
            (int) $data['doctor_id'] !== (int) $user->id
        ) {
            return response()->json([
                'message' => 'You can only update a schedule that belongs to you.'
            ], 403);
        }

        // Also make sure the schedule itself belongs to this doctor
        if ((int) $doctorSchedule->doctor_id !== (int) $user->id) {
            return response()->json([
                'message' => 'You can only update your own schedule.'
            ], 403);
        }
    }

    $doctorSchedule->update($data);

    // Get the actual data saved in the database
    $doctorSchedule->refresh();
    $doctorSchedule->load('doctor');

    return new DoctorScheduleResource($doctorSchedule);
}

    /**
     * Remove the specified doctor schedule.
     */
    public function destroy(DoctorSchedule $doctorSchedule)
    {
        $this->authorize('delete', $doctorSchedule);

        $doctorSchedule->delete();

        return response()->json([
            'message' => 'Doctor schedule deleted successfully.'
        ]);
    }
}