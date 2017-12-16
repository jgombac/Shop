<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'administrators';
    protected $primaryKey = "id_user";
}