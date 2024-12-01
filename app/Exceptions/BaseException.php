<?php

namespace App\Exceptions;

use App\Helpers\Response\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;
use function response;

class BaseException extends Exception
{

    #[Pure] public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    #[ArrayShape(['status_code' => "mixed", 'message' => "mixed", 'errors' => "mixed", 'extra_data' => "mixed"])]
    public static function extract_data_exception(Throwable $exception): array
    {
        $status_code = self::getCodePriority($exception);
        $message = __('custom.defaults.exceptions.500');
        $errors = self::getErrorsPriority($exception);
        return [
            'status_code' => $status_code,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    public function response(Request $request, $exception = null): Response|JsonResponse
    {
        $extract_data_exception = self::extract_data_exception($this);

        /**
         * @var int $status_code
         * @var string $message
         * @var array $errors
         */
        extract($extract_data_exception);
        if ($request->wantsJson() || $request->ajax()) {
            return $this->responseJson(status_code: $status_code, message: $message, errors: $errors);
        }
        return $this->responseWebPage(status_code: $status_code, message: $message, errors: $errors);
    }

    public static function responseJson($status_code = HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $message = '', $errors = []): JsonResponse
    {
        return ResponseHelper::responseError($errors, $message, $status_code);
    }

    public static function responseWebPage($status_code = HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $message = '', $errors = []): Response
    {
        return response()->view('errors.custom', [
            'code' => $status_code,
            'message' => $message . " - " . $status_code,
            'errors' => $errors,
        ]);
    }


    public static function getCodePriority($exception = null): int
    {
        $status_code = method_exists($exception, 'getCode') ? $exception->getCode() : 0;
        $status_code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : $status_code;
        $status_code = $status_code ?? HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR;
        return filled($status_code) && $status_code ? (int)$status_code : HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR;
    }

    public static function getErrorsPriority($exception = null): array
    {
        return method_exists($exception, 'errors') ? $exception->errors() : [];
    }
}
