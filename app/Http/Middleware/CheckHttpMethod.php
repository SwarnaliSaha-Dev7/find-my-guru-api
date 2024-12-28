<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class CheckHttpMethod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $response = $next($request);

    //     if ($response->status() === 405) {
    //         throw new MethodNotAllowedHttpException(
    //             $request->route()->methods(),
    //             'HTTP method not allowed.'
    //         );
    //     }

    //     return $response;
    // }
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'HTTP method not allowed. Please use one of the supported methods.',
                'allowed_methods' => $exception->getHeaders()['Allow'] ?? []
            ], 405);
        };

        //return parent::render($request, $exception);
    }
}
