<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        // Handle token expiration or invalid token (unauthenticated users)
        if ($exception instanceof AuthenticationException && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Your token has expired or is invalid.',
            ], 401);
        }


        // Check if the exception is a NotFoundHttpException
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found'
            ], 404);
        }

        // Optional: Handle other types of exceptions similarly
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
        }

        if($exception instanceof UserApiException) {
            $data = [
//                'type' => 'api',
                'message' => $exception->getMessage(),
            ];

            $status = $exception->getCode();
            return response()->json($data, $status);
        }
        return parent::render($request, $exception);
    }
}
