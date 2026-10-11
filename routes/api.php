<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\LocationController;
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
Route::post('/webhooks/midtrans', [WebhookController::class, 'midtrans']);
Route::post('/webhooks/biteship', [WebhookController::class, 'biteship']);


Route::get('/provinces',      [LocationController::class, 'provinces']);
Route::get('/cities',         [LocationController::class, 'cities']);
Route::get('/districts',      [LocationController::class, 'districts']);
Route::get('/postal-codes',   [LocationController::class, 'postalCodes']);
