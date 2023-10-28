<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiCustomerIDRequest;
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

    function createOrder(Request $request){
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
                    'billing' => $cartDetails['shipping_address'],
                    'shipping' => $cartDetails['billing_address'],
                    'line_items' => $cart_items,
                    'payment_method' => 'cod',
                    'customer_id' => $request->customer_id, // Replace with the actual customer's ID
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
    }


    public function getOrders(ApiCustomerIDRequest $request){
        $woocommerceUrl = env('woocommerce_url');
        $customer_id =  $request->customer_id;
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
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }
}
