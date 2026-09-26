<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Throwable;



class AuthenticationController extends Controller
{
    public function index(Request $request)
{
    Gate::authorize('viewAny', User::class);

    $users = User::with('role')->get();

    return response()->json([
        'message' => 'Users retrieved successfully.',
        'users' => UserResource::collection($users),
    ], 200);
}
    
    public function register(CreateUserRequest $request)
    {
        DB::beginTransaction();

        try {

            // 1. Store profile image
            $profileImagePath = null;

            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')
                    ->store('profile-images', 'public');
            }
            $status = ((int) $request->role_id === 4)
                ? 'approved'
                : 'pending';
            // 2. Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'profile_image' => $profileImagePath,
                'status' => $status,
            ]);


            // 3. If the user is a doctor
            if ((int) $request->role_id === 3) {

                foreach ($request->specialties as $specialty) {

                    // Store certificate image
                    $certificateImagePath = $specialty['image']
                        ->store('doctor-certificates', 'local');


                    // Insert doctor + specialty
                    DB::table('doctor_specialties')->insert([
                        'doctor_id' => $user->id,
                        'specialty_id' => $specialty['id'],
                        'license_image' => $certificateImagePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }


            // 4. Everything succeeded
            DB::commit();


            // 5. Return response
            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user,
            ], 201);


        } catch (\Throwable $e) {

            // Undo database changes
            DB::rollBack();


            return response()->json([
                'message' => 'Failed to create user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {

            $user = User::with("role")->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid email or password.'
                ], 401);
            }
            if ($user->status === 'pending') {
            Auth::logout();

            return response()->json([
                'message' => 'Your account is waiting for admin approval.'
            ], 403);
        }

        if ($user->status === 'blocked') {
            Auth::logout();

            return response()->json([
                'message' => 'Your account has been blocked.'
            ], 403);
        }
        if ($user->status === 'rejected') {
            Auth::logout();

            return response()->json([
                'message' => 'Your account has been rejected.'
            ], 403);
        }
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => new UserResource($user)
            ], 200);

        } catch (QueryException $e) {

            // Save the real database error in Laravel's log
            Log::error('Database error during login', [
                'error' => $e->getMessage()
            ]);

            // Send a safe message to JavaScript
            return response()->json([
                'message' => 'Database error. Please try again later.'
            ], 500);

        } catch (Throwable $e) {

            Log::error('Login error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message'=>'Logged out'
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
        {
            
            Gate::authorize('update', $user);
            try {

                // Get only the fields allowed by UpdateUserRequest
                $data = $request->validated();

                // Handle profile image
                if ($request->hasFile('profile_image')) {

                    // Delete old profile image if it exists
                    if ($user->profile_image) {
                        Storage::disk('public')->delete($user->profile_image);
                    }

                    // Store new profile image
                    $data['profile_image'] = $request->file('profile_image')
                        ->store('profile-images', 'public');
                }

                // Update user
                $user->update($data);

                // Load role for UserResource
                $user->load('role');

                return response()->json([
                    'message' => 'User updated successfully.',
                'user' => new UserResource($user),
                ], 200);

            } catch (\Throwable $e) {

                Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update user.',
            ], 500);
            }
    }
    public function show(User $user)
    {
        Gate::authorize('view', $user);

        // Load role for UserResource
        $user->load('role');

        return response()->json([
            'message' => 'User retrieved successfully.',
            'user' => new UserResource($user),
        ], 200);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user)
    {
        Gate::authorize('updatePassword', $user);

        try {
            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password updated successfully.',
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'message' => 'Failed to update password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showPatients(){
        Gate::authorize('showPatients', User::class);
        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'patient');
        })->get();
        return response()->json([
            'message' => 'Patients retrieved successfully.',
            'patients' => UserResource::collection($users),
        ], 200);
    }
    public function showAdmins()
{
    Gate::authorize('showAdmins', User::class);

    $users = User::with('role')
        ->whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })
        ->get();

    return response()->json([
        'message' => 'Admins retrieved successfully.',
        'admins' => UserResource::collection($users),
    ], 200);
}
    public function showDoctors(Request $request)
{
Gate::authorize('showDoctors', User::class);


$user = $request->user()->load('role');

$query = User::with([
    'role',
    'doctorSpecialties.specialty'
])
->whereHas('role', function ($query) {
    $query->where('name', 'doctor');
});

if (!in_array($user->role->name, ['admin', 'head_admin'])) {
    $query->where('status', 'approved');
}

$doctors = $query->get();

return response()->json([
    'message' => 'Doctors retrieved successfully.',
    'doctors' => UserResource::collection($doctors),
], 200);


}


    public function changeStatus(UpdateStatusRequest $request, User $user){
        Gate::authorize('updateStatus', $user);
        try {

                // Get only the fields allowed by UpdateUserRequest
                $data = $request->validated();

                // Update user
                $user->update($data);

                // Load role for UserResource
                $user->load('role');

                return response()->json([
                    'message' => 'User status updated successfully.',
                'user' => new UserResource($user),
                ], 200);

            } catch (\Throwable $e) {

                return response()->json([
                    'message' => 'Failed to update status user.',
                    'error' => $e->getMessage(),
                ], 500);
            }
    }

}
