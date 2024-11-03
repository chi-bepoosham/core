<?php

namespace App\Http\Middleware;

use App\Helpers\Response\ResponseHelper;
use App\Services\AuthenticationsService;
use Closure;
use Illuminate\Http\Request;

class checkApiKeyMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        # get api-key
        $apiKey = request()->header('api-key');
        $mobile = request()->mobile ?? request("mobile");

        if (isset($apiKey) && filled($apiKey) && AuthenticationsService::checkApiKey($apiKey, $mobile)) {
            return $next($request);
        }

        return ResponseHelper::responseCustomError(__('exceptions.exceptionErrors.ApiKeyDeniedException'));
    }
}
