<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
//use \App\Mail\PotrditevRacuna;

class orderController extends BaseController
{

    public function orders(Request $req) {      
        $auth = $req->header("auth");
        $user = (new authController)->getUser($auth);
        if (!$user) {
            return json_encode(["message" => "Unauthorized."]);
        }
        if ($user->type == "customer") {
            $orders = DB::table("orders")->where("id_user", $user->id_user)->where("finished", 1)->orderBy("created", "desc")->get();
            return json_encode($orders);
        }
        else if ($user->type == "seller") {
            $orders = DB::table("orders")->where("finished", 1)->orderBy("created", "desc")->get();
            return json_encode($orders);
        }
        
        return json_encode(["message" => "Unauthorized."]);
    }

    public function getOrder($id) {
        $order = DB::table("orders")->where("id_order", $id)->first();
        $products = DB::table("in_order")
                    ->join("products", "in_order.id_product", "=", "products.id_product")
                    ->where("id_order", $id)
                    ->select("products.id_product", "products.name", "in_order.price", "in_order.num_products")
                    ->get();
        foreach ($products as $product) {
            $id_product = $product->id_product;
            $images = DB::table("images")->select("path")->where("id_product", $id_product)->get();
            $product->images = $images;
            // array_push($results, ["product" => $product, "images" => $images]);
        }
        // return json_encode($results);
        return json_encode(
            [
                "order" => $order,
                "products" => $products
            ]
        );
    }


    public function updateCart(Request $req) {
        $auth = $req->header("auth");
        $type = (new authController)->getUserType($auth);
        $id_product = $req->input("id_product");
        $num_products = $req->input("num_products");
        $finished = $req->input("finished");
        $add = $req->input("add");

        if ($type == "customer") {
            if ($finished) {
                $latest = DB::table("orders")->where("status", 0)->where("finished", 0)->where("processed", 0)->orderBy("created", "desc")->update(["finished" => 1])->first();   
                return json_encode(["message" => "Order added to queue."]);                 
            }
            else if ($id_product && is_int($num_products)) {
                $latest = DB::table("orders")->where("status", 0)->where("finished", 0)->where("processed", 0)->orderBy("created", "desc")->first();                       
                if ($num_products > 0) {
                    if ($add) {
                        $exist = DB::table("in_order")
                            ->where("id_order", $latest->id_order)
                            ->where("id_product", $id_product)
                            ->increment("num_products", $num_products);
                            if (!$exist) {
                                //If new to cart
                                $product = DB::table("products")->where("id_product", $id_product)->first();
                                $in_order = DB::table("in_order")->insert([
                                    "id_order" => $latest->id_order,
                                    "id_product" => $id_product,
                                    "num_products" => $num_products,
                                    "price" => $product->price,
                                ]);
                                return json_encode(["message" => $product->name . " added to cart."]);
                            }
                            else {
                                return json_encode(["message" => $num_products." items added to cart."]);
                            }                 
                    }
                    else {
                        $exist = DB::table("in_order")
                        ->where("id_order", $latest->id_order)
                        ->where("id_product", $id_product)
                        ->update(["num_products" => $num_products]);

                        if (!$exist) {
                            //If new to cart
                            $product = DB::table("products")->where("id_product", $id_product)->first();
                            $in_order = DB::table("in_order")->insert([
                                "id_order" => $latest->id_order,
                                "id_product" => $id_product,
                                "num_products" => $num_products,
                                "price" => $product->price,
                            ]);
                            return json_encode(["message" => $product->name . " added to cart."]);
                        }
                        else {
                            return json_encode(["message" => "Cart updated."]);
                        }
                    }
          
                }
                else {
                    //has to be removed from cart
                    $removed = DB::table("in_order")
                    ->where("id_order", $latest->id_order)
                    ->where("id_product", $id_product)
                    ->delete();
                    return json_encode(["message" => "Item removed."]);
                }
            }
        }
        return json_encode(["message" => "Unauthorized."]);
    }

    public function updateOrder(Request $req) {
        $auth = $req->header("auth");
        $type = (new authController)->getUserType($auth);
        $id_order = $req->input("id_order");
        $status = $req->input("status");
        $processed = $req->input("processed");
        if ($type == "seller") {
            $order = DB::table("orders")->where("id_order", $id_order)
                ->update([
                    "status" =>  $status,
                    "processed" =>  $processed
                ]);
            return json_encode(["message" => "Order updated."]);
        }
        return json_encode(["message" => "Unauthorized."]);
    }

