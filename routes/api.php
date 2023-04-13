<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\MovementController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\HeritageController;
use App\Http\Controllers\Api\V1\BudgetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['jwt', 'verified'])->prefix('v1')->group(function () {
    Route::apiResource('/users', UserController::class);
    
    Route::apiResource('/accounts', AccountController::class);
    Route::post('/accounts/{id}/restore', [AccountController::class, 'restore']);
    Route::get('/accounts/{id}/movements', [AccountController::class, 'movements']);
    
    Route::apiResource('/categories', CategoryController::class);
    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore']);
    
    Route::apiResource('/events', EventController::class);
    Route::get('/active/events', [EventController::class, 'active']);
    
    Route::apiResource('/movements', MovementController::class);

    Route::apiResource('/payments', PaymentController::class);
    
    Route::apiResource('/heritages', HeritageController::class);
    
    Route::apiResource('/budgets', BudgetController::class);

    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
