<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductRating extends Model
{

    protected $table = 'product_ratings';
    protected $primaryKey = ["id_product", "id_user"];
    public $timestamps = false;


    public static function newRating($id_product, $id_user, $rating) {
        try {
            $insert = DB::table("product_ratings")->insert(
                [
                    "id_product" => $id_product,
                    "id_user" => $id_user,
                    "rating" => $rating
                ]
                );
                return true;
        } catch (\Illuminate\Database\QueryException $ex) {
            return false;
        }
    }

}