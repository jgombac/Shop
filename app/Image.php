<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $table = 'images';
    protected $primaryKey = "id_image";

    protected $hidden = ["id_image", "id_product"];


}