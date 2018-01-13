<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Product;

class Order extends Model
{

    protected $table = 'orders';
    protected $primaryKey = "id_order";
    public $timestamps = false;

    public static function getAll() {
        return parent::orderBy("created", "desc")->get();
    }

    public function products() {
        return $this->belongsToMany("App\Product", "in_order", "id_order", "id_product");
    }

    public function withProducts() {
        $items = $this::join("in_order", "in_order.id_order", "=", "orders.id_order")
        ->join("products", "in_order.id_product", "=", "products.id_product")
        ->where("in_order.id_order", $this->getKey())
        ->select("in_order.id_product", "products.name", "in_order.num_products", "in_order.price")
        ->get();
        foreach($items as $item) {
            $item["images"] = Product::find($item->id_product)->images;
        }
        return ["order" => $this->toArray(), "products" => $items];
    }

    public static function finished() {
        return parent::where([
            "finished" => 1,
            ])
            ->orderBy("created", "desc")
            ->get();
    }

    public function hasProduct($id_product) {
        return DB::table("in_order")
        ->where([
            "id_order" => $this->getKey(),
            "id_product" => $id_product,
            ])
        ->first();
    }


    public static function history($id_user) {
        return parent::where([
            "id_user" => $id_user,
            "finished" => 1,
            ])
            ->orderBy("created", "desc")
            ->get();
    }

    public static function cart($id_user) {
        return parent::where([
            "id_user" => $id_user,
            "finished" => 0,
            "status" => 0, 
            "processed" => 0])
            ->orderBy("created", "desc")
            ->first();
    }


    public function addNewProduct($id_product, $num_products) {
        $key = $this->getKey();
        $newPrice = Product::find($id_product)->price;
        $insert = DB::table("in_order")
        ->insert([
            "id_order" => $key,
            "id_product" => $id_product,
            "num_products" => $num_products,
            "price" => $newPrice
        ]);
    }

    public function updateProductVolume($product, $num_products, $add) {
        $newVolume = $product->num_products;
        $isActive = Product::find($product->id_product)->active;
        if ($isActive) {
            if ($add) {
                $newVolume = $newVolume + $num_products;
            }
            else {
                $newVolume = $num_products;
            }
        }
        else {
            if ($num_products < $newVolume) {
                $newVolume = $num_products;
            }
        }           
        $update = DB::table("in_order")
        ->where([
            "id_order" => $this->getKey(),
            "id_product" => $product->id_product,
        ])
        ->update([
            "num_products" => $newVolume,
            "price" => $this->products->find($product->id_product)->price,
        ]);
    }

    public function removeProduct($id_product) {
        $delete = DB::table("in_order")
        ->where([
            "id_order" => $this->getKey(),
            "id_product" => $id_product,
        ])
        ->delete();
    }


}