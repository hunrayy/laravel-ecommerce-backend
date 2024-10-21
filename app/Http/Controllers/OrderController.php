<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Controllers\MailController;

use App\Models\Order;
use App\Models\User; 
use Illuminate\Support\Facades\Redis;


/**
 * pass the expected date of delivery, shipping fee and transaction id from flutter wave to the mail and save to the database also
 */

class OrderController extends Controller
{
    //

    public function saveProductToDbAfterPayment(Request $request){
        try{
            $detailsToken = $request->header('detailsToken'); // Token from header

            // Verify JWT Token
            $verifyToken = JWT::decode($detailsToken, new Key(env('JWT_SECRET'), 'HS256'));

            // Extract details from the token
            $firstname = $verifyToken->firstname;
            $lastname = $verifyToken->lastname;
            $email = $verifyToken->email;
            $address = $verifyToken->address;
            $city = $verifyToken->city;
            $postalCode = $verifyToken->postalCode;
            $phoneNumber = $verifyToken->phoneNumber;
            $country = $verifyToken->country;
            $state = $verifyToken->state;
            $totalPrice = $verifyToken->totalPrice;
            $currency = $verifyToken->currency;
            $expectedDateOfDelivery = $verifyToken->expectedDateOfDelivery;
            $transactionId = $verifyToken->transactionId;
            $products = $request->input('cartProducts'); // Assuming products are passed from the frontend

            // Fetch user_id from the email passed in the token
            $user = User::where('email', $email)->firstOrFail(); 
            $user_id = $user->id;


            // Create order details
            $tracking_id = rand(1000000000, 9999999999); // Generates a random 10-digit number
            $numberFormatOfTotalPrice = str_replace(',', '', $totalPrice); // Remove commas
            $orderDetails = [
                'tracking_id' => $tracking_id,
                'transaction_id' => $transactionId,
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
                return "
                <div style='padding: 20px; text-align: center; background: #f4f4f4; max-width: 300px;'>
                    <div>
                        <img src='{$item['productImage']}' alt='" . htmlspecialchars($item['productName']) . "' style='width: 80px; height: 80px;'>
                    </div>
                    <div style='text-align: center;'>
                        <h4 style='margin: 0;'>" . htmlspecialchars($item['productName']) . "</h4>
                        <h5 style='margin: 0;'>Length - " . htmlspecialchars($item['lengthPicked']) . "</h5>
                        <h5 style='margin: 0;'>Quantity * " . htmlspecialchars($item['quantity']) . "</h5>
                        <h5 style='margin: 0;'><b>Price:</b> {$currency} " . number_format((int)$item['updatedPrice']) . "</5>
                    </div>
                </div>
                    ";
            }, $products, array_keys($products)));

            $postalCodeSection = $postalCode ? "<b>Postal code:</b> {$postalCode}<br/>" : '';

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
                        <b>Transaction ID:</b> {$transactionId}<br/>
                        <b>Total Price:</b> {$totalPrice}<br/>
                    </p>
                    <h4 style='color: #333;'>Order Summary:</h4>
                    <div style='display: flex; flex-wrap: wrap; gap: 10px;'>
                        {$orderSummary}
                    </div>
                    <p>
                        <p><b>Shipping information:</b></p>
                        <b>Country:</b> {$country}<br/>
                        <b>State:</b> {$state}<br/>
                        <b>City:</b> {$city}<br/>
                        <b>Address:</b> {$address}<br/>
                        {$postalCodeSection} <!-- Postal Code section will only appear if not null -->
                        <b>Expected date of delivery:</b> {$expectedDateOfDelivery}
                    </p>
                    <p>
                        <b>Contact information</b><br/>
                        <b>Phone number:</b> {$phoneNumber}<br/>
                    </p>
                    <p>
                        If you have any question or need assistance, feel free to contact our support team.
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


