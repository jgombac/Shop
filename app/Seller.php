<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{

    protected $table = 'sellers';
    protected $primaryKey = "id_user";
    public $timestamps = false;
    protected $fillable = ["active"];


}