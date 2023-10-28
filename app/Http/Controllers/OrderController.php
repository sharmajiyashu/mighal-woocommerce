<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiTokenRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrdersResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class OrderController extends Controller
{

    use ApiResponse;

    function createOrder(ApiTokenRequest $request){
        $woocommerceUrl = env('woocommerce_url');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $request->token, // Replace with your JWT token
        ])->get("$woocommerceUrl/wp-json/wc/store/cart/");        
        if ($response->successful()) {
            $nonce = $response->header('X-WC-Store-API-Nonce');
            $cartDetails = $response->json();
            $data = $cartDetails['items'];
            if(!empty($data)) {
                $cart_items = [];
                $cart_key = [];
                foreach($data as $key => $val){
                    $cart_items[] = [
                        'product_id' => $val['id'],
                        'quantity' => $val['quantity'],
                    ];
                    $cart_key[] = $val['key'];
                }
    
                $order_data = [
                    'billing' => [
                        'first_name' => 'Kaoil',
                        'last_name' => 'Doe',
                        'address_1' => '123 Main St',
                        'city' => 'New York',
                        'state' => 'NY',
                        'postcode' => '10001',
                        'country' => 'US',
                    ],
                    'shipping' => [
                        'first_name' => 'Jane',
                        'last_name' => 'Doe',
                        'address_1' => '456 Shipping Ave',
                        'city' => 'Los Angeles',
                        'state' => 'CA',
                        'postcode' => '90001',
                        'country' => 'US',
                    ],
                    'line_items' => $cart_items,
                    'payment_method' => 'cod',
                    'customer_id' => 7219, // Replace with the actual customer's ID
                    'shipping_lines' => [
                        [
                            'method_id' => 'flat_rate',
                            'method_title' => 'Flat Rate',
                            'total' => '00.00',
                        ],
                    ],
                    'status' => 'pending',
                ];
                $token = $request->token;
                $woocommerceUrl = env('woocommerce_url');
                $consumerKey = env('consumer_key');
                $consumerSecret = env('consumer_secret');
                $credentials = base64_encode("$consumerKey:$consumerSecret");
        
                $response = Http::withHeaders([
                    // 'Authorization' => 'Bearer ' . $request->token,
                    'Authorization' => 'Basic ' . $credentials,
                ])->post("$woocommerceUrl/wp-json/wc/v3/orders",$order_data);
        
                if ($response->successful()) {
                    foreach($cart_key as $item){
                        $delet_cart_response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $request->token,
                            'Nonce' =>$nonce,
                        ])->post("$woocommerceUrl/wp-json/wc/store/cart/remove-item", [
                            'key' => $item,
                        ]);
                    }
                    $tokenData = $response->json();
                    return $this->sendSuccess('Order create successfully',$tokenData);
                } else {
                    return $this->sendFailed('Order create failour',);
                }
            }else{
                return $this->sendFailed('Cart value is empty',);
            }
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }

        // return $cartDetails;

        
        
        


       
        
    }


    public function getOrders(ApiTokenRequest $request){
        $woocommerceUrl = env('woocommerce_url');
        $token = $request->token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("$woocommerceUrl/wp-json/wp/v2/users/me");

        if ($response->successful()) {
            $user = $response->json();
            $customer_id =  $user['id'];
            $consumerKey = env('consumer_key');
            $consumerSecret = env('consumer_secret');
            $credentials = base64_encode("$consumerKey:$consumerSecret");

            $response = Http::withHeaders([
                // 'Authorization' => 'Bearer ' . $token,
                'Authorization' => 'Basic ' . $credentials,
            ])->get("$woocommerceUrl/wp-json/wc/v3/orders?customer=7219");
            // return $response;
            $data = $response->json();
            $collect = OrdersResource::collection($data);
            return $this->sendSuccess('Orders fetch successfully',$collect);
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }

    public function getOrderDetail(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $order_id = $request->order_id;
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $token,
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/orders/$order_id");
        
        if ($response->successful()) {
            $data = new OrdersResource($response->json());
            return $this->sendSuccess('Orders fetch successfully',$data);
            echo $response;die;
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }


    // public function createOrder(Request $request){

    //     $woocommerceUrl = env('woocommerce_url');
    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $request->token, // Replace with your JWT token
    //     ])->get("$woocommerceUrl/wp-json/wc/store/cart/");        
    //     if ($response->successful()) {
    //         $cartDetails = $response->json();
    //         $data = new CartResource($cartDetails);
    //         // return $this->sendSuccess('cart fetch successfully',$data);
    //     } else {
    //         $data = $response->json();
    //         return $this->sendFailed($data['message'],);
    //     }

    //     $cart_items =$data['items'];
    //     $order_data = [
    //         'payment_method' => 'cod', // Replace with your desired payment method
    //         'payment_method_title' => 'Your Payment Method Title',
    //         'set_paid' => true, // You can set it to true if you want to mark the order as paid immediately
    //         'billing' => [
    //             'first_name' => 'John', // Customer's first name
    //             'last_name' => 'Doe', // Customer's last name
    //             'address_1' => '123 Main St', // Customer's billing address
    //             'city' => 'City',
    //             'state' => 'State',
    //             'postcode' => '12345',
    //             'country' => 'US',
    //             'email' => 'customer@example.com',
    //             'phone' => '1234567890',
    //         ],
    //         'shipping' => [
    //             'first_name' => 'John', // Customer's first name
    //             'last_name' => 'Doe', // Customer's last name
    //             'address_1' => '123 Shipping St', // Customer's shipping address
    //             'city' => 'City',
    //             'state' => 'State',
    //             'postcode' => '12345',
    //             'country' => 'US',
    //         ],
    //         'line_items' => $cart_items, // An array of items from the cart
    //     ];


    //     $order_response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $request->token,
    //     ])->post("$woocommerceUrl/wp-json/wc/v3/orders", $order_data);
            
    //     return $order_response;


    //     if ($order_response->successful()) {
    //         // The order has been created successfully.
    //         $order = $order_response->json();

            
    //         // Now, you can perform additional actions like payment processing, email notifications, etc.
    //         // Make sure to handle the payment and other order-related tasks as needed.
    //     } else {
    //         // Handle the case where order creation failed.
    //         $error_message = $order_response->json();
    //         // Log or return an error message.
    //     }





    // }



}
