<?php

use App\Http\Controllers\api\v1\BodyTypeController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ShopController;
use App\Http\Controllers\api\v1\UserAddressController;
use App\Http\Controllers\api\v1\UserClothingController;
use App\Http\Controllers\api\v1\UserMarkedProductController;
use App\Http\Controllers\api\v1\WalletController;
use App\Http\Controllers\api\v1\WalletTransactionController;
use App\Http\Middleware\checkAdminAccessMiddleware;
use App\Http\Middleware\checkApiKeyMiddleware;
use App\Http\Middleware\checkShopAccessMiddleware;
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
            Route::get("/all", [UserClothingController::class, "index"]);
            Route::post("/upload/image", [UserClothingController::class, "uploadClothingImage"]);
            Route::delete("/{clothesId}", [UserClothingController::class, "delete"]);
        });

        Route::prefix('/address')->group(function () {
            Route::get("/all", [UserAddressController::class, "index"]);
            Route::get("/{addressId}", [UserAddressController::class, "show"]);
            Route::post("/", [UserAddressController::class, "create"]);
            Route::post("/update/{addressId}", [UserAddressController::class, "update"]);
            Route::delete("/{addressId}", [UserAddressController::class, "delete"]);
        });

        Route::prefix('/shop')->group(function () {

            Route::get("/search/all", [ProductController::class, "searchAll"]);

            Route::prefix('/product')->group(function () {
                Route::get("/all", [ProductController::class, "indexUsers"]);
                Route::get("/{productId}", [ProductController::class, "show"]);

                Route::prefix('/marked')->group(function () {
                    Route::get("/all", [UserMarkedProductController::class, "index"]);
                    Route::patch("/{productId}/{markedStatus}", [UserMarkedProductController::class, "changeMarkedStatus"]);
                });
            });

            Route::prefix('/orders')->group(function () {
                Route::get("/all", [OrderController::class, "index"]);
                Route::get("/{orderId}", [OrderController::class, "show"]);
                Route::post("/register", [OrderController::class, "register"]);
                Route::put("/{orderId}", [OrderController::class, "update"]);
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
            Route::get("/splash", [ShopController::class, "splash"]);
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

        Route::prefix('/orders')->group(function () {
            Route::get("/all", [OrderController::class, "index"]);
            Route::get("/{orderId}", [OrderController::class, "show"]);
            Route::put("/{orderId}", [OrderController::class, "update"]);
        });

        Route::prefix('/wallet')->group(function () {
            Route::get("/{shopId}", [WalletController::class, "show"]);

            Route::prefix('/transaction')->group(function () {
                Route::get("/all", [WalletTransactionController::class, "index"]);
                Route::get("/{walletTransactionId}", [WalletTransactionController::class, "show"]);
            });
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

        Route::prefix('/product')->group(function () {
            Route::get("/all", [ProductController::class, "index"]);
            Route::get("/{productId}", [ProductController::class, "show"]);
            Route::post("/", [ProductController::class, "create"]);
            Route::post("/update/{productId}", [ProductController::class, "update"]);
            Route::delete("/{productId}", [ProductController::class, "delete"]);
        });

        Route::prefix('/orders')->group(function () {
            Route::get("/all", [OrderController::class, "index"]);
            Route::get("/{orderId}", [OrderController::class, "show"]);
            Route::post("/register", [OrderController::class, "register"]);
            Route::put("/{orderId}", [OrderController::class, "update"]);
            Route::delete("/{orderId}", [OrderController::class, "delete"]);
        });

        Route::prefix('/wallet')->group(function () {
            Route::get("/all", [WalletController::class, "index"]);
            Route::get("/{shopId}", [WalletController::class, "show"]);
            Route::delete("/{shopId}", [WalletController::class, "delete"]);

            Route::prefix('/transaction')->group(function () {
                Route::get("/all", [WalletTransactionController::class, "index"]);
                Route::post("/", [WalletTransactionController::class, "create"]);
                Route::get("/{walletTransactionId}", [WalletTransactionController::class, "show"]);
                Route::put("/{walletTransactionId}", [WalletTransactionController::class, "update"]);
                Route::delete("/{walletTransactionId}", [WalletTransactionController::class, "delete"]);
            });
        });

    });
});

