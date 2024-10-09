<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Controllers\MailController;

use App\Models\Order;
use App\Models\User; 

class OrderController extends Controller
{
    //

    public function saveProductToDbAfterPayment(Request $request){
        try{
            // $detailsToken = $request->header('detailsToken'); // Token from header

            // // Verify JWT Token
            // $verifyToken = JWT::decode($detailsToken, new Key(env('JWT_SECRET'), 'HS256'));

            // // Extract details from the token
            // $firstname = $verifyToken->firstname;
            // $lastname = $verifyToken->lastname;
            // $email = $verifyToken->email;
            // $address = $verifyToken->address;
            // $city = $verifyToken->city;
            // $postalCode = $verifyToken->postalCode;
            // $phoneNumber = $verifyToken->phoneNumber;
            // $country = $verifyToken->country;
            // $state = $verifyToken->state;
            // $totalPrice = $verifyToken->totalPrice;
            // $currency = $verifyToken->currency;
            // $expectedDateOfDelivery = $verifyToken->expectedDateOfDelivery;
            // $products = $request->input('cartProducts'); // Assuming products are passed from the frontend





             // // Extract details from the token
            $firstname = $request->firstname;
            $lastname = $request->lastname;
            $email = $request->email;
            $address = $request->address;
            $city = $request->city;
            $postalCode = $request->postalCode;
            $phoneNumber = $request->phoneNumber;
            $country = $request->country;
            $state = $request->state;
            $totalPrice = $request->totalPrice;
            $currency = $request->currency;
            $expectedDateOfDelivery = $request->expectedDateOfDelivery;
            $products = $request->input('cartProducts'); // Assuming products are passed from the frontend



















            // Fetch user_id from the email passed in the token
            $user = User::where('email', $email)->firstOrFail(); 
            $user_id = $user->id;


            // Create order details
            $tracking_id = rand(1000000000, 9999999999); // Generates a random 10-digit number
            $numberFormatOfTotalPrice = str_replace(',', '', $totalPrice); // Remove commas
            $orderDetails = [
                'tracking_id' => $tracking_id,
                'user_id' => $user_id,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'country' => $country,
                'state' => $state,
                'address' => $address,
                'city' => $city,
                'postalCode' => $postalCode,
                'phoneNumber' => $phoneNumber,
                'totalPrice' => $numberFormatOfTotalPrice,
                'currency' => $currency,
                'products' => json_encode($products), // Save products as JSON
                'status' => 'Pending'
            ];

            // Save order to the database
            $order = Order::create($orderDetails);

            // Send a confirmation email to the user
            $subject = 'Payment Confirmation'; //subject of mail

            $orderSummary = implode('', array_map(function($item, $index) use ($currency) {
                // return "<li style='margin-bottom: 8px;'>Item " . ($index + 1) . ": <b>" . $item['name'] . "</b> - {$currency} " . number_format((float)$item['price'], 2, '.', '') . "</li>";
                // return "
                // <div style='border: 1px solid #ddd; border-radius: 5px; padding: 10px; margin: 10px 0; display: flex; flex-direction: column; justify-content: center; align-items: center'>
                //     <img src='{$item['img']}' alt='" . htmlspecialchars($item['name']) . "' style='width: 80px; height: 80px;'>
                //     <div style='text-align: center;'>
                //         <h4 style='margin: 0;'>" . htmlspecialchars($item['name']) . "</h4>
                //         <h5 style='margin: 0;'>Length - " . htmlspecialchars($item['lengthPicked']) . "</h5>
                //         <h5 style='margin: 0;'>Quantity * " . htmlspecialchars($item['quantity']) . "</h5>
                //         <h5 style='margin: 0;'><b>Price:</b> {$currency} " . number_format((int)$item['price']) . "</5>
                //     </div>
                // </div>";
                return "
                <div style='padding: 20px; text-align: center; background: #f4f4f4'>
                    <div>
                        <img src='{$item['img']}' alt='" . htmlspecialchars($item['name']) . "' style='width: 80px; height: 80px;'>
                    </div>
                    <div style='text-align: center;'>
                        <h4 style='margin: 0;'>" . htmlspecialchars($item['name']) . "</h4>
                        <h5 style='margin: 0;'>Length - " . htmlspecialchars($item['lengthPicked']) . "</h5>
                        <h5 style='margin: 0;'>Quantity * " . htmlspecialchars($item['quantity']) . "</h5>
                        <h5 style='margin: 0;'><b>Price:</b> {$currency} " . number_format((int)$item['price']) . "</5>
                    </div>
                </div>
                    ";
            }, $products, array_keys($products)));

            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #4CAF50;'>Payment Confirmation</h2>
                    <p style='font-size: 16px;'>
                        <b>Dear {$firstname},</b>
                    </p>
                    <p>
                        Thank you for your payment! We're pleased to inform you that your transaction has been successfully processed.
                    </p>
                    <p>
                        <b>Tracking ID:</b> {$tracking_id}<br/>
                        <b>Order ID:</b> {$order->id}<br/>
                    </p>
                    <h4 style='color: #333;'>Order Summary:</h4>
                    <div style='display: flex; flex-wrap: wrap; gap: 10px;'>
                        {$orderSummary}
                    </div>
                    <p>
                        <b>Shipping information</b>
                        <b>Country:</b> {$currency} " . $totalPrice . "<br/>
                        <b>State:</b> {$currency} " . $totalPrice . "<br/>
                        <b>City:</b> {$currency} " . $totalPrice . "<br/>
                        <b>Address:</b> {$currency} " . $totalPrice . "<br/>
                        <b>Shipping Address:</b> {$address}<br/>
                        <b>Expected date of delivery:</b> {$expectedDateOfDelivery}
                    </p>
                    <p>
                        <b>Total Amount:</b> {$currency} " . $totalPrice . "<br/>
                        <b>Shipping Address:</b> {$address}<br/>
                        <b>Expected date of delivery:</b> {$expectedDateOfDelivery}
                    </p>
                    <p>
                        If you have any questions or need assistance, feel free to contact our support team.
                    </p>
                    <p style='margin-top: 20px;'>
                        Thank you for choosing us! We look forward to serving you again.
                    </p>
                </div>
            ";

            // Send the email
            $mailClass = new MailController();
            $mailClass->sendEMail($email, $subject, $body);
            

            return response()->json([
                'message' => 'Product(s) ordered successfully saved to DB',
                'code' => 'success'
            ]);


        }catch (\Exception $error) {
            return response()->json([
                'message' => 'Products ordered could not be saved to DB',
                'code' => 'error',
                'reason' => $error->getMessage()
            ]);
        }

    }
}
