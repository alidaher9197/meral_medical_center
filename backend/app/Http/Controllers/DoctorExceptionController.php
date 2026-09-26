<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorExceptionRequest;
use App\Http\Requests\UpdateDoctorExceptionRequest;
use App\Http\Resources\DoctorExceptionResource;
use App\Models\DoctorException;
use Illuminate\Support\Facades\Auth;

class DoctorExceptionController extends Controller
{
    /**
     * Display a listing of the doctor exceptions.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role?->name === 'doctor') {
            $exceptions = DoctorException::with('doctor')
                ->where('doctor_id', $user->id)
                ->orderBy('exception_date')
                ->get();
        } else {
            $this->authorize('viewAny', DoctorException::class);

            $exceptions = DoctorException::with('doctor')
                ->orderBy('exception_date')
                ->get();
        }

        return DoctorExceptionResource::collection($exceptions);
    }

    /**
     * Store a newly created doctor exception.
     */
    public function store(StoreDoctorExceptionRequest $request)
    {
        $user = Auth::user();

        /*
        | Doctor creates an exception for himself.
        */
        if ($user->role?->name === 'doctor') {
            $doctorId = $user->id;
        } else {
            /*
            | Admin / Head Admin chooses the doctor.
            */
            $doctorId = $request->doctor_id;
        }

        $this->authorize(
            'create',
            [DoctorException::class, $doctorId]
        );

        $data = $request->validated();

        // Set the doctor ID from the authenticated user/request
        $data['doctor_id'] = $doctorId;

        $exception = DoctorException::create($data);

        return new DoctorExceptionResource(
            $exception->load('doctor')
        );
    }

    /**
     * Display the specified doctor exception.
     */
    public function show(DoctorException $doctorException)
    {
        $this->authorize('view', $doctorException);

        return new DoctorExceptionResource(
            $doctorException->load('doctor')
        );
    }

    /**
     * Update the specified doctor exception.
     */
    public function update(
        UpdateDoctorExceptionRequest $request,
        DoctorException $doctorException
    ) {
        $this->authorize('update', $doctorException);

        $doctorException->update(
            $request->validated()
        );

        return new DoctorExceptionResource(
            $doctorException->load('doctor')
        );
    }

    /**
     * Remove the specified doctor exception.
     */
    public function destroy(DoctorException $doctorException)
    {
        $this->authorize('delete', $doctorException);

        $doctorException->delete();

        return response()->json([
            'message' => 'Doctor exception deleted successfully.'
        ]);
    }
}

