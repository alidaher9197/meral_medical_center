<?php

namespace App\Http\Controllers;

use App\Models\DoctorSpecialty;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DoctorSpecialtyController extends Controller
{
    /**
     * Get the specialties of the authenticated doctor.
     */
    public function index(Request $request)
    {
        $doctor = $request->user();

        if ($doctor->role?->name !== 'doctor' && $doctor->role?->name !== 'head_admin') {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $specialties = DoctorSpecialty::with('specialty')
            ->where('doctor_id', $doctor->id)
            ->get();

        return response()->json([
            'specialties' => $specialties
        ], 200);
    }

    /**
     * Get all available specialties.
     */
    public function availableSpecialties()
    {
        return response()->json([
            'specialties' => Specialty::orderBy('name')->get()
        ], 200);
    }

    /**
     * Update a doctor's specialty.
     */
    public function update(Request $request, DoctorSpecialty $doctorSpecialty)
    {
        $user = $request->user();

        // Doctor can only modify his own specialty.
        if (
            $user->id !== $doctorSpecialty->doctor_id &&
            $user->role?->name !== 'head_admin'
        ) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $validated = $request->validate([
            'specialty_id' => [
                'required',
                'integer',
                'exists:specialties,id',
            ],

            'license_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        // Prevent duplicate specialty for the same doctor.
        $exists = DoctorSpecialty::where('doctor_id', $doctorSpecialty->doctor_id)
            ->where('specialty_id', $validated['specialty_id'])
            ->where('id', '!=', $doctorSpecialty->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This doctor already has this specialty.'
            ], 422);
        }

        $doctorSpecialty->specialty_id = $validated['specialty_id'];

        if ($request->hasFile('license_image')) {

            if ($doctorSpecialty->license_image) {
                Storage::disk('local')->delete($doctorSpecialty->license_image);
            }

            $doctorSpecialty->license_image =
                $request->file('license_image')
                    ->store('doctor-certificates', 'local');
        }

        $doctorSpecialty->save();

        $doctorSpecialty->load('specialty');

        return response()->json([
            'message' => 'Specialty updated successfully.',
            'specialty' => $doctorSpecialty,
        ], 200);
    }

    /**
     * Delete a doctor's specialty.
     */
    public function destroy(Request $request, DoctorSpecialty $doctorSpecialty)
    {
        $user = $request->user();

        if (
            $user->id !== $doctorSpecialty->doctor_id &&
            $user->role?->name !== 'head_admin'
        ) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        if ($doctorSpecialty->license_image) {
            Storage::disk('local')->delete($doctorSpecialty->license_image);
        }

        $doctorSpecialty->delete();

        return response()->json([
            'message' => 'Specialty deleted successfully.'
        ], 200);
    }
}