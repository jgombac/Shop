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

use App\Product;
use App\Order;
use App\Auth;
use App\User;
use App\Image;
use App\ProductRating;

class productController extends BaseController
{

    public function products(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json(Product::active());
        }
        switch ($auth->user->type) {
            case "customer":              
                return response()->json(Product::active());
                break;
            case "seller":
                return response()->json(Product::getAll());
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function rateProduct(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":
                $id_product = $req->input("id_product");
                $rating = $req->input("rating");
                if ($rating > 5 || $rating < 1) {
                    return response()->json("Invalid rating: ".$rating, 400);
                }
                $product = Product::find($id_product);
                if(!$product || !$product->active) {
                    return response()->json("Can't rate this product", 400);
                }        
                $id_user = $auth->user->id_user;
                $insert = ProductRating::newRating($id_product, $id_user, $rating);
                if (!$insert){
                    return response()->json("You have already rated this product", 400);
                }
                $rate = Product::find($id_product)->rate($rating);     
                return response()->json(Product::find(1));
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function getProduct(Request $req, $id) {
        $auth = Auth::find($req->header("auth"));
        $product = Product::find($id);
        if(!$auth || $auth->user->type == "customer") {
            if (!$product) {
                return response()->json("Cant view this product", 400);
            }
        }
        return response()->json($product);
    }

    public function updateProduct(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":              
                return response()->json("Unauthorized", 401);
                break;
            case "seller":
                $id_product = $req->input("id_product");
                $name = $req->input("name");
                $price = $req->input("price");
                $active = $req->input("active");
                if (!isset($id_product) || !isset($name)  || !isset($price)  || !isset($active)) {
                    return response()->json("Some parameters are missing", 400);
                }
                $product = Product::find($id_product)->updateProduct($name, $price, $active);
                return response()->json(Product::find($id_product));
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function manageImage(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "customer":              
                return response()->json("Unauthorized", 401);
                break;
            case "seller":
                $id_product = $req->input("id_product");
                $path = $req->input("path");
                $action = $req->input("action");
                if ($id_product || $path || $action) {
                    return response()->json("Some parameters are missing", 400);
                }
                if ($action == "add") {
                    $image == new Image;
                    $image->id_product = $id_product;
                    $image->path = $path;
                    $image->save();
                    return response()->json("Image added");
                }
                else if ($action == "remove") {
                    $removed = Image::where(["id_product" => $id_product, "path" => $path])->delete();
                    return response()->json("Image removed");
                }
                return response()->json("Some parameters are incorrect", 400);
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }





}
