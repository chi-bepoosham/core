<?php

use App\Exceptions\BaseException;
use App\Helpers\Response\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (!function_exists('exception_response_exception')) {
    function exception_response_exception(
        Request   $request,
        Throwable $exception = null,
                  $status_code = Response::HTTP_INTERNAL_SERVER_ERROR,
                  $message = '',
                  $errors = [],
                  $extra_data = []
    ): \Illuminate\Http\Response|JsonResponse
    {
        if ($exception != null) {

            $extract_data_exception = BaseException::extract_data_exception($exception);
            /**
             * @var int $status_code
             * @var string $message
             * @var array $errors
             */
            extract($extract_data_exception);
        }
        $message = $exception->getMessage();

        if ($request->wantsJson()) {

            if ($exception instanceof AuthenticationException) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_UNAUTHORIZED);
            }

            if ($exception instanceof ValidationException) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($exception instanceof AccessDeniedHttpException) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_FORBIDDEN);
            }

            if ($exception instanceof NotFoundHttpException) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_NOT_FOUND);
            }

            if ($exception instanceof BadRequestHttpException ) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_BAD_REQUEST);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_METHOD_NOT_ALLOWED);
            }

            if ($exception instanceof ThrottleRequestsException) {
                $message = "لحظاتی بعد تلاش کنید";
                return ResponseHelper::responseError($errors, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($exception instanceof Exception) {
                return ResponseHelper::responseError($errors, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return ResponseHelper::responseError($errors, $message, $status_code);

        }
        return BaseException::responseWebPage($status_code, $message, $errors);
    }
}
