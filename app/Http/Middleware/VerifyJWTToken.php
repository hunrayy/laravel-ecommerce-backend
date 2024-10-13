<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
      public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header("Authorization");

        if (!$authorization) {
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Authorization header not found'
            ]);
        }

        try {
            // Split the bearer token
            $bearerToken = explode(' ', $authorization)[1];

            // Verify the token
            $decodedToken = JWT::decode($bearerToken, new Key(env("JWT_SECRET"), 'HS256'));

            // Attach user email to the request
            $request->merge(['user_email' => $decodedToken->email]);

            // If verification is successful, continue the request
            return $next($request);

        } catch (ExpiredException $e) {
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Token has expired'
            ], 401);

        } catch (Exception $e) {
            Log::error('JWT error: ' . $e->getMessage()); // Log the error for debugging
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Invalid JWT or other error: ' . $e->getMessage()
            ], 401);
        }
    }
}
