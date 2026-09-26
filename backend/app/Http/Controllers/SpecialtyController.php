<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Http\Resources\SpecialtyResource;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use App\Http\Resources\UserResource;
class SpecialtyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialties = Specialty::all();

        return SpecialtyResource::collection($specialties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialtyRequest $request)
    {
        $this->authorize('create', Specialty::class);

        $specialty = Specialty::create([
            'name' => $request->name,
        ]);

        return new SpecialtyResource($specialty);
    }

    /**
     * Display the specified resource.
     */
    public function show(Specialty $specialty)
    {
        $this->authorize('view', $specialty);

        return new SpecialtyResource($specialty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateSpecialtyRequest $request,
        Specialty $specialty
    ) {
        $this->authorize('update', $specialty);

        $specialty->update([
            'name' => $request->name,
        ]);

        return new SpecialtyResource($specialty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialty $specialty)
    {
        $this->authorize('delete', $specialty);

        $specialty->delete();

        return response()->json([
            'message' => 'Specialty deleted successfully.'
        ]);
    }
    public function doctors($specialtyId)
{
    $doctors = User::whereHas('doctorSpecialties', function ($query) use ($specialtyId) {
        $query->where('specialty_id', $specialtyId);
    })
    ->where('status', 'approved')
    ->whereHas('role', function ($query) {
        $query->where('name', 'doctor');
    })
    ->with([
        'role',
        'doctorSpecialties.specialty',
    ])
    ->get();

    return UserResource::collection($doctors);
}
}