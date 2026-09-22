<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\TechnicalSheetController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => $user->id,
        'first_name' => $user->first_name,
        'middle_name' => $user->middle_name,
        'last_name' => $user->last_name,
        'second_last_name' => $user->second_last_name,
        'email' => $user->email,
        'phone' => $user->phone,
        'employee_number' => $user->employee_number,
        'user_type' => $user->user_type,
        'position' => $user->position,
        'url' => $user->url,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/technical-sheets/development', [TechnicalSheetController::class, 'inDevelopment']);
    Route::get('/technical-sheets/completed', [TechnicalSheetController::class, 'completed']);
    Route::resource('/technical-sheets', TechnicalSheetController::class);

    Route::resource('/categories', CategoryController::class);

    Route::resource('/subcategories', SubcategoryController::class);

    Route::resource('/segments', SegmentController::class);

    Route::resource('/models', ModelController::class);

    Route::resource('/users', UserController::class);

    Route::resource('/logs', LogsController::class);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login/{employee_number}', [AuthController::class, 'logincollaborator']);
Route::post('/login', [AuthController::class, 'login']);

