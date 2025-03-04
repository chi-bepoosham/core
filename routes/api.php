<?php

use App\Http\Controllers\api\v1\BodyTypeController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ShopController;
use App\Http\Controllers\api\v1\UserClothingController;
use App\Http\Middleware\checkAdminAccessMiddleware;
use App\Http\Middleware\checkApiKeyMiddleware;
use App\Http\Middleware\checkShopAccessMiddleware;
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


Route::prefix('v1')->group(function () {


    Route::prefix('user')->middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->withoutMiddleware('auth:sanctum')->group(function () {
            Route::post("/otp/send", [AuthController::class, "sendOtp"]);
            Route::post("/otp/confirm", [AuthController::class, "otpConfirm"]);
            Route::post("/register", [AuthController::class, "register"])->middleware(checkApiKeyMiddleware::class);

//        Google Authentication
            Route::get("/google/redirect", [AuthController::class, "redirectToGoogleOAuth"])->middleware('web');
            Route::get("/google/callback", [AuthController::class, "callbackGoogleOAuth"])->middleware('web');
        });


        Route::prefix('body_type')->group(function () {
            Route::get("/all", [BodyTypeController::class, "index"]);
            Route::get("/details", [UserController::class, "getBodyTypeDetail"]);
            Route::post("/upload/image", [UserController::class, "uploadBodyImage"]);
        });

        Route::prefix('clothes')->group(function () {
            Route::get("/", [UserClothingController::class, "index"]);
            Route::post("/upload/image", [UserClothingController::class, "uploadClothingImage"]);
            Route::delete("/{clothesId}", [UserClothingController::class, "delete"]);
        });

        Route::prefix('/shop')->group(function () {

            Route::prefix('/product')->group(function () {
                Route::get("/all", [ProductController::class, "indexUsers"]);
                Route::get("/{productId}", [ProductController::class, "show"]);
            });

        });

        Route::get("/splash", [UserController::class, "splash"]);
        Route::post("/update/profile", [UserController::class, "updateUser"]);

    });


//  -----------------  Shop Section  -----------------

    Route::prefix('shop')->middleware(checkShopAccessMiddleware::class)->group(function () {

        Route::prefix('auth')->withoutMiddleware(checkShopAccessMiddleware::class)->group(function () {
            Route::post("/register", [AuthController::class, "shopRegister"]);
            Route::post("/login", [AuthController::class, "shopLogin"]);
        });

        Route::prefix('/')->group(function () {
            Route::get("/{shopId}", [ShopController::class, "show"]);
            Route::post("/update/{shopId}", [ShopController::class, "update"]);
        });

        Route::prefix('/product')->group(function () {
            Route::get("/all", [ProductController::class, "index"]);
            Route::get("/{productId}", [ProductController::class, "show"]);
            Route::post("/", [ProductController::class, "create"]);
            Route::post("/update/{productId}", [ProductController::class, "update"]);
            Route::delete("/{productId}", [ProductController::class, "delete"]);
        });

    });


//  -----------------  Admin Section  -----------------

    Route::prefix('admin')->middleware(checkAdminAccessMiddleware::class)->group(function () {

        Route::prefix('auth')->withoutMiddleware(checkAdminAccessMiddleware::class)->group(function () {
            Route::post("/login", [AuthController::class, "adminLogin"]);
        });

        Route::prefix('/shop')->group(function () {
            Route::get("/all", [ShopController::class, "index"]);
            Route::get("/{shopId}", [ShopController::class, "show"]);
            Route::post("/update/{shopId}", [ShopController::class, "update"]);
            Route::delete("/{shopId}", [ShopController::class, "delete"]);
        });

    });
});

