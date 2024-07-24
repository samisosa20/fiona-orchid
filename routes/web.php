<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\VerificationController;

use Illuminate\Http\Request;

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

Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    return redirect()->away(env('URL_FRONT') . '/login' . "?i={$request->id}&h={$request->hash}&e={$request->expires}&s={$request->signature}");
})->name('verification.verify');

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice');
});
