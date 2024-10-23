<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Controllers\MailController;
use App\Http\Controllers\CurrencyController;


use App\Models\Order;
use App\Models\User; 
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;


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
            $subtotal = $verifyToken->subtotal;
            $shippingFee = $verifyToken->shippingFee;
            $currency = $verifyToken->currency;
            $expectedDateOfDelivery = $verifyToken->expectedDateOfDelivery;
            $transactionId = $verifyToken->transactionId;
            // $products = $request->input('cartProducts'); // Assuming products are passed from the frontend
            $products = $verifyToken->cartProducts;

            // Fetch user_id from the email passed in the token
            $user = User::where('email', $email)->firstOrFail(); 
            $user_id = $user->id;


            // Create order details
            $tracking_id = rand(1000000000, 9999999999); // Generates a random 10-digit number
            $numberFormatOfTotalPrice = str_replace(',', '', $totalPrice); // Remove commas
            $numberFormatOfSubtotal = str_replace(',', '', $subtotal); // Remove commas
            $numberFormatOfShippingFee = str_replace(',', '', $shippingFee); // Remove commas
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
                'subtotal' => $numberFormatOfSubtotal,
                'shippingFee' => $numberFormatOfShippingFee,
                'postalCode' => $postalCode,
                'phoneNumber' => $phoneNumber,
                'totalPrice' => $numberFormatOfTotalPrice,
                'currency' => $currency,
                'products' => json_encode($products), // Save products as JSON
                'expectedDateOfDelivery' => $expectedDateOfDelivery,
                'status' => 'Pending'
            ];

            // Save order to the database
            $order = Order::create($orderDetails);

            //fetch all orders from the database and save to cache
            $allPendingOrders = Order::where('status', 'pending')->orderBy('created_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('pendingOrders', json_encode($allPendingOrders, true));


            // Send a confirmation email to the user
            $subject = 'Payment Confirmation'; //subject of mail

            $orderSummary = implode('', array_map(function($item, $index) use ($currency) {
                // $formattedPrice = 'NGN' . ' ' . number_format((float)$item->price, 2, '.', ',');
                $currencyClass = new CurrencyController();
                $convertedCurrency = $currencyClass->convertCurrency($item->productPriceInNaira, $currency);

                $formattedPrice = $currency . ' ' . number_format((float)$convertedCurrency, 2, '.', ',');


                return "
                        <div style='display: flex; border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin-bottom: 20px; align-items: center; background-color: #fafafa;'>
                                        <img src='{$item->productImage}' alt='" . htmlspecialchars($item->productName) . "' style='width: 100%; height: auto; max-width: 80px; object-fit: cover; border-radius: 8px; margin-right: 20px;' />
                                        <div style='flex-grow: 1;'>
                                            <h3 style='margin: 0; color: #333; font-size: 18px;'>" . htmlspecialchars($item->productName) . "</h3>
                                            <p style='margin: 5px 0; color: #777; font-size: 14px;'>Length: " . htmlspecialchars($item->lengthPicked) . "</p>
                                            <p style='margin: 5px 0; color: #777; font-size: 14px;'>Quantity: " . htmlspecialchars($item->quantity) . "</p>
                                            <p style='margin: 5px 0; color: #777; font-size: 14px;'>Price: {$formattedPrice}</p>
                                        </div>
                        </div>
                    ";
                    
            }, $products, array_keys($products)));

            $postalCodeSection = $postalCode ? "<p style='margin: 5px 0;'><strong>Postal code:</strong> {$postalCode}</p>" : '';
            $formattedTotalPrice = number_format($totalPrice);
            $formattedSubtotal = number_format($subtotal);
            $formattedShippingFee = number_format($shippingFee);

            $body = "
                        <div style=font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;'>
                            <div style='background-color: #fff; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);'>
                                <div style='text-align: center; margin-bottom: 20px;'>
                                    <h1 style='margin: 0; color: #333;'>Your Order Receipt</h1>
                                    <p style='margin: 5px 0; color: #777;'>Thank you for shopping with us!</p>
                                </div>

                                <div>
                                    <p>Dear {$firstname},</p>
                                    <p>Thank you for your payment! We're pleased to inform you that your transaction has been successfully processed.</p>
                                </div>
                                <hr />

                                <div style='display: flex; font-size: 15px;'>
                                    <div style='margin-right: 20px; flex: 1;'>
                                        <p style='color: purple;'><strong>Order Details</strong></p>
                                        <p><strong>Tracking ID:</strong> {$tracking_id}</p>
                                        <p><strong>Transaction ID:</strong> {$transactionId}</p>
                                        <p><strong>Phone number:</strong> {$phoneNumber}</p>
                                    </div>
                                    <div style='flex: 1;'>
                                        <p style='color: purple;'><strong>Shipping Details</strong></p>
                                        <p><strong>Country:</strong> {$country}</p>
                                        <p><strong>State:</strong> {$state}</p>
                                        <p'><strong>City:</strong> {$city}</p>
                                        <p><strong>Address:</strong> {$address}</p>
                                        {$postalCodeSection}
                                        <p><strong>Expected date of delivery:</strong> {$expectedDateOfDelivery}</p>
                                    </div>
                                </div>
                                <hr />
                                <h4 style='font-weight: bold;'>Summary</h4>
                                {$orderSummary}
                                <div style='text-align: right;'>
                                    <p style='color: #333;'>Subtotal: {$currency} {$formattedSubtotal}</p>
                                    <p style='color: #333;'>Shipping Fee: {$currency} {$formattedShippingFee}</p>
                                    <p style='color: #333;'><strong>Total: {$currency} {$formattedTotalPrice}</strong></p>
                                </div>

                                <div style='background: purple; padding: 10px; text-align: center; color: white; margin-top: 20px;'>
                                    <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                                    <p>Thank you for choosing us! We look forward to serving you again.</p>
                                </div>
                            </div>
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
            return $order;
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

            //fetch all fresh pending orders from database
            $newAllPendingOrders = Order::where('status', 'pending')->orderBy('updated_at', 'desc')->get();

            //save fetched orders to cache
            Redis::set('pendingOrders', json_encode($newAllPendingOrders, true));


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
                $currencyClass = new CurrencyController();
                $convertedCurrency = $currencyClass->convertCurrency($item['productPriceInNaira'], $currency);

                $formattedPrice = $currency . ' ' . number_format((float)$convertedCurrency, 2, '.', ',');

                return "
                <div style='padding: 20px; text-align: center; background: #f4f4f4; max-width: 180px;'>
                    <div>
                        <img src='{$item['productImage']}' alt='" . htmlspecialchars($item['productName']) . "' style='width: 80px; height: 80px;'>
                    </div>
                    <div style='text-align: center;'>
                        <h4 style='margin: 0;'>" . htmlspecialchars($item['productName']) . "</h4>
                        <h5 style='margin: 0;'>Length - " . htmlspecialchars($item['lengthPicked']) . "</h5>
                        <h5 style='margin: 0;'>Quantity * " . htmlspecialchars($item['quantity']) . "</h5>
                        <h5 style='margin: 0;'><b>Price:</b> {$formattedPrice}</5>
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
            DB::transaction(function () use($request){
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
                    $currencyClass = new CurrencyController();
                    $convertedCurrency = $currencyClass->convertCurrency($item['price'], $currency);
    
                    $formattedPrice = $currency . ' ' . number_format((float)$convertedCurrency, 2, '.', ',');
    
                    return "
                    <div style='padding: 20px; text-align: center; background: #f4f4f4; max-width: 190px;'>
                        <div>
                            <img src='{$item['productImage']}' alt='" . htmlspecialchars($item['productName']) . "' style='width: 80px; height: 80px;'>
                        </div>
                        <div style='text-align: center;'>
                            <h4 style='margin: 0;'>" . htmlspecialchars($item['name']) . "</h4>
                            <h5 style='margin: 0;'>Length - " . htmlspecialchars($item['lengthPicked']) . "</h5>
                            <h5 style='margin: 0;'>Quantity * " . htmlspecialchars($item['quantity']) . "</h5>
                            <h5 style='margin: 0;'><b>Price:</b> {$formattedPrice}</5>
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
    
            });
            
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
