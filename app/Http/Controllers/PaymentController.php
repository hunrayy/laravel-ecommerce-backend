<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Transaction;


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
                'phoneNumber' => 'phone',
                'country' => 'required|string',
                'state' => 'required|string',
                'checkoutTotal' => 'required|numeric',
                'currency' => 'required|string',
                'expectedDateOfDelivery' => 'required|string',
                'cartProducts' => 'required'
            ],
            ['phoneNumber.phone' => 'The :attribute must be a valid phone number, preceeded by the country code.']);

            // run a function to convert the price of each cart item to the desired currency passed
            $currencyClass = new CurrencyController();
            $cartProducts = $request->cartProducts; // Get the products from the request
        
            // Loop through each product and update the price
            foreach ($cartProducts as $index => $product) {
                // Convert the currency
                $convertedCurrency = $currencyClass->convertCurrency($product['productPriceInNaira'], $request->currency);
                
                // Add the new price directly to each product
                $cartProducts[$index]['updatedPrice'] = number_format($convertedCurrency, 2, '.', ',');
            }
        
            // Now cartProducts contains each product with its new price added
            $updatedRequestData = array_merge($request->all(), ['cartProducts' => $cartProducts]);



            $email = $updatedRequestData['email'];
            $firstname = $updatedRequestData['firstname'];
            $lastname = $updatedRequestData['lastname'];
            $address = $updatedRequestData['address'];
            $city = $updatedRequestData['city'];
            $postalCode = $updatedRequestData['postalCode'];
            $phoneNumber = $updatedRequestData['phoneNumber'];
            $country = $updatedRequestData['country'];
            $state = $updatedRequestData['state'];
            $subtotal = $updatedRequestData['totalPrice'];
            $shippingFee = $updatedRequestData['checkoutTotal'] - $updatedRequestData['totalPrice'];
            $totalPrice = $updatedRequestData['checkoutTotal'];
            $currency = $updatedRequestData['currency'];
            $expectedDateOfDelivery = $updatedRequestData['expectedDateOfDelivery'];
            $cartProducts = $updatedRequestData['cartProducts'];
            // $uniqueId = (int) substr(microtime(true) * random_int(10000, 99999), 0, 15);
            $uniqueId = random_int(1000000000, 9999999999);

            // Check in the database for uniqueness
            if (Transaction::where('tx_ref', "ref_$uniqueId")->exists()) {
                // Regenerate if duplicate
                // $uniqueId = (int) substr(microtime(true) * random_int(10000, 99999), 0, 15);
                $uniqueId = random_int(1000000000, 9999999999);
            }

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
                'shippingFee' => $shippingFee,
                'subtotal' => $subtotal,
                'cartProducts' => json_encode($cartProducts),
                'currency' => $currency,
                'expectedDateOfDelivery' => $expectedDateOfDelivery,
                'transactionId' => $uniqueId
            ];

            // Generate token with a 5-minute expiration
            $createTokenWithDetails = $authController->createToken($tokenPayload, 5 * 60);

            $payload = [
                'tx_ref' => 'ref_' . $uniqueId, // Unique transaction reference
                'email' => $email,
                'amount' => (int)$totalPrice,
                'currency' => $currency,  // Ensure this currency is supported by Flutterwave
                'customer' => [
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                    'name' => $firstname . ' ' . $lastname,
                ],
                'redirect_url' => env('FRONTEND_URL') . '/payment-status',
                'meta' => [
                    'detailsToken' => $createTokenWithDetails
                ]

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
            ]);
        }
    }

    public function validatePayment(Request $request){
        // Extract the 'tx_ref' from the request
        $tx_ref = $request->query('tx_ref');
        // $detailsToken = $request->query('detailsToken');
        // \Log::info("from tx_ref", ['tx_ref' => $tx_ref]);

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

            // \Log::info("from flutterwave", ['response' => $response->json()]);

            // Check if the response indicates success
            if ($response->json('status') === 'success') {
                $data = $response->json('data');
                $detailsToken = $data['meta']['detailsToken'];

                // Process payment (You can implement the processPayment logic)
                return $this->processPayment(
                    $data['flw_ref'],
                    $data['tx_ref'],
                    $data['amount'],
                    'successful',
                    $data['created_at'],
                    $data['payment_type'],
                    $detailsToken

                );
            } else {
                return response()->json([
                    'code' => 'error',
                    'message' => $response->json('message')
                ]);
            }

        } catch (\Exception $error) {
            // Log detailed error information for better debugging
            // \Log::error('Error response from Flutterwave', [
            //     'message' => $error->getMessage()
            // ]);

            // Handle any errors from the request
            return response()->json([
                'code' => 'error',
                'message' => $error->getMessage() ?: 'Error validating payment',
            ]);
        }
    }

    public function processPayment($flw_ref, $tx_ref, $amount, $status, $created_at, $payment_type, $detailsToken)
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

            $orderClass = new OrderController();
            return $orderClass->saveProductToDbAfterPayment($detailsToken);

            // return response()->json([
            //     'code' => 'success',
            //     'message' => 'Transaction processed successfully'
            // ], 200);

        } catch (\Exception $error) {
            // Log the error for debugging purposes
            // Log::error('Error processing payment', [
            //     'flw_ref' => $flw_ref,
            //     'tx_ref' => $tx_ref,
            //     'error' => $error->getMessage()
            // ]);

            // Return an error response
            return response()->json([
                'code' => 'error',
                'message' => 'An error occurred while processing the transaction',
                'reason' => $error->getMessage()
            ]);
        }
    }


}