    public function getLatestOrder(Request $req) {
        $auth = $req->header("auth");

        $type = (new authController)->getUserType($auth);
        if ($type == "customer") {
            //change to filter latest
            $latest = DB::table("orders")->where("status", 0)->where("finished", 0)->where("processed", 0)->orderBy("created", "desc")->first();
            $products = DB::table("in_order")
                    ->join("products", "in_order.id_product", "=", "products.id_product")
                    ->where("id_order", $latest->id_order)
                    ->select("products.id_product", "products.name", "in_order.price", "in_order.num_products")
                    ->get();

            foreach ($products as $product) {
                $id_product = $product->id_product;
                $images = DB::table("images")->select("path")->where("id_product", $id_product)->get();
                $product->images = $images;
            }
            return json_encode(
                [
                    "order" => $latest,
                    "products" => $products
                ]
            );
        }
        return json_encode(["message" => "Unauthorized"]);
    }

    public function finishOrder(Request $req) {
        $auth = $req->header("auth");     
        $user = (new authController)->getUser($auth);
        if ($user->type == "customer") {
            $id_order = $req->input("id_order");
            //check if not empty
            $in_order = DB::table("in_order")->where("id_order", $id_order)->first();
            if(!$in_order) {
                return json_encode(["message" => "Order is empty."]);
            }

            $order = DB::table("orders")->where("id_order", $id_order)->update(["finished" => 1]);
            $neworder = DB::table("orders")->insertGetId([
                        "id_user" => $user->id_user,
                        "status" => 0,
                        "finished" => 0,
                        "processed" => 0,
                    ]);
            return json_encode(["message" => "Purchase complete."]);
        }
        return json_encode(["message" => "Unauthorized."]);
    }

    public function newOrder(Request $req) {
        $auth = $req->input("auth");
        $id_user = (new authController)->getUserByType($auth, "customer");
        if ($id_user) {
            $id_product = $req->input("id_product");

            $product = DB::table("products")->where("id_product", $id_product)->first();
            $id_order = DB::table("orders")->insertGetId([
                    "id_user" => $id_user,
                    "confirmed" => false,
                    "cancelled" => false,
                    "processed" => false,
                    "price_sum" => $product->price,
                ]);
            $in_order = DB::table("in_order")->insert([
                    "id_order" => $id_order,
                    "id_product" =>$id_product
                ]);

            return json_encode(["id_order" => $id_order]);
        }
        return json_encode(["message" => "Unauthorized."]);
    }

    public function addProduct(Request $req) {
        $auth = $req->input("auth");
        $id_user = (new authController)->getUserByType($auth, "customer");
        if ($id_user) {
            $id_product = $req->input("id_product");
            $id_order = $req->input("id_order");
            $product = DB::table("products")->where("id_product", $id_product)->first();
            $order= DB::table("orders")
                    ->where("id_order", $id_order)
                    ->where("id_user", $id_user)
                    ->increment("price_sum", $product->price);
            $in_order = DB::table("in_order")->insert([
                    "id_order" => $id_order,
                    "id_product" =>$id_product
                ]);

            return json_encode(["id_order" => $id_order]);
        }
        return json_encode(["message" => "Unauthorized."]);
    }

    public function removeProduct(Request $req) {
        $auth = $req->input("auth");
        $id_user = (new authController)->getUserByType($auth, "customer");
        if ($id_user) {
            $id_product = $req->input("id_product");
            $id_order = $req->input("id_order");
            $product = DB::table("products")->where("id_product", $id_product)->first();
            $order = DB::table("orders")
                    ->where("id_order", $id_order)
                    ->where("id_user", $id_user)
                    ->decrement("price_sum", $product->price);
            $first =  DB::table("in_order")
                    ->where("id_order", $id_order)
                    ->where("id_product", $id_product)
                    ->first();
            $in_order = DB::table("in_order")
                    ->where("id_in_order", $first->id_in_order)
                    ->delete();

            return json_encode(["id_order" => $id_order]);
        }
        return json_encode(["message" => "Unauthorized."]);
    }



}