    public function getOrders(Request $request){
        try{
            $query = $request->query('status');
            if($query == "pending"){
                //check if pending orders are saved in cache
                $cachedOrders = Redis::get('pendingOrders');
                if($cachedOrders){
                    return response()->json([
                        "message" => "all pending orders successfully retrieved from cache",
                        "code" => "success",
                        "data" => json_decode($cachedOrders, true)
                    ]);
                }else{
                    //fetch all pending orders from database
                    $allPendingOrders = Order::where('status', 'pending')->orderBy('created_at', 'desc')->get();

                    //save fetched orders to cache
                    Redis::set('pendingOrders', json_encode($allPendingOrders, true));

                    return response()->json([
                        "message" => "all pending orders successfully retrieved from database",
                        "code" => "success",
                        "data" => $allPendingOrders
                    ]);
                }


            }else if($query == "outForDelivery"){
                //check if out-for-delivery orders are saved in cache
                $cachedOrders = Redis::get('outForDeliveryOrders');

                if($cachedOrders){
                    return response()->json([
                        "message" => "all out-for-delivery orders successfully retrieved from cache",
                        "code" => "success",
                        "data" => json_decode($cachedOrders, true)
                    ]);
                }else{
                    //fetch all out-for-delivery orders from database
                    $allOutForDeliveryOrders = Order::where('status', 'outForDelivery')->orderBy('updated_at', 'desc')->get();

                    //save fetched orders to cache
                    Redis::set('outForDeliveryOrders', json_encode($allOutForDeliveryOrders, true));
                    
                    return response()->json([
                        "message" => "all out-for-delivery orders successfully retrieved from database",
                        "code" => "success",
                        "data" => $allOutForDeliveryOrders
                    ]);
                }
            }else if($query == "delivered"){
                //check if delivered orders are saved in cache
                $cachedOrders = Redis::get('deliveredOrders');

                if($cachedOrders){
                    return response()->json([
                        "message" => "all delivered orders successfully retrieved from cache",
                        "code" => "success",
                        "data" => json_decode($cachedOrders, true)
                    ]);
                }else{
                    //fetch all delivered orders from database
                    $allDeliveredOrders = Order::where('status', 'delivered')->orderBy('created_at', 'desc')->get();

                    //save fetched orders to cache
                    Redis::set('deliveredOrders', json_encode($allDeliveredOrders, true));
                    
                    
                    return response()->json([
                        "message" => "all delivered orders successfully retrieved from database",
                        "code" => "success",
                        "data" => $allDeliveredOrders
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                "message" => "an error occured while fetching pending orders",
                "code" => "error",
                "reason" => $e->getMessage()
            ]);
        }
    }

    public function ChangeOrderStatusToOutForDelivery(Request $request){
        try{
            $request->validate([
                'trackingId' => 'string|required'
            ]);
            //fetch the order in the database using the trackingId
            $order = Order::where('tracking_id', $request->trackingId)->first();
            if(!$order){
                return response()->json([
                    "message" => "Order with tracking number does not exist",
                    "code" => "error"
                ]);
            }

            $firstname = $order->firstname;
            $lastname = $order->lastname;
            $email = $order->email;
            $address = $order->address;
            $city = $order->city;
            $postalCode = $order->postalCode;
            $phoneNumber = $order->phoneNumber;
            $country = $order->country;
            $state = $order->state;
            $totalPrice = $order->totalPrice;
            $trackingId = $order->tracking_id;
            $orderDate = $order->created_at->format('F j, Y');
            $outForDeliveryDate = $order->updated_at->format('F j, Y');
            $currency = $order->currency;
            $expectedDateOfDelivery = $order->expectedDateOfDelivery;
            $transactionId = $order->transactionId;
            $products = json_decode($order->products, true);

            //update the status to out-for-delivery
            $order->status = 'outForDelivery';
            $order->save();

            //fetch all fresh out-for-delivery orders from database
            $newAllOutForDeliveryOrders = Order::where('status', 'outForDelivery')->orderBy('updated_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('outForDeliveryOrders', json_encode($newAllOutForDeliveryOrders, true));

            //fetch all fresh delivered orders from database
            $newAllOutForDeliveredOrders = Order::where('status', 'delivered')->orderBy('updated_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('deliveredOrders', json_encode($newAllOutForDeliveredOrders, true));
            

            //send a notification via mail to the user
            $subject = 'Order Status Update'; //subject of mail

            $orderSummary = implode('', array_map(function($item, $index) use ($currency) {
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

            $postalCodeSection = $postalCode ? "<b>Postal code:</b> {$postalCode}<br/>" : '';

            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #4CAF50;'>Order Status Update</h2>
                    <p style='font-size: 16px;'>
                        <b>Hello {$firstname},</b>
                    </p>
                    <p>
                        We are excited to inform you that your order with Tracking ID: <strong>{$trackingId}</strong> is now <strong>'Out for Delivery'</strong>! Our delivery team is working hard to ensure your order reaches you promptly.
                    </p>

                    <h4>Order Summary:</h4>
                    <ul>
                        <li><strong>Tracking ID:</strong> {$trackingId}</li>
                        <li><strong>Order Date:</strong> {$orderDate}</li>
                        <li><strong>Out For Delivery Date:</strong> {$outForDeliveryDate}</li>
                    </ul>

                    <h4 style='color: #333;'>Order Product(s):</h4>
                    <div style='display: flex; flex-wrap: wrap; gap: 10px;'>
                        {$orderSummary}
                    </div>
                   
                    <p>
                        If you have any question or concern, feel free to contact our support team.
                    </p>
                    <p style='margin-top: 20px;'>
                        Thank you for choosing us!, and we hope you enjoy your purchase!.
                    </p>
                </div>
            ";

            // Send the email
            $mailClass = new MailController();
            $mailClass->sendEMail($email, $subject, $body);

            return response()->json([
                'message' => "order status successfully updated to out for delivery",
                "code" => "success"
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => "An error occured while updating order status to out for delivery",
                "code" => "error",
                "reason" => $e->getMessage()
            ]);
        }

    }


    public function ChangeOrderStatusToDelivered(Request $request){
        try{
            $request->validate([
                'trackingId' => 'string|required'
            ]);
            //fetch the order in the database using the trackingId
            $order = Order::where('tracking_id', $request->trackingId)->first();
            if(!$order){
                return response()->json([
                    "message" => "Order with tracking number does not exist",
                    "code" => "error"
                ]);
            }

            $firstname = $order->firstname;
            $lastname = $order->lastname;
            $email = $order->email;
            $address = $order->address;
            $city = $order->city;
            $postalCode = $order->postalCode;
            $phoneNumber = $order->phoneNumber;
            $country = $order->country;
            $state = $order->state;
            $totalPrice = $order->totalPrice;
            $trackingId = $order->tracking_id;
            $orderDate = $order->created_at->format('F j, Y');
            $deliveredDate = $order->updated_at->format('F j, Y');
            $currency = $order->currency;
            $expectedDateOfDelivery = $order->expectedDateOfDelivery;
            $transactionId = $order->transactionId;
            $products = json_decode($order->products, true);

            //update the status to out-for-delivery
            $order->status = 'delivered';
            $order->save();

            //fetch all fresh delivered orders from database
            $newAllDeliveredOrders = Order::where('status', 'delivered')->orderBy('updated_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('deliveredOrders', json_encode($newAllDeliveredOrders, true));

            //fetch all fresh out-for-delivery orders from database
            $newAllOutForDeliveryOrders = Order::where('status', 'outForDelivery')->orderBy('updated_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('outForDeliveryOrders', json_encode($newAllOutForDeliveryOrders, true));
            

            //send a notification via mail to the user
            $subject = 'Order Status Update'; //subject of mail

            $orderSummary = implode('', array_map(function($item, $index) use ($currency) {
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

            $postalCodeSection = $postalCode ? "<b>Postal code:</b> {$postalCode}<br/>" : '';

            $body = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #4CAF50;'>Order Status Update</h2>
                    <p style='font-size: 16px;'>
                        <b>Dear {$firstname},</b>
                    </p>
                        <p>
                            We are happy to inform you that your order with Tracking ID: <strong>{$trackingId}</strong> has been <strong>delivered</strong>! We hope that everything arrived in great condition and that you are satisfied with your purchase.
                        </p>

                    <h4>Order Summary:</h4>
                    <ul>
                        <li><strong>Tracking ID:</strong> {$trackingId}</li>
                        <li><strong>Order Date:</strong> {$orderDate}</li>
                        <li><strong>Out For Delivery Date:</strong> {$deliveredDate}</li>
                    </ul>

                    <h4 style='color: #333;'>Order Product(s):</h4>
                    <div style='display: flex; flex-wrap: wrap; gap: 10px;'>
                        {$orderSummary}
                    </div>
                   
                     <p>
                        If you have any questions, concerns, or feedback regarding your order, please don't hesitate to contact our support team. Your satisfaction is important to us!
                    </p>
                        <p style='margin-top: 20px;'>
                            Thank you once again for choosing us. We hope you enjoy your purchase, and we look forward to serving you again in the future!
                        </p>
                </div>
            ";

            // Send the email
            $mailClass = new MailController();
            $mailClass->sendEMail($email, $subject, $body);

            return response()->json([
                'message' => "order status successfully updated to delivered",
                "code" => "success"
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => "An error occured while updating order status to out for delivery",
                "code" => "error",
                "reason" => $e->getMessage()
            ]);
        }
    }
}
