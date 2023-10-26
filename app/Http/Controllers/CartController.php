<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    
    function addInCart(){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customerId = 7;
        $cart_item_data = [
            'product_id' => 14,
            'quantity' => 10,
        ];
        $response = Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $token,
            'Authorization' => 'Basic ' . $credentials,
        ])->post("$woocommerceUrl/wp-json/wc/v1/customers/$customerId/cart",$cart_item_data);

        echo $response;
    }


}
