<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductInCartRequest;
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




}
