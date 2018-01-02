<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post("login", "userController@login");
Route::post("register", "userController@register");

Route::get("profile", "userController@profile");
Route::post("profile", "userController@updateProfile");
Route::post("userupdate", "userController@updateUser");


Route::get("testorder", "orderController@testOrder");
Route::get("testemail", "userController@sendEmail");

Route::get("products", "productController@products");
Route::post("products/update", "productController@updateProduct");
Route::post("products/rate", "productController@rateProduct");
Route::get("products/{id}", "productController@getProduct");


Route::get("orders", "orderController@orders");
Route::post("cart/new", "orderController@newOrder");
Route::post("cart/add", "orderController@addProduct");
Route::post("cart/update", "orderController@updateCart");
Route::post("cart/finish", "orderController@finishOrder");
Route::post("orders/update", "orderController@updateOrder");
Route::post("orders/remove", "orderController@removeProduct");
Route::get("cart", "orderController@cart");
Route::get("orders/{id}", "orderController@getOrder");



Route::get('customers', "userController@customers");
Route::get('sellers', "userController@sellers");

Route::post("admin", "AdminController@login");
Route::post("seller", "SellerController@login");