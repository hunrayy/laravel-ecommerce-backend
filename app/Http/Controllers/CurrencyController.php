<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    //
    public function convertCurrency($amountInNaira, $targetCurrency){
        $apiUrl = 'https://api.exchangerate-api.com/v4/latest/NGN';

        try {
            // Send a GET request to fetch exchange rates for NGN
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                // Get the target currency exchange rate from the response
                $targetCurrencyRate = $response['rates'][$targetCurrency];

                
                $convertedAmount = $amountInNaira * $targetCurrencyRate;

                return $convertedAmount;
            } else {
                return response()->json([
                    'code' => "error",
                    'message' => 'Failed to retrieve exchange rates'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => "error",
                'message' => 'Error converting currency: ' . $e->getMessage()
            ]);
        }
    }
}
