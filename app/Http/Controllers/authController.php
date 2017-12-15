<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
//use \App\Mail\PotrditevRacuna;

class authController extends BaseController
{

    public function getUser($auth) {
        $user = DB::table('auth')
        ->join("users", "auth.id_user", "=", "users.id_user")
        ->where('auth.code', $auth)
        ->select("users.id_user", "users.type", "auth.expire")
        ->first();
        if($user){
            if($user->expire > Carbon::now()){
                return $user;
            }
        }
        return null;
    }

    public function getUserByType($auth, $type){
        $id_user = DB::table('auth')
            ->join("users", "auth.id_user", "=", "users.id_user")
            ->where('auth.code', $auth)
            ->where('users.type', $type)
            ->select("users.id_user", "users.type", "auth.expire")
            ->first();
        if($id_user){
            if($id_user->expire > Carbon::now()){
                return $id_user->id_user;
            }
        }
        return null;
    }

    public function getUserType($auth){
        $id_user = DB::table('auth')
            ->join("users", "auth.id_user", "=", "users.id_user")
            ->where('code', $auth)
            ->select("users.id_user", "users.type", "auth.expire")
            ->first();
        if($id_user){
            if($id_user->expire > Carbon::now()){
                return $id_user->type;
            }
        }
        return null;
    }
 
}
