<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Str;


class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = $request->header('Authorization');
        // Check if the token exists
        //echo($apiToken);
        if (!$apiToken) {
            // Return a 401 response if no token is provided
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. No token provided or token has expired',
            ], 401);
        }
        // Remove 'Bearer' if present in the Authorization header
        if (Str::startsWith($apiToken, 'Bearer ')) {
            $apiToken = Str::replaceFirst('Bearer ', '', $apiToken);
        }

        // Check if the token exists
        if (!$apiToken) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. No token provided.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Use Sanctum to retrieve the token
        // $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($apiToken); // Check if the token has expired
        // if ($accessToken->expires_at && \Carbon\Carbon::now()->greaterThan($accessToken->expires_at)) {
        //     // Token has expired
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unauthorized. Token has expired.',
        //     ], JsonResponse::HTTP_UNAUTHORIZED);
        // }


        // Use Sanctum to retrieve the user from the token
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Invalid token.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Set the authenticated user in the request for later use
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
