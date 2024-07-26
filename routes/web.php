<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice');
});
