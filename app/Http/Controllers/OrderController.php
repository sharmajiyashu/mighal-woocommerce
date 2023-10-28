<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiTokenRequest;
use App\Http\Resources\OrdersResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class OrderController extends Controller
{

    use ApiResponse;

    // function createOrder(ApiTokenRequest $request){
    //     $order_data = [
    //         'billing_address' => [
    //             'first_name' => 'Kaoil',
    //             'last_name' => 'Doe',
    //             'address_1' => '123 Main St',
    //             'city' => 'New York',
    //             'state' => 'NY',
    //             'postcode' => '10001',
    //             'country' => 'US',
    //         ],
    //         'shipping_address' => [
    //             'first_name' => 'Jane',
    //             'last_name' => 'Doe',
    //             'address_1' => '456 Shipping Ave',
    //             'city' => 'Los Angeles',
    //             'state' => 'CA',
    //             'postcode' => '90001',
    //             'country' => 'US',
    //         ],
    //         'line_items' => [
    //             [
    //                 'product_id' => 14,
    //                 'quantity' => 5,
    //             ],
    //             [
    //                 'product_id' => 14,
    //                 'quantity' => 5,
    //             ],
    //         ],
    //         'payment_method' => 'cod',
    //         'customer_id' => 7, // Replace with the actual customer's ID
    //         'shipping_lines' => [
    //             [
    //                 'method_id' => 'flat_rate',
    //                 'method_title' => 'Flat Rate',
    //                 'total' => '00.00',
    //             ],
    //         ],
    //         'status' => 'pending',
    //     ];

    //     $token = $request->token;
    //     $woocommerceUrl = env('woocommerce_url');
    //     $consumerKey = env('consumer_key');
    //     $consumerSecret = env('consumer_secret');
    //     $credentials = base64_encode("$consumerKey:$consumerSecret");

    //     $response = Http::withHeaders([
    //         // 'Authorization' => 'Bearer ' . $token,
    //         'Authorization' => 'Basic ' . $credentials,
    //     ])->post("$woocommerceUrl/wp-json/wc/v3/orders",$order_data);

    //     return $response;
    //     if ($response->successful()) {
    //         $tokenData = $response->json();
    //         return $this->sendSuccess('Customer login successfully',$tokenData);
    //     } else {
    //         return $this->sendFailed('Login failed',);
    //     }
    // }


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
            ])->get("$woocommerceUrl/wp-json/wc/v3/orders?customer=7");
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



}
