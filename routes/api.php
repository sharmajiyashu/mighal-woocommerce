<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register_user',[Controller::class,'registerUser']);
Route::post('login',[Controller::class,'loginUser']);
Route::get('user_detail',[Controller::class,'getUserDetails']);
Route::get('categories',[Controller::class,'getCategories']);
Route::get('products',[Controller::class,'getProducts']);
Route::get('category_products',[Controller::class,'getCategoryProducts']);
Route::get('product_detail',[Controller::class,'productDetail']);

Route::get('home_details_1',[Controller::class,'getHomeSchreen_1']);
Route::get('home_details_2',[Controller::class,'getHomeSchreen_2']);
Route::get('home_details_3',[Controller::class,'getHomeSchreen_3']);

Route::get('orders',[OrderController::class,'getOrders']);
Route::get('order_detail',[OrderController::class,'getOrderDetail']);
Route::post('create_order',[OrderController::class,'createOrder']);

Route::post('add_to_cart',[CartController::class,'addInCart']);
Route::post('add_to_cart_all',[CartController::class,'addMultipleProduct']);
Route::get('get_user_cart',[CartController::class,'getUserCartDetails']);
Route::post('remove_cart_item',[CartController::class,'removeCartItem']);
Route::get('countaries',[Controller::class,'getCountries']);
Route::get('get_state',[Controller::class,'getState']);
Route::post('apply_coupon',[CartController::class,'applyCoupon']);
Route::post('get_related_product_ids',[Controller::class,'related_products_id']);
Route::post('update_shipping_address',[CartController::class,'updateShippingAddress']);
Route::get('search_products',[Controller::class,'searchProducts']);
