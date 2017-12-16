<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $table = 'customers';
    protected $primaryKey = "id_user";
    protected $hidden = ["id_user"];


}