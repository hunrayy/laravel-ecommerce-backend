<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Log;

use Exception;

class TokenController extends Controller
{
    //
    public function isTokenActive(Request $request){
        $authorization = $request->header("Authorization");
        if(!$authorization){
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Authorization header not found'
            ]);
        }
        try{
            //split the bearer token
            $bearerToken = explode(' ', $authorization)[1];
            //verify the token
            $decodedToken = JWT::decode($bearerToken, new Key(env("JWT_SECRET"), 'HS256'));
            return response()->json([
                'code'=> "success",
                'message'=> 'token is still active'
            ]);
        }catch(ExpiredException $e){
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Token has expired'
            ]);
        }catch (Exception $e) {
            Log::error('JWT error: ' . $e->getMessage()); // Logs the error to storage/logs/laravel.log
            return response()->json([
                'code' => 'invalid-jwt',
                'message' => 'Invalid JWT or other error: ' . $e->getMessage()
            ]);
        }
    }
}
