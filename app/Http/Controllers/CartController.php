<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductInCartRequest;
use App\Http\Requests\ApiCustomerIDRequest;
use App\Http\Requests\ApiTokenRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{

    public function getUserCartDetails(ApiTokenRequest $request)
    {
        $woocommerceUrl = env('woocommerce_url');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $request->token, // Replace with your JWT token
        ])->get("$woocommerceUrl/wp-json/wc/store/cart/");        
        if ($response->successful()) {
            $cartDetails = $response->json();
            $data = new CartResource($cartDetails);
            return $this->sendSuccess('cart fetch successfully',$data);
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }
    
    function addInCart(AddProductInCartRequest $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $product_data = [
            'id' => $request->product_id,
            'quantity' => $request->quantity,
            'variation_id' => $request->variation_id,
        ];
        $token = $request->token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token, // Replace with your JWT token
        ])->get("$woocommerceUrl/wp-json/wc/store/cart/");     
        if ($response->successful()) {
            $nonce = $response->header('X-WC-Store-API-Nonce');
            $cartDetails = $response->json();
            $add_to_cart_response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Nonce' =>$nonce,
            ])->post("$woocommerceUrl/wp-json/wc/store/cart/add-item",$product_data);
            if ($add_to_cart_response->successful()) {
                $data = new CartResource($add_to_cart_response->json());
                return $this->sendSuccess('Product added successfully in cart',$data);
            } else {
                $data = $add_to_cart_response->json();
                return $this->sendFailed($data['message'],);
            }
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }


    function removeCartItem(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $token = $request->token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token, // Replace with your JWT token
        ])->get("$woocommerceUrl/wp-json/wc/store/cart/");     
        if ($response->successful()) {   
            $nonce = $response->header('X-WC-Store-API-Nonce');
            $delet_cart_response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $request->token,
                'Nonce' =>$nonce,
            ])->post("$woocommerceUrl/wp-json/wc/store/cart/remove-item", [
                'key' => $request->key,
            ]);
            if ($delet_cart_response->successful()) {  
                $data = new CartResource($delet_cart_response->json());
                return $this->sendSuccess('Product Remove from cart',$data);
            } else {
                $data = $delet_cart_response->json();
                return $this->sendFailed($data['message'],);
            }
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }

    }

    function updateShippingAddress(ApiCustomerIDRequest $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customer_id = $request->customer_id;
        $data = [
            'first_name' => isset($request->first_name) ? $request->first_name :'',
            'company' => isset($request->company) ? $request->company :'',
            'last_name' => isset($request->last_name) ? $request->last_name :'',
            'address_1' => isset($request->address_1) ? $request->address_1 :'',
            'address_2' => isset($request->address_2) ? $request->address_2 :'',
            'city' => isset($request->city) ? $request->city :'',
            'state' => isset($request->state) ? $request->state :'',
            'postcode' => isset($request->postcode) ? $request->postcode :'',
            'country' => isset($request->country) ? $request->country :'',
            'email' => isset($request->email) ? $request->email :'',
            'phone' => isset($request->phone) ? $request->phone :'',
        ];
        $updated_billing_address = [
            'billing' => $data,
            'shipping' => $data
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->put("$woocommerceUrl/wp-json/wc/v3/customers/$customer_id", $updated_billing_address);
        if ($response->successful()) {
            return $this->sendSuccess('Billing address update successfully',$response->json());
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }

    function addMultipleProduct(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $token = $request->token;
        $data = $request->all();
        $success_added = 0;
        $error_msg = [];
        foreach($data as $key=>$val){
            if(!empty($val['product_id']) && !empty($val['quantity'])){
                $product_data = [
                    'id' => isset($val['product_id']) ? $val['product_id'] :'',
                    'quantity' => isset($val['quantity']) ? $val['quantity'] :'',
                    'variation_id' => isset($val['variation_id']) ? $val['variation_id'] :'',
                ];
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token, // Replace with your JWT token
                ])->get("$woocommerceUrl/wp-json/wc/store/cart/");     
                if ($response->successful()) {
                    $nonce = $response->header('X-WC-Store-API-Nonce');
                    $cartDetails = $response->json();
                    $add_to_cart_response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Nonce' =>$nonce,
                    ])->post("$woocommerceUrl/wp-json/wc/store/cart/add-item",$product_data);
                    if ($add_to_cart_response->successful()) {
                        $data = new CartResource($add_to_cart_response->json());
                        $success_added ++;
                    } else {
                        $data = $add_to_cart_response->json();
                        $error_msg[] = $data['message'];
                    }
                } else {
                    $data = $response->json();
                    $error_msg[] = $data['message'];
                }
            }
        }
        return $this->sendSuccess($success_added.' product added in cart',[
            'total_added' => $success_added,
            'error' => $error_msg
        ]);
    }

}
