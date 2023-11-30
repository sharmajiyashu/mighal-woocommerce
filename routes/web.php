<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('forget-password');
});

Route::get('reset-password/{id}',function($id){
    return view('reset-password',compact('id'));
})->name('reset-password');

Route::post('reset_password',[Controller::class,'resetPassword'])->name('reset_password');