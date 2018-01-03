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
use File;

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
                return response()->json(Product::getAll());
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
        $product = Product::getProduct($id);
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
                $verified = $this->verifyFields(["active" => $active, "name" => $name, "price" => $price]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }  
                $product = Product::find($id_product)->updateProduct($name, $price, $active);
                return response()->json(Product::find($id_product));
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }

    public function addProduct(Request $req) {
        $auth = Auth::find($req->header("auth"));
        if(!$auth) {
            return response()->json("Unauthorized", 401);
        }
        switch ($auth->user->type) {
            case "seller":

                $photo = $req->file("image");
                $path = $photo->getClientOriginalName();//$req->input("path");
                $name = $req->input("name");
                $price = $req->input("price");
                $active = $req->input("active");
                if (!isset($path) || !isset($name)  || !isset($price)  || !isset($active) || !isset($photo)) {
                    return response()->json("Some parameters are missing", 400);
                }
                $verified = $this->verifyFields(["path" => $path, "active" => $active, "image" => $photo, "name" => $name, "price" => $price]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }  
                $imgfile = $photo->move(public_path("/images"), $path);
                $product = new Product;
                $product->name = $name;
                $product->price = $price;
                if ($active === "true"){
                    $product->active = 1;
                }
                else {
                    $product->active = 0;
                }
                $product->num_ratings = 0;
                $product->rating = 0;
                $product->save();
                $id_product = $product->id_product;
                $image = new Image;
                $image->id_product = $id_product;
                $image->path = $path;
                $image->save();

                return response()->json("Product added");
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
                if ($id_product == null || $path == null || $action == null) {
                    return response()->json("Some parameters are missing", 400);
                }
                if ($action == "add") {
                    $imgfile = $req->file("photo");
                    $path = $imgfile->getClientOriginalName();//$req->input("path");
                    $verified = $this->verifyFields(["path" => $path, "action" => $action, "image" => $imgfile]);
                    if ($verified != 1){
                        return response()->json("Bad input: ".$verified, 400);
                    }  
                    $imgfile->move(public_path("/images"), $path);
                    $image = new Image;
                    $image->id_product = $id_product;
                    $image->path = $path;
                    $image->save();
                    return response()->json("Image added");
                }
                else if ($action == "remove") {
                    try{
                        unlink(public_path('images/'.$path));
                    }catch (\Exception $e) {
                        
                    }
                    $removed = Image::where(["id_product" => $id_product, "path" => $path])->delete();
                    return response()->json("Image removed");
                }
                return response()->json("Some parameters are incorrect", 400);
                break;
            default:
                return response()->json("Unauthorized", 401);
        }
    }


    public function verifyFields($fields) {

        foreach ($fields as $key => $val) {
            if($val == null){
                return $key;
            }
            switch ($key) {
                case "action":
                    if ($val == "add" || $val == "remove") {
                        continue;
                    }
                    else {
                        return $key;
                    }
                    break;
                case "image":
                    if(substr($val->getMimeType(), 0, 5) != 'image') {
                        return $key;
                    }
                    break;
                case "name":
                    if (strlen($val) < 3) {
                        return $key;
                    }
                    break;
                case "path":
                    if (!preg_match("/^[a-zA-Z0-9\_\-]+\.(jpg|png|pneg|jpeg)$/", $val)) {
                        return $key;
                    }  
                    break;
                case "price":
                    if (!preg_match("/^\d+(\,|\.)?\d+?$/", $val)) {
                        return $key;
                    }  
                    break;
                case "active":
                    if ($val != 0 || $val != 1 || $val != "true" || $val != "false") {
                        continue;
                    } 
                    else{
                        return $key;
                    }
                    break;
            }
        }
        return true;
    }




}
