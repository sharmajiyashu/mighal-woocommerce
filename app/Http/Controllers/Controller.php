<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Http\Requests\ApiTokenRequest;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\ProductsResource;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,ApiResponse;
  
    public function getUserDetails(ApiTokenRequest $request)
    {
        $woocommerceUrl = env('woocommerce_url');
        $token = $request->token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("$woocommerceUrl/wp-json/wp/v2/users/me");

        if ($response->successful()) {
            $user = $response->json();
            return $this->sendSuccess('Customer data fetch successfully',$user);
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }


    public function loginUser(ApiLoginRequest $request)
    {
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            // 'Authorization' => 'Basic ' . $credentials,
        ])->post("$woocommerceUrl/wp-json/jwt-auth/v1/token",$request->validated());
        // return $response;
        if ($response->successful()) {
            $tokenData = $response->json();
            return $this->sendSuccess('Customer login successfully',$tokenData);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    public function registerUser(ApiRegisterRequest $request)
    {
        $data = $request->validated();
        $consumer_key = env('consumer_key');
        $consumer_secret = env('consumer_secret');
        $woocommerceUrl = env('woocommerce_url');
        $base_url = $woocommerceUrl.'/wp-json/wc/v3/customers';
        try {
            $response = Http::withBasicAuth($consumer_key, $consumer_secret)
                ->post($base_url, $data);
            if ($response->successful()) {
                $customer = $response->json();
                return $this->sendSuccess('Customer register successfully',$customer);
            } else {
                $message = $response->json();
                return $this->sendFailed($message['message'],);
            }
        } catch (\Exception $e) {
            return $this->sendFailed($e->getMessage(),);
        }
    }

    public function cartCheckout(Request $request)
    {
        $woocommerceUrl = env('woocommerce_url');
        // Replace with the token you obtained during login
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2hpZ2hmbHlyZWFsZXN0YXRlLmNvbS9uZXciLCJpYXQiOjE2OTgyMDU2NTgsIm5iZiI6MTY5ODIwNTY1OCwiZXhwIjoxNjk4ODEwNDU4LCJkYXRhIjp7InVzZXIiOnsiaWQiOiI0In19fQ.5r6MQe4zGJZXgSHzzJ_uMpJ6sE722MwAN8BPeDaNc_s';

        // Make a GET request to the WooCommerce API with the token in the headers
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get("$woocommerceUrl/wp-json/wc/v3/orders",);

        if ($response->successful()) {
            $user = $response->json();
            dd($user);
        } else {
            // Handle errors here
            dd($response->status(), $response->json());
        }
        
    }

    public function getCategories()
    {
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            ])->get("$woocommerceUrl/wp-json/wc/v3/products/categories", [
                'per_page' => 100,
                'page' => 1,
            ]);
        
        if ($response->successful()) {
            $tokenData = $response->json();
            // print_r($tokenData);die;
            $data = CategoriesResource::collection($tokenData);
            return $this->sendSuccess('category fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    public function getProducts()
    {
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products",[
            'per_page' => 100,
            'page' => 1,
        ]);
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = ProductsResource::collection($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }


    public function getCategoryProducts(Request $request){

        $category_id = $request->category_id;
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products?category=$category_id",[
            'per_page' => 100,
            'page' => 1,
        ]);
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = ProductsResource::collection($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    public function productDetail(Request $request){
        $product_id = $request->product_id;
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products/$product_id");
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = new ProductsResource($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }


    function getHomeSchreen_1(){
        $category_data = self::get_category_product(473);
        return $this->sendSuccess('Home 1 fetch successfully',$category_data);
    }

    function getHomeSchreen_2(){
        $category_data = self::get_category_product(607);
        return $this->sendSuccess('Home 2 fetch successfully',$category_data);
    }

    function getHomeSchreen_3(){
        $category_data = self::get_category_product(443);
        return $this->sendSuccess('Home 3 fetch successfully',$category_data);
    }

    function get_category_product($category_id){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products/categories/$category_id");
        if ($response->successful()) {
            $category_data = $response->json();
            $products_data = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
            ])->get("$woocommerceUrl/wp-json/wc/v3/products?category=$category_id&per_page=8");
            if ($products_data->successful()) {
                $tokenData = $products_data->json();
                $product_data = ProductsResource::collection($tokenData);
            } else {
                $product_data = [];
            }
            $category_data['product_data'] = $product_data;
            $category_data =  new CategoriesResource($category_data);
            return $category_data;
        } else {
            return $this->sendFailed('Login failed',);
        }
    }




}






