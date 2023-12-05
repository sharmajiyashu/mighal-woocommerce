<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Http\Requests\ApiTokenRequest;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\CountriesResource;
use App\Http\Resources\ProductsResource;
use App\Http\Resources\StateResource;
use App\Mail\ForgetPassword;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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


    public function customerDetail(Request $request)
    {
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customerId = $request->customer_id; // Replace with the actual customer ID you want to retrieve

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/customers/$customerId");
        if ($response->successful()) {
            $user = $response->json();
            return $this->sendSuccess('Customer data fetch successfully',$user);
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }

    function updateCustomerID(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customerId = $request->customer_id; // Replace with the actual customer ID you want to retrieve

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/customers/$customerId");
        if ($response->successful()) {
            $customerData = $response->json();
            // Add or update the meta key and value
            $customerData['meta_data'][] = [
                'key' => 'payment_customer_id',
                'value' => "$request->payment_customer_id",
            ];
            // Now, update the customer with the new meta data
            $updateResponse = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
            ])->put("$woocommerceUrl/wp-json/wc/v3/customers/$customerId", $customerData);
        
            if ($updateResponse->successful()) {
                return $this->sendSuccess('Meta data added successfully', $updateResponse->json());
            } else {
                $data = $updateResponse->json();
                return $this->sendFailed($data['message']);
            }
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message']);
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
        if ($response->successful()) {
            $tokenData = $response->json();
            $token = $tokenData['token'];
            $get_user_detail_res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get("$woocommerceUrl/wp-json/wp/v2/users/me");
            if ($get_user_detail_res->successful()) {
                $user = $get_user_detail_res->json();
                $tokenData['customer_id'] = $user['id'];
                return $this->sendSuccess('Customer data fetch successfully',$tokenData);
            } else {
                $data = $get_user_detail_res->json();
                return $this->sendFailed($data['message'],);
            }
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
        ])->get("$woocommerceUrl/wp-json/wc/v3/products",[
            'category' => $category_id,
            'per_page' => 100,
            'page' => 1,
        ]);
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = ProductsResource::collection($tokenData);
            return $this->sendSuccess('Product fetch successfully',['category_id' => $category_id ,'items' => $data]);
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
        // return $response;
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = new ProductsResource($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    function getHomeSchreen_1(){
        $category_data = [
            'id' => 473,
            'name' => "Mix n Match",
            'image' => ['src' => 'https://mighzalalarab.com/wp-content/uploads/2023/09/O1CN01F5Qaff1uoWWLraCNT_2212706806084-0-cib.jpg'],
        ];

        $category_data = self::get_category_product($category_data);
        return $this->sendSuccess('Home 1 fetch successfully',$category_data);
    }

    function getHomeSchreen_2(){
        $category_data = [
            'id' => 607,
            'name' => "DESIGNER PICKS",
            'image' => ['src' => 'https://mighzalalarab.com/wp-content/uploads/2023/09/O1CN01W8Lfeq1uoWSQ1yGHN_2212706806084-0-cib.jpg'],
        ];
        $category_data = self::get_category_product($category_data);
        return $this->sendSuccess('Home 2 fetch successfully',$category_data);
    }

    function getHomeSchreen_3(){
        $category_data = [
            'id' => 443,
            'name' => "All Bracelet",
            'image' => ['src' => 'https://mighzalalarab.com/wp-content/uploads/2023/08/DSC_0579.jpg'],
        ];
        $category_data = self::get_category_product($category_data);
        return $this->sendSuccess('Home 3 fetch successfully',$category_data);
    }

    function get_category_product($category_data){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        // $category_data = $response->json();
        $category_id = $category_data['id'];
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
        
    }

    // function get_category_product($category_id){
    //     $woocommerceUrl = env('woocommerce_url');
    //     $consumerKey = env('consumer_key');
    //     $consumerSecret = env('consumer_secret');
    //     $credentials = base64_encode("$consumerKey:$consumerSecret");
    //     $response = Http::withHeaders([
    //         'Authorization' => 'Basic ' . $credentials,
    //     ])->get("$woocommerceUrl/wp-json/wc/v3/products/categories/$category_id");
    //     if ($response->successful()) {
    //         $category_data = $response->json();
    //         $products_data = Http::withHeaders([
    //             'Authorization' => 'Basic ' . $credentials,
    //         ])->get("$woocommerceUrl/wp-json/wc/v3/products?category=$category_id&per_page=8");
    //         if ($products_data->successful()) {
    //             $tokenData = $products_data->json();
    //             $product_data = ProductsResource::collection($tokenData);
    //         } else {
    //             $product_data = [];
    //         }
    //         $category_data['product_data'] = $product_data;
    //         $category_data =  new CategoriesResource($category_data);
    //         return $category_data;
    //     } else {
    //         return $this->sendFailed('Login failed',);
    //     }
    // }


    function getCountries(){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/data/countries");   

        // return $response;
        if ($response->successful()) {
            $data = CountriesResource::collection($response->json());
            return $this->sendSuccess('Countries fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    function getState(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/data/continents/$request->country_code");   
        if ($response->successful()) {
            $data = new StateResource($response->json());
            return $this->sendSuccess('Countries fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }

    public function related_products_id(Request $request)
    {
        $productIds = $request->all();
        $productIdsQueryParam = implode(',', $productIds);
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products",[
            'per_page' => 100,
            'page' => 1,
            'include' => $productIdsQueryParam,
        ]);
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = ProductsResource::collection($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }


    public function searchProducts(Request $request){
        $productIds = $request->all();
        $productIdsQueryParam = implode(',', $productIds);
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/products",[
            'per_page' => 10,
            'page' => 1,
            'search' => $request->name,
        ]);
        if ($response->successful()) {
            $tokenData = $response->json();
            $data = ProductsResource::collection($tokenData);
            return $this->sendSuccess('Product fetch successfully',$data);
        } else {
            return $this->sendFailed('Login failed',);
        }
    }


    function forgetPassword(Request $request){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->get("$woocommerceUrl/wp-json/wc/v3/customers/",[
            'email' => $request->email,
            'per_page' => 10,
            'page' => 1,
        ]);
        if ($response->successful()) {
            $data = $response->json();
            $customer_data = '';
            foreach($data as $values){
                if(strtolower($values['email']) == strtolower($request->email)){
                    $customer_data = $values;
                }
            }
            if(!empty($customer_data)){
                $data = [
                    'name' => $customer_data['first_name'].' '.$customer_data['last_name'],
                    'link' => route('reset-password',$customer_data['id']),
                ];
                Mail::to('jangidkapilyashu@gmail.com')->send(new ForgetPassword($data));
                Mail::to(strtolower($customer_data['email']))->send(new ForgetPassword($data));
                return $this->sendSuccess('Email sent successfully! ',[
                    'name' => $customer_data['first_name'].' '.$customer_data['last_name'],
                    'email' => $customer_data['email'],
                ]);
            }else{
                return $this->sendFailed('You are not register',);
            }
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }

    function changePassword(){
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customer_id = 7219;
        $update_data = [
            'password' => '121212',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->patch("$woocommerceUrl/wp-json/wc/v3/customers/$customer_id", $update_data);
        if ($response->successful()) {
            return $this->sendSuccess('Billing address update successfully',$response->json());
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }

    function resetPassword(Request $request){
        $credentials = $request->validate([
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'user_id' => 'required'
        ]);
        $woocommerceUrl = env('woocommerce_url');
        $consumerKey = env('consumer_key');
        $consumerSecret = env('consumer_secret');
        $credentials = base64_encode("$consumerKey:$consumerSecret");
        $customer_id = $request->user_id;
        $update_data = [
            'password' => $request->password,
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
        ])->patch("$woocommerceUrl/wp-json/wc/v3/customers/$customer_id", $update_data);
        if ($response->successful()) {
            return redirect()->back()->with('success','Password Change Successfully');
        } else {
            $data = $response->json();
            return $this->sendFailed($data['message'],);
        }
    }    


}






