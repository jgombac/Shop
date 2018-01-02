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
use Illuminate\Database\QueryException;

class SellerController extends Controller
{



    public function login(Request $req) {

        $email = $req->input('email');
        $password = $req->input('password');
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return response()->json("Email or password is incorrect", 400);
        }
        $hashedpass = $user->password;
        if (Hash::check($password, $hashedpass)) {
            $finalAuth = "";
            $currentAuth = DB::table('auth')->where('id_user', $user->id_user)->first();
            if ($currentAuth && $currentAuth->expire > Carbon::now()) {
                $finalAuth = $currentAuth->code;
                $updateExpire = DB::table('auth')->where('id_user', $user->id_user)->update(['expire'=>Carbon::now()->addHours(5)]);
            }
            else {
                $oldAuthRemove = DB::table('auth')->where('id_user', $user->id_user)->delete();
                $auth = $this->requestAuth();
                $expire = Carbon::now()->addHours(5);
                $insertAuth = DB::table('auth')->insert(['id_user' => $user->id_user, 'code' => $auth, 'expire' => $expire]);
                $finalAuth = $auth;
            }
            if ($user->type == "seller"){
                return response()->json(
                    [
                        'auth' => $finalAuth,
                        'email' => $user->email, 
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                        'type' => $user->type
                ]);
            }
           
        }
        return response()->json("Email or password is incorrect", 400);
    }

    
    public function requestAuth() {
        $auth = str_random(60);
        while(DB::table('auth')->where('code', $auth)->get()->first()){
            $auth = str_random(60);
        }
        return $auth;
    }
 
}

