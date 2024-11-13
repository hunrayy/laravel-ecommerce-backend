<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\PaystackTransaction;

class PaystackPaymentController extends Controller
{
    
    //
    public function makePayment(Request $request)
    {
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
            $uniqueId = now()->timestamp;

            // Call createToken method from AuthController
            $authController = new AuthController();

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
                'cartProducts' => $cartProducts,
                'currency' => $currency,
                'expectedDateOfDelivery' => $expectedDateOfDelivery,
                'transactionId' => $uniqueId
            ];

            // Generate token with a 5-minute expiration
            $createTokenWithDetails = $authController->createToken($tokenPayload, 5 * 60);

            // Paystack API payload
            $payload = [
                'email' => $email,
                'amount' => (int)($totalPrice * 100), // Paystack requires amount in kobo (Naira subunit)
                'currency' => $currency,  // e.g., "NGN"
                'reference' => 'ref_' . $uniqueId,  // Unique transaction reference
                'callback_url' => env('FRONTEND_URL') . '/payment-success?details=' . $createTokenWithDetails,
                'metadata' => [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone_number' => $phoneNumber,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'shippingFee' => $shippingFee,
                    'expectedDateOfDelivery' => $expectedDateOfDelivery,
                    'cartProducts' => $cartProducts
                ],
            ];

            // Make POST request to Paystack API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', $payload);

            // Check if the request was successful
            if ($response->successful()) {
                // Redirect user to Paystack payment page
                return $response->json();
            } else {
                return response()->json([
                    'message' => 'Error initiating payment',
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


    public function validatePayment(Request $request)
    {
        // Extract the 'reference' from the request (Paystack uses 'reference')
        $reference = $request->query('reference');
        $detailsToken = $request->query('detailsToken');

        // Check if 'reference' is missing
        if (!$reference) {
            return response()->json([
                'code' => 'error',
                'message' => 'Transaction reference is required.'
            ]);
        }

        try {
            // Make a GET request to Paystack's API to verify the transaction
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            ])->get('https://api.paystack.co/transaction/verify/' . $reference);

            // \Log::info("from Paystack", ['response' => $response->json()]);

            // Check if the response indicates success
            if ($response->json('status') === true) {
                $data = $response->json('data');

                // Check if payment was successful
                if ($data['status'] === 'success') {
                    // Process payment (You can implement the processPayment logic)
                    return $this->processPayment(
                        $data['reference'],
                        $data['amount'] / 100, // Convert from kobo to the actual amount
                        'successful',
                        $data['paid_at'],
                        $data['channel'], // payment channel used (card, bank, etc.)
                        $detailsToken
                    );
                } else {
                    return response()->json([
                        'code' => 'error',
                        'message' => 'Payment verification failed.'
                    ]);
                }
            } else {
                return response()->json([
                    'code' => 'error',
                    'message' => $response->json('message')
                ]);
            }

        } catch (\Exception $error) {
            // Log detailed error information for better debugging
            \Log::error('Error response from Paystack', [
                'message' => $error->getMessage()
            ]);

            // Handle any errors from the request
            return response()->json([
                'code' => 'error',
                'message' => $error->getMessage() ?: 'Error validating payment',
            ]);
        }
    }


    public function processPayment($reference, $amount, $status, $paid_at, $channel, $detailsToken)
    {
        try {
            // Start a database transaction
            // DB::transaction(function () use ($reference, $amount, $status, $channel, $detailsToken) {
                // Check if a transaction with the same reference already exists using `lockForUpdate()`
                $existingTransaction = PaystackTransaction::where('reference', $reference)->lockForUpdate()->first();
                
                if ($existingTransaction !== null) {
                    \Log::info('Existing transaction found:', ['reference' => $reference]);
                    \Log::info('Existing transaction found:', ['transaction' => $existingTransaction]);
                    return response()->json([
                        'code' => 'already-made',
                        'message' => 'Transaction already processed'
                    ]);
                } else {
                    \Log::info('Inserting new transaction:', ['reference' => $reference]);
    
                    // Insert the transaction into the 'transactions' table
                    PaystackTransaction::insert([
                        'id' => (string) Str::uuid(), // Generate and cast the UUID to string
                        'reference' => $reference,  // Paystack's unique transaction reference
                        'amount' => $amount, // Amount in base currency (kobo if NGN)
                        'status' => $status, // The transaction status, e.g., 'success'
                        'payment_channel' => $channel, // Payment channel like card, bank, etc.
                    ]);
    
                    $orderClass = new OrderController();
                    return $orderClass->saveProductToDbAfterPayment($detailsToken);
                }
            // });
    
        } catch (\Exception $error) {
            // Log the error for debugging purposes
            // Log::error('Error processing payment', [
            //     'reference' => $reference,
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
