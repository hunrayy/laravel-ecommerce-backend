<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use PHPMailer\PHPMailer\Exception; //for catching errors
use Illuminate\Support\Facades\Validator; //for validating the request coming in
use Illuminate\Support\Facades\Hash; //for password hahsing
use Illuminate\Support\Facades\Log; //for logging error to the terminal
use App\Http\Controllers\AuthController; 
use App\Models\Admin;

class AdminAuthController extends Controller
{
    //
    public function adminLogin(Request $request){
        try{
            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => 'All fields required', 'code' => 'error']);
            }
            $email = $request->email;
            $password = $request->password;

            // Check if the admin exists
            $admin = Admin::where('email', $email)->first();
            if (!$admin) {
                return response()->json(['message' => 'Invalid email/password', 'code' => 'error']);
            }

            // Verify password
            if (!Hash::check($password, $admin->password)) {
                return response()->json(['message' => 'Invalid email/password', 'code' => 'error']);
            }

            // Prepare the payload for JWT
            $payload = [
                'firstname' => $admin->firstname,
                'lastname' => $admin->lastname,
                'email' => $admin->email,
                'user' => $admin->user,
                'is_an_admin' => $admin->is_an_admin,
            ];
            
            $authControllerClass = new AuthController();
            // Create a token (20 days expiration)
            $loginToken = $authControllerClass->createToken($payload, 60 * 60 * 24 * 20); // 20 days in seconds

            // Prepare response data
            return response()->json([
                'message' => 'Login success',
                'code' => 'success',
                'data' => [
                    'firstname' => $admin->firstname,
                    'lastname' => $admin->lastname,
                    'email' => $admin->email,
                    'user' => $admin->user,
                    'is_an_admin' => $admin->is_an_admin,
                    'token' => $loginToken,
                    'countryOfWarehouseLocation' => $admin->countryOfWarehouseLocation,
                    'domesticShippingFeeInNaira' => $admin->domesticShippingFeeInNaira,
                    'internationalShippingFeeInNaira' => $admin->internationalShippingFeeInNaira,
                ],
            ]);


        }catch(\Exception $e){
            // Log::error("An error occured: " . $e->getMessage());
            return response()->json(['code' => 'error', 'message' => 'An error occurred while logging in', 'reason' => $e->getMessage()]);
        }
    }

    public function isAdminTokenActive(Request $request){
        return response()->json([
            'code' => 'success',
            'message' => 'User is authorized... grant access',
        ]);
    }
}
