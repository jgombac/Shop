<?php

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
View::addExtension('html', 'php');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\authController;
use App\Http\Controllers\orderController;

Route::get('/', function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type)
            return view("products", ["type" => ucfirst($type)]);
    }
    return view("products", ["type" => "anon"]);
});

Route::get('/login', function () {
    return view("login");
});

Route::get('/register', function (Request $req) {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type == "admin") {
            return view("registerseller", ['type' => ucfirst($type)]);
        }
        else {
            return view("register", ["type" => ucfirst($type)]);
        }
    }
    return view("register", ["type" => "anon"]);
});

Route::get("/products", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type)
            return view("products", ["type" => ucfirst($type)]);
    }
    return view("products", ["type" => "anon"]);
});

Route::get("/products/{id}", function ($id) {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type)
            return view("productdetails", ["type" => ucfirst($type), "id_product" => $id]);
    }
    return view("productdetails", ["type" => "anon", "id_product" => $id]);
});

Route::get("/sellers", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type == "admin") {
            return view("sellers", ['type' => ucfirst($type)]);
        }
    }
    return view("welcome");
});

Route::get("/customers", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type == "seller") {
            return view("customers", ['type' => ucfirst($type)]);
        }
    }
    return redirect()->route("/");
});

Route::get("/orders", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type) {
            return view("orders", ['type' => ucfirst($type)]);
        }
    }
    return redirect()->route("/");
});

Route::get("/orders/{id}", function ($id) {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type) {
            $order = (new orderController)->getOrder($id);
            return view("orderdetails", ['type' => ucfirst($type), "order" => $order]);
        }
    }
    return redirect()->route("/");
});

Route::get("/profile", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type == "customer") {
            return view("profilecustomer", ['type' => ucfirst($type)]);
        }
        if ($type == "seller" || $type == "admin") {
            return view("profile", ['type' => ucfirst($type)]);
        }
    }
    return redirect()->route("/");

});

Route::get("/cart", function () {
    if (isset($_COOKIE["auth"])){
        $auth = $_COOKIE["auth"];
        $type = (new authController)->getUserType($auth);
        if ($type == "customer") {
            return view("cart", ['type' => ucfirst($type)]);
        }
    }
    return redirect()->route("/");

});
