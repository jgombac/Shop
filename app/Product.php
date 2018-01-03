<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';
    protected $primaryKey = "id_product";
    public $timestamps = false;
    protected $fillable = ["name", "price", "active", "num_ratings", "rating"];


    public function rate($rating) {
        $newnumratings = $this->num_ratings + 1;
        $this->rating = ($this->num_ratings * $this->rating + $rating) / $newnumratings;
        $this->num_ratings = $newnumratings;
        $this->save();
    } 

    public function updateProduct($newname, $newprice, $newactive) {
        $this->update([
            "name" => $newname,
            "price" => $newprice,
            "active" => $newactive,
        ]);
    }

    public static function getProduct($id_product) {
        $item = parent::where("id_product", $id_product)->first();
        $item["images"] = $item->images; 
        return $item;
    }

    public static function getAll() {
        $items = parent::get();
        foreach ($items as $item) {
            $item["images"] = $item->images; 
        }
        return $items;
    }

    public static function active() {
        $items = parent::where("active", 1)->get();
        foreach ($items as $item) {
            $item["images"] = $item->images; 
        }
        return $items;
    }

    public function images() {
        return $this->hasMany("App\Image", "id_product");
    }


}