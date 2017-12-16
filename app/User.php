<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $table = 'users';
    protected $primaryKey = "id_user";
    protected $hidden = ['password'];


    public function customer() {
        return $this->hasOne("App\Customer", "id_user");
    }

    public function admin() {
        return $this->hasOne("App\Admin", "id_user");
    }

    public function seller() {
        return $this->hasOne("App\Seller", "id_user");
    }

    public function profile() {
        $type = $this->type;
        switch ($type) {
            case "customer":
                return $this->toArray() + $this->customer->toArray();
                break;
            case "seller":
                return $this->toArray() + $this->seller->toArray();
                break;
            case "admin":
                return $this->toArray();
                break;
            return [];
        }
    }


}