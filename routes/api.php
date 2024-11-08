<?php

use App\Http\Controllers\api\v1\BodyTypeController;
use App\Http\Controllers\api\v1\UserClothingController;
use App\Http\Middleware\checkApiKeyMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\UserController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/


Route::prefix('v1/user')->middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->withoutMiddleware('auth:sanctum')->group(function () {
        Route::post("/otp/send", [AuthController::class, "sendOtp"]);
        Route::post("/otp/confirm", [AuthController::class, "otpConfirm"]);
        Route::post("/register", [AuthController::class, "register"])->middleware(checkApiKeyMiddleware::class);
    });


    Route::prefix('body_type')->group(callback: function () {
        Route::get("/all", [BodyTypeController::class, "index"]);
        Route::get("/details", [UserController::class, "getBodyTypeDetail"]);
        Route::post("/upload/image", [UserController::class, "uploadBodyImage"]);
    });

    Route::prefix('clothes')->group(callback: function () {
        Route::get("/", [UserClothingController::class, "index"]);
        Route::post("/upload/image", [UserClothingController::class, "uploadClothingImage"]);
    });

    Route::post("/update/profile", [UserController::class, "updateUser"]);

});

