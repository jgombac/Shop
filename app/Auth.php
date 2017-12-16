<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{

    protected $table = 'auth';
    protected $primaryKey = "code";

    public function user() {
        return $this->belongsTo("App\User", "id_user");
    }


}