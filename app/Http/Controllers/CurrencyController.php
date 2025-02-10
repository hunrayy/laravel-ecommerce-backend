<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    //
    public function convertCurrency($amountInPounds, $targetCurrency){
        $apiUrl = 'https://api.exchangerate-api.com/v4/latest/GBP';

        try {
            // Send a GET request to fetch exchange rates for GBP
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                // Get the target currency exchange rate from the response
                $targetCurrencyRate = $response['rates'][$targetCurrency];

                
                $convertedAmount = $amountInPounds * $targetCurrencyRate;

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
