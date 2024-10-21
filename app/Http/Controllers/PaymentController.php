<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\User;

class PaymentController extends Controller
{
    //
    public function makePayment(Request $request){
        try {   
            $request->validate([
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'email' => 'required|email',
                'address' => 'required|string',
                'city' => 'required|string',
                'phoneNumber' => 'required|string',
                'country' => 'required|string',
                'state' => 'required|string',
                'checkoutTotal' => 'required|numeric',
                'currency' => 'required|string',
                'expectedDateOfDelivery' => 'required|string'
            ]);

            $email = $request->input('email');
            $firstname = $request->input('firstname');
            $lastname = $request->input('lastname');
            $address = $request->input('address');
            $city = $request->input('city');
            $postalCode = $request->input('postalCode');
            $phoneNumber = $request->input('phoneNumber');
            $country = $request->input('country');
            $state = $request->input('state');
            $totalPrice = $request->input('checkoutTotal');
            $currency = $request->input('currency');
            $expectedDateOfDelivery = $request->input('expectedDateOfDelivery');
            $uniqueId = now()->timestamp; // Similar to Date.now()

            // Call createToken method from AuthController
            $authController = new AuthController(); // Create an instance of AuthController

            $tokenPayload = [
                
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'address' => $address,
                'city' => $city,
                'postalCode' => $postalCode,
                'phoneNumber' => $phoneNumber,
                'country' => $country,
                'state' => $request->state,
                'totalPrice' => $totalPrice,
                'currency' => $currency,
                'expectedDateOfDelivery' => $expectedDateOfDelivery,
                'transactionId' => $uniqueId
            ];

            // Generate token with a 5-minute expiration
            $createTokenWithDetails = $authController->createToken($tokenPayload, 5 * 60);

            // Flutterwave API payload
            $payload = [
                'tx_ref' => 'ref_' . $uniqueId, // Unique transaction reference
                'amount' => (float)$totalPrice,
                'currency' => $currency,  // Ensure this currency is supported by Flutterwave
                'customer' => [
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                    'name' => $firstname . ' ' . $lastname,
                ],
                'redirect_url' => env('FRONTEND_URL') . '/payment-success?details=' . $createTokenWithDetails,
            ];
            // Make POST request to Flutterwave API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://api.flutterwave.com/v3/payments', $payload);
            
            // Check if the request was successful
            if ($response->successful()) {
                return $response->json(); // Return the response data from Flutterwave
            } else {
                return response()->json([
                    'message' => 'Error creating payment',
                    'code' => 'error',
                    'reason' => $response->json()['message']
                ]);
            }

        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Error creating payment',
                'code' => 'error',
                'reason' => $error->getMessage()
            ], 500);
        }
    }

    public function validatePayment(Request $request){
        // Extract the 'tx_ref' from the request
        $tx_ref = $request->query('tx_ref');
        \Log::info("from tx_ref", ['tx_ref' => $tx_ref]);

        // Check if 'tx_ref' is missing
        if (!$tx_ref) {
            return response()->json([
                'code' => 'error',
                'reason' => 'Transaction reference is required.'
            ]);
        }

        try {
            // Make a GET request to Flutterwave's API to verify the transaction
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
            ])->get('https://api.flutterwave.com/v3/transactions/verify_by_reference', [
                'tx_ref' => $tx_ref
            ]);

            \Log::info("from flutterwave", ['response' => $response->json()]);

            // Check if the response indicates success
            if ($response->json('status') === 'success') {
                $data = $response->json('data');

                // Process payment (You can implement the processPayment logic)
                return $this->processPayment(
                    $data['flw_ref'],
                    $data['tx_ref'],
                    $data['amount'],
                    'successful',
                    $data['created_at'],
                    $data['payment_type']
                );
            } else {
                return response()->json([
                    'code' => 'error',
                    'message' => 'Payment verification failed'
                ], 400);
            }

        } catch (\Exception $error) {
            // Log detailed error information for better debugging
            \Log::error('Error response from Flutterwave', [
                'message' => $error->getMessage()
            ]);

            // Handle any errors from the request
            return response()->json([
                'code' => 'error',
                'message' => $error->getMessage() ?: 'Error validating payment'
            ], 500);
        }
    }

    public function processPayment($flw_ref, $tx_ref, $amount, $status, $created_at, $payment_type)
    {
        try {
            // Check if a transaction with the same flw_ref or tx_ref already exists
            $existingTransaction = DB::table('transactions')
                ->where('flw_ref', $flw_ref)
                ->orWhere('tx_ref', $tx_ref)
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'code' => 'already-made',
                    'message' => 'Transaction already processed'
                ], 200);
            }

            // Insert the transaction into the 'transactions' table
            DB::table('transactions')->insert([
                'id' => (string) Str::uuid(), // Generate and cast the UUID to string
                'flw_ref' => $flw_ref,
                'tx_ref' => $tx_ref,
                'amount' => $amount,
                'status' => $status,
                'payment_type' => $payment_type
            ]);

            return response()->json([
                'code' => 'success',
                'message' => 'Transaction processed successfully'
            ], 200);

        } catch (\Exception $error) {
            // Log the error for debugging purposes
            Log::error('Error processing payment', [
                'flw_ref' => $flw_ref,
                'tx_ref' => $tx_ref,
                'error' => $error->getMessage()
            ]);

            // Return an error response
            return response()->json([
                'code' => 'error',
                'message' => 'An error occurred while processing the transaction',
                'reason' => $error->getMessage()
            ], 500);
        }
    }


}
