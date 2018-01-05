<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use \App\Order;
use \App\User;
use \App\Auth;
use \App\Product;
//use \App\Mail\PotrditevRacuna;

class orderController extends BaseController
{

    public function testOrder(Request $req) {
        $token = "sOhuTrvKbxUUkKC9PIzMHljkFm4yhwzpTp8mOMlnoUywPr3z7nuMfGQgcuxI";
        $profile = Auth::find($token)->user->profile();
        // $orders = Order::cart(2);
        return response()->json($profile);
        // return json_encode([
        //     "profile" => $profile,
        //     ]);
    }

    public function orders(Request $req) {      
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":              
                return response()->json(Order::history($auth->user->id_user));
                break;
            case "seller":
                return response()->json(Order::finished());
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function cart(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":              
                $cart = Order::cart($auth->user->id_user);
                return response()->json(Order::find($cart->id_order)->withProducts());
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function updateOrder(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "seller":              
                $id_order = $req->input("id_order");
                $processed = $req->input("processed");
                $status = $req->input("status");
                $verified = $this->verifyFields(["processed" => $processed, "status" => $status]);
                if ($verified != 1) {
                    return response()->json("Bad input: ".$verified, 400);
                }
                $order = Order::where("id_order", $id_order)->update([
                    "processed" => $processed,
                    "status" => $status,
                ]);
                return response()->json("Order updated");
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
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
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":   
                $cart = Order::cart($auth->user->id_user);        
                $id_product = $req->input("id_product");
                $num_products = $req->input("num_products");
                $add = $req->input("add");
                //Might remove
                $finished = $req->input("finished");
                if ($finished) {
                    $cart->finished = true;
                    $cart->save();
                    return response()->json("Order added to queue.");
                }
                if ($num_products <= 0) {
                    $cart->removeProduct($id_product);
                    return response()->json("Item removed.");
                }
                else {
                    $item = $cart->hasProduct($id_product);
                    if ($item) {
                        $cart->updateProductVolume($item, $num_products, $add);
                        return response()->json("Volume updated.");
                    }
                    else {
                        $cart->addNewProduct($id_product, $num_products);
                        return response()->json("Item added.");
                    }
                }
                return response()->json();
                break;
            default:
                return response()->json("Unauthorized", 401);
        }

    }

    public function getLatestOrder(Request $req) {
        $auth = $req->header("auth");

        $user = (new authController)->getUser($auth);
        if ($user->type == "customer") {
            //change to filter latest
            $latest = DB::table("orders")->where("id_user", $user->id_user)->where("status", 0)->where("finished", 0)->where("processed", 0)->orderBy("created", "desc")->first();
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
        return json_encode([
            "code" => 403,
            "message" => "Unauthorized"
            ]);
    }

    public function finishOrder(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":              
                $cart = Order::cart($auth->user->id_user);
                $order = Order::find($cart->id_order);
                if ($order->products->count() > 0) {
                    $order->finished = true;
                    $order->save();
                    $newOrder = new Order;
                    $newOrder->id_user = $auth->user->id_user;
                    $newOrder->finished = 0;
                    $newOrder->status = 0;
                    $newOrder->processed = 0;
                    $newOrder->save();
                    return response()->json("Order complete");
                }
                return response()->json("Order doesn't have any products.", 400);
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
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
        return json_encode([
            "code" => 403,
            "message" => "Unauthorized."
            ]);
    }


    public function verifyFields($fields) {

        foreach ($fields as $key => $val) {
            if($val == null){
                return $key;
            }
            switch ($key) {
                case "processed":
                    if ($val == 0 || $val == 1) {
                        continue;
                    }
                    else {
                        return $key;
                    }
                    break;
                case "status":
                    if ($val == 0 || $val == 1 || $val == -1) {
                        continue;
                    }
                    else {
                        return $key;
                    }
                    break;
            }
        }
        return true;
    
    }

}
