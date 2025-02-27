<?php

namespace App\Http\Middleware;

use App\Helpers\Response\ResponseHelper;
use App\Models\Shop;
use App\Services\AuthenticationsService;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class checkShopAccessMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken() ?? '';
        $secretKey = env('JWT_SECRET_KEY');

        try {
            $verifiedToken = JWT::decode($token, new Key($secretKey, 'HS256'));
        } catch (Exception) {
            return ResponseHelper::responseCustomError(__('exceptions.exceptionErrors.accessDenied'));
        }

        $shop = Shop::query()->find($verifiedToken->shop_id)->first() ?? null;
        if ($shop === null) {
            return ResponseHelper::responseCustomError(__('exceptions.exceptionErrors.accessDenied'));
        }

        Auth::setUser(new GenericUser($shop->toArray()));

        return $next($request);
    }
}
