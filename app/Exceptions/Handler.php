<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        // Check if the request expects a JSON response (for API requests)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. No authentication provided.',
            ], 401);
        }

        // Default redirect behavior for non-API requests
        return redirect()->guest(route('login'));
    }


    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please login.',
            ], 401);
        } 

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => false,
                'message' => 'HTTP method not allowed. Please use one of the supported methods.',
                'allowed_methods' => $exception->getHeaders()['Allow'] ?? []
            ], 405);
        };

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
            ], 404);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ], 429);
        }

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again later.',
        ], 500);

        //return parent::render($request, $exception);
    }
}
