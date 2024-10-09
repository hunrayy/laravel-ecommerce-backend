<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\AuthController;
use App\Models\User;


class UserPasswordResetController extends Controller
{
    //
    public function sendPasswordResetLink(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);
        try {
            // Check if the user already exists
            $existingUser = User::where('email', $request->email)->first();
            if (!$existingUser) {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Account not found',
                ]);
            }
            //generate JWT token and send
            $authClass = new AuthController();
            $passwordResetToken = $authClass->createToken(['email' => $request->email], 300); // Token expires in 5 minutes

            $resetLink = env('FRONTEND_URL') . '/accounts/password/reset/reset-password/' . $passwordResetToken;

            //use PHPMailer to send the email
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($request->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            padding: 20px;
                        }
                        .email-container {
                            background-color: white;
                            padding: 20px;
                            border-radius: 8px;
                            max-width: 600px;
                            margin: 0 auto;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        h2 {
                            color: #333;
                        }
                        p {
                            color: #666;
                        }
                        .button {
                            display: inline-block;
                            padding: 10px 20px;
                            background-color: #9d4edd;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                            font-weight: bold;
                        }
                        .footer {
                            margin-top: 20px;
                            font-size: 12px;
                            color: #999;
                        }
                    </style>
                </head>
                <body>

                    <div class='email-container'>
                        <h2>Password Reset Request</h2>
                        <p>We received a request to reset the password for your account. If you made this request, please click the button below to reset your password:</p>

                        <a href='$resetLink' class='button'>Reset Password</a>

                        <p>If you did not request a password reset, please ignore this email or contact our support team if you have any concerns.</p>

                    </div>

                </body>
                </html>
            ";

            $mail->send();

            return response()->json([
                "message" => "A link to reset your password has successfully been sent to your email",
                "code" => "success"
            ]);

        }catch(Exception $e){
            return response()->json([
                'message' => 'An error occured while sending password reset link',
                'code' => 'error',
                'reason' => $e->getMessage()
            ]);
        }

    }

    public function resetPassword(Request $request){
        $email = $request->email;
        $newPassword = $request->newPassword;
        $request->validate([
            'email' => 'required|string',
            'newPassword' => 'required|string'
        ]);
        try{
            $fetchUser = User::where('email', $email)->first();
            if(!$fetchUser){
                return response()->json([
                    'message' => 'Account not found',
                    'code' => 'error',
                ]);
            }

            // Hash the new password before saving
            $fetchUser->password = Hash::make($newPassword);

            // Save the updated user
            $fetchUser->save();

            // Return success response
            return response()->json([
                'message' => 'Password has been successfully reset',
                'code' => 'success'
            ]);
            

        }catch(Exception $e){
            return response()->json([
                'message' => 'An error occured while updating password',
                'code' => 'error',
                'reason' => $e->getMessage()
            ]);
        }
    }
}
