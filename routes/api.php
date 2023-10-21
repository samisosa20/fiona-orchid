<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\HeritageController;
use App\Http\Controllers\Api\V1\MovementController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\InvestmentController;
use App\Http\Controllers\Api\V1\AppretiationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Private routes
Route::middleware('auth.api')->prefix('v1')->group(function () {
    Route::apiResource('/accounts', AccountController::class);
    Route::post('/accounts/restore/{id}', [AccountController::class, 'restore']);
    Route::delete('/accounts/delete/{id}', [AccountController::class, 'hardDestory']);
    Route::apiResource('/budgets', BudgetController::class);
    Route::get('/budgets-list', [BudgetController::class, 'listYear']);
    Route::get('/budgets-report', [BudgetController::class, 'reportBudget']);
    Route::apiResource('/categories', CategoryController::class);
    Route::get('/categories-list', [CategoryController::class, 'listCategories']);
    Route::get('/events/active',[ EventController::class, 'active']);
    Route::apiResource('/events', EventController::class);
    Route::apiResource('/heritages', HeritageController::class);
    Route::get('/heritages-list', [HeritageController::class, 'listYear']);
    Route::apiResource('/movements', MovementController::class);
    Route::apiResource('/payments', PaymentController::class);
    Route::apiResource('/investments', InvestmentController::class);
    Route::apiResource('/appretiations', AppretiationController::class);
    Route::get('/report', [ReportController::class, 'report']);
    Route::get('/report/category', [ReportController::class, 'movementsByCategory']);
    Route::get('/report/group', [ReportController::class, 'movementsByGroup']);
    Route::apiResource('/profile', UserController::class);
});