<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;
// use Illuminate\Support\Facades\Mail; //for sending mails
// use App\Models\Order;
// use App\Models\User; 

// class OrderController extends Controller
// {
//     //

//     public function saveProductToDbAfterPayment(Request $request){
//       try{
//             $detailsToken = $request->header('detailsToken'); // Token from header

//             // Verify JWT Token
//             $verifyToken = JWT::decode($detailsToken, new Key(env('JWT_SECRET'), 'HS256'));

//             // Extract details from the token
//             $firstname = $verifyToken->firstname;
//             $lastname = $verifyToken->lastname;
//             $email = $verifyToken->email;
//             $address = $verifyToken->address;
//             $city = $verifyToken->city;
//             $postalCode = $verifyToken->postalCode;
//             $phoneNumber = $verifyToken->phoneNumber;
//             $country = $verifyToken->country;
//             $state = $verifyToken->state;
//             $totalPrice = $verifyToken->totalPrice;
//             $currency = $verifyToken->currency;
//             $expectedDateOfDelivery = $verifyToken->expectedDateOfDelivery;
//             $products = $request->input('cartProducts'); // Assuming products are passed from the frontend

//             // Fetch user_id from the email passed in the token
//             $user = User::where('email', $email)->firstOrFail(); 
//             $user_id = $user->id;


//             // Create order details
//             $tracking_id = rand(1000000000, 9999999999); // Generates a random 10-digit number
//             $orderDetails = [
//                 'tracking_id' => $tracking_id,
//                 'user_id' => $user_id,
//                 'firstname' => $firstname,
//                 'lastname' => $lastname,
//                 'email' => $email,
//                 'country' => $country,
//                 'state' => $state,
//                 'address' => $address,
//                 'city' => $city,
//                 'postalCode' => $postalCode,
//                 'phoneNumber' => $phoneNumber,
//                 'totalPrice' => $totalPrice,
//                 'currency' => $currency,
//                 'products' => json_encode($products), // Save products as JSON
//                 'status' => 'Pending'
//             ];

//             // Save order to the database
//             $order = Order::create($orderDetails);

//             // Send a confirmation email to the user
//             Mail::send([], [], function ($message) use ($firstname, $email, $totalPrice, $currency, $order, $address, $products) {
//                 $message->to($email)
//                     ->subject('Payment Confirmation')
//                     ->setBody("<h4>
//                     <b>Dear {$firstname}</b>,
//                     <br />
//                     Thank you for your payment! We're pleased to inform you that your transaction has been successfully processed.<br />
//                     Tracking ID: {$tracking_id}
//                     Order ID: {$order->id}<br />
//                     Amount Paid: {$currency} " . number_format((float)$totalPrice, 2, '.', '') . "<br />
//                     Order Summary:<br />" .
//                     implode('', array_map(function($item, $index) use ($currency) {
//                         return "- Item " . ($index + 1) . ": " . $item['name'] . " - {$currency} " . number_format((float)$item['price'], 2, '.', '') . "<br />";
//                     }, json_decode($products, true), array_keys(json_decode($products, true)))) .
//                     "Shipping Address: {$address}<br />
//                     Expected date of delivery: {$expectedDateOfDelivery}<br />
//                     If you have any questions or need assistance, feel free to contact our support team.<br />
//                     Thank you for choosing us! We look forward to serving you again.<br />
//                     Best regards.<br />
//                     </h4>", 'text/html');
//             });

//             return response()->json([
//                 'message' => 'Products ordered successfully saved to DB',
//                 'code' => 'success'
//             ]);


//         }catch (\Exception $error) {
//             return response()->json([
//                 'message' => 'Products ordered could not be saved to DB',
//                 'code' => 'error',
//                 'reason' => $error->getMessage()
//             ]);
//         }

//     }
// }
