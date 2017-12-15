<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class productController extends BaseController
{

    public function products(Request $req) {
        $results = [];
        if ($req->header("auth")){
            $auth = $req->header("auth");
            $type = (new authController)->getUserType($auth);
            if($type == "seller") {
                $products = DB::table("products")->get();
                foreach ($products as $product) {
                    $id_product = $product->id_product;
                    $images = DB::table("images")->select("path")->where("id_product", $id_product)->get();
                    array_push($results, ["product" => $product, "images" => $images]);
                }
                return json_encode($results);
            }
        }
        $products = DB::table("products")->select("id_product", "name", "price", "rating", "num_ratings")->where("active", 1)->get();
        foreach ($products as $product) {
            $id_product = $product->id_product;
            $images = DB::table("images")->select("path")->where("id_product", $id_product)->get();
            array_push($results, ["product" => $product, "images" => $images]);
        }
        return json_encode($results);
    }

    public function rateProduct(Request $req) {
        if ($req->header("auth")){
            $auth = $req->header("auth");
            $rating = $req->input("rating");
            $id_product = $req->input("id_product");
            $id_user = (new authController)->getUserByType($auth, "customer");
            if($id_user) {
                $product = DB::table("products")
                        ->where("id_product", $id_product)
                        ->first();
                $newnumratings = $product->num_ratings + 1;
                $newrating = ($product->num_ratings * $product->rating + $rating) / $newnumratings;
                
                $updated = DB::table("products")
                        ->where("id_product", $id_product)
                        ->update(["rating" => $newrating, "num_ratings" => $newnumratings]);
                return json_encode(["message" => "Rating has been updated"]);
            }
        }
        return json_encode(["message" => "Unauthorized"]);
    }

    public function getProduct(Request $req, $id) {
        if ($req->header("auth")){
            $auth = $req->header("auth");
            $type = (new authController)->getUserType($auth);
            if($type == "seller") {
                $product = DB::table("products")->where("id_product", $id)->first();
                $images = DB::table("images")->select("path")->where("id_product", $id)->get();
                return json_encode(["product" => $product, "images" => $images]);
            }
        }
        $product = DB::table("products")->select("id_product", "name", "price", "rating", "num_ratings")->where("id_product", $id)->where("active", 1)->first();
        $images = DB::table("images")->select("path")->where("id_product", $id)->get();
        return json_encode(["product" => $product, "images" => $images]);
    }

    public function updateProduct(Request $req) {
        $auth = $req->header("auth");
        $idProduct = $req->input("id_product");

        $type = (new authController)->getUserType($auth);
        if ($type == "seller") {
            $active = $req->input("active");
            $name = $req->input("name");
            $price = $req->input("price");
            $update = DB::table("products")->where('id_product', $idProduct)
                    ->update([
                        'active' => $active,
                        "name" => $name,
                        "price" => $price,
                        ]);
            return json_encode(["message" => "Product has been updated."]);
        }
        else {
            return json_encode(["message" => "Action not permitted."]);
        }
    }





}
