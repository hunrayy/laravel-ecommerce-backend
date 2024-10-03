<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT; // for jwt token generation
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException; // For handling token expiration


class VerifyAdminToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get the Authorization header
            $bearerToken = $request->header('Authorization');
            if (!$bearerToken) {
                return response()->json([
                    'message' => 'Authorization token not provided',
                    'code' => 'error',
                ]);
            }

            // Split the token
            $token = explode(' ', $bearerToken)[1];

            // Verify the token
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256')); 

            // Check user role
            if ($payload->user == "admin" && $payload->is_an_admin) {
                return $next($request);
            } else {
                return response()->json([
                    'message' => 'Unauthorized',
                    'code' => 'error',
                ]);
            }

        } catch (ExpiredException $e) {
            return response()->json([
                'message' => 'Invalid JSON Web Token or JWT expired',
                'code' => 'invalid-jwt',
                'reason' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage()); // Log the error message
            return response()->json([
                'message' => 'Invalid JSON Web Token',
                'code' => 'invalid-jwt',
                'reason' => $e->getMessage(),
            ]);
        }
    }
    
}
