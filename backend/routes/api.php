<?php

use App\Http\Controllers\AdminPermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DoctorSpecialtyController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorExceptionController;
use App\Http\Controllers\AppointmentController;
                      
Route::get('/users', [AuthenticationController::class, 'index'])
    ->middleware(['auth:sanctum', 'approved']);

Route::post('/register', [AuthenticationController::class, 'register']);

Route::post('/login', [AuthenticationController::class, 'login']);

Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');



Route::put('/users/{user}', [AuthenticationController::class, 'update'])
 ->middleware(['auth:sanctum', 'approved']);

 Route::put('/users/{user}/status', [AuthenticationController::class, 'changeStatus'])
 ->middleware(['auth:sanctum', 'approved']);

Route::get('/users/{user}', [AuthenticationController::class, 'show'])
 ->middleware(['auth:sanctum', 'approved']);

Route::put('/users/{user}/password', [AuthenticationController::class, 'updatePassword'])
 ->middleware(['auth:sanctum', 'approved']);

Route::middleware('auth:sanctum')->apiResource('admin-permissions',AdminPermissionController::class)
 ->middleware(['auth:sanctum', 'approved']);

Route::get('/patients', [AuthenticationController::class, 'showPatients'])
 ->middleware(['auth:sanctum', 'approved']);

Route::get('/doctors', [AuthenticationController::class, 'showDoctors'])
->middleware(['auth:sanctum', 'approved']);

Route::get('admins',[AuthenticationController::class,"showAdmins"])
->middleware(['auth:sanctum', 'approved']);

Route::get('/permissions', [PermissionController::class, 'index'])
    ->middleware(['auth:sanctum', 'approved']);



Route::middleware(['auth:sanctum', 'approved'])->group(function () {

    Route::get('/doctor-specialties', [
        DoctorSpecialtyController::class,
        'index'
    ]);

    Route::get('/specialties', [
        DoctorSpecialtyController::class,
        'availableSpecialties'
    ]);

    Route::put('/doctor-specialties/{doctorSpecialty}', [
        DoctorSpecialtyController::class,
        'update'
    ]);

    Route::delete('/doctor-specialties/{doctorSpecialty}', [
        DoctorSpecialtyController::class,
        'destroy'
    ]);
});   
Route::apiResource('specialties', SpecialtyController::class)
->middleware(['auth:sanctum', 'approved']); 

Route::apiResource("doctor-schedules",DoctorScheduleController::class)
->middleware(['auth:sanctum', 'approved']);

Route::apiResource("doctor-exceptions",DoctorExceptionController::class)
->middleware(['auth:sanctum', 'approved']);

Route::get('specialties/{specialtyId}/doctors',[SpecialtyController::class, 'doctors'])->middleware(['auth:sanctum', 'approved']);

Route::middleware(['auth:sanctum', 'approved'])->group(function () {

    Route::get(
        'doctors/{doctorId}/available-times',
        [AppointmentController::class, 'availableTimes']
    );

});


Route::middleware(['auth:sanctum', 'approved'])->group(function () {

    // Patient reserves appointment
    Route::post(
        'appointments',
        [AppointmentController::class, 'store']
    );

    // Doctor approves
    Route::patch(
        'appointments/{appointment}/approve',
        [AppointmentController::class, 'approve']
    );

    // Doctor rejects
    Route::patch(
        'appointments/{appointment}/reject',
        [AppointmentController::class, 'reject']
    );

    // Patient cancels
    Route::patch(
        'appointments/{appointment}/cancel',
        [AppointmentController::class, 'cancel']
    );

    // Available times
    Route::get(
        'doctors/{doctorId}/available-times',
        [AppointmentController::class, 'availableTimes']
    );
});
