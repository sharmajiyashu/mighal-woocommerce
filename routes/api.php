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

Route::get('get_user_cart',[Controller::class,'getUserCartDetails']);
Route::post('register_user',[Controller::class,'registerUser']);
Route::post('login',[Controller::class,'loginUser']);
Route::get('user_detail',[Controller::class,'getUserDetails']);
Route::get('cart_checkout',[Controller::class,'cartCheckout']);
Route::get('categories',[Controller::class,'getCategories']);
Route::get('products',[Controller::class,'getProducts']);
Route::get('category_products',[Controller::class,'getCategoryProducts']);
Route::get('product_detail',[Controller::class,'productDetail']);

Route::get('home_details',[Controller::class,'getHomeSchreen']);


// Route::get('create_order',[OrderController::class,'createOrder']);
Route::get('orders',[OrderController::class,'getOrders']);
Route::get('order_detail',[OrderController::class,'getOrderDetail']);
Route::get('add_product_in_Cart',[CartController::class,'addInCart']);
Route::get('create_order',[OrderController::class,'create_order']);
