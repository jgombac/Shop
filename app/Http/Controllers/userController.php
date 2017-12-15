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
//use \App\Mail\PotrditevRacuna;

class userController extends BaseController
{

    public function customers(Request $req) {
        $auth = $req->header('auth');
        $type = (new authController)->getUserType($auth);
        if ($type && $type == "seller") {
            $customers = DB::table('users')
            ->join('customers', 'users.id_user', '=', 'customers.id_user')
            ->select('users.id_user', 'users.first_name', 'users.last_name', 'users.email', 'customers.address', 'customers.street', 'customers.phone', 'customers.active')
            ->get();
            return json_encode($customers);
        }
        else {
            return json_encode(["message" => "Access denied."]);
        }
    }

    public function sellers(Request $req) {
        $auth = $req->header('auth');
        $type = (new authController)->getUserType($auth);
        if ($type && $type == "admin") {
            $customers = DB::table('users')
            ->join('sellers', 'users.id_user', '=', 'sellers.id_user')
            ->select('users.id_user', 'users.first_name', 'users.last_name', 'users.email', 'sellers.active')
            ->get();
            return json_encode($customers);
        }
        else {
            return json_encode(["message" => "Access denied."]);
        }
    }

    public function updateUser(Request $req) {
        $auth = $req->header("auth");
        $type = (new authController)->getUserType($auth);
        $id_user = $req->input("id_user");
        $first_name = $req->input("first_name");
        $last_name = $req->input("last_name");
        $email = $req->input("email");
        $exists = $this->checkEmailUpdate($email, $id_user);
        if($exists) {
            return json_encode(["message" => "E-mail already in use."]);
        }
        if ($type == "seller" || $type == "admin") {
            $user = DB::table("users")
                ->where("id_user", $id_user)
                ->update([
                    'email' => $email, 
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ]);
        }
       
        if ($type == "seller") {
            $address = $req->input("address");
            $street = $req->input("street");
            $phone = $req->input("phone");
            $active = $req->input("active");

            $customer = DB::table("customers")
            ->where("id_user", $id_user)
            ->update([
                'address' => $address, 
                'street' => $street,
                'phone' => $phone,
                'active' => $active, 
            ]);
            return json_encode(["message" => "Customer updated"]);
        }

        else if ($type == "admin") {
            $active = $req->input("active");

            $seller = DB::table("sellers")
            ->where("id_user", $id_user)
            ->update([
                'active' => $active, 
            ]);
            return json_encode(["message" => "Seller updated"]);
        }
        return json_encode(["message" => "Unauthorized"]);
    }

    public function profile(Request $req) {
        $auth = $req->header('auth');
        $user = (new authController)->getUser($auth);
        if ($user) {
            $profile = DB::table("users")->where("id_user", $user->id_user)->first();
            if ($user->type == "customer") {
                $customer = DB::table("customers")->where("id_user", $user->id_user)->first();
                return json_encode(
                    [
                        'email' => $profile->email, 
                        'firstName' => $profile->first_name,
                        'lastName' => $profile->last_name,
                        'address' => $customer->address,
                        'street' => $customer->street,
                        'phone' => $customer->phone,   
                    ]
                );
            }
            // if ($user->type == "seller") {
            //     $seller = DB::table("sellers")->where("id_user", $user->id_user)->first();
            //     return json_encode($profile->merge($seller));
            // }
            return json_encode($profile);
        }
        return json_encode(["message" => "Access denied."]);
    }

    public function updateProfile(Request $req) {
        $auth = $req->header('auth');
        $user = (new authController)->getUser($auth);
        if ($user) {
            $email = $req->input('email');   
            $exists = $this->checkEmailUpdate($email, $user->id_user);
            if($exists) {
                return json_encode(["message" => "E-mail already in use."]);
            }
            if (strlen($req->input('password')) > 4) {
                $password = Hash::make($req->input('password'));
                $updatedUser = DB::table("users")->where("id_user", $user->id_user)
                ->update([
                    'password' => $password,
                ]);
            }
            
            $firstName = $req->input('firstName');
            $lastName = $req->input("lastName");
            $updatedUser = DB::table("users")->where("id_user", $user->id_user)
                        ->update([
                            'email' => $email, 
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ]);

            if ($user->type == "customer") {
                $address = $req->input('address');
                $street = $req->input('street');
                $phone = $req->input('phone');
                $customer = DB::table("customers")->where("id_user", $user->id_user)
                        ->update([
                            "address" => $address,
                            "street" => $street,
                            "phone" => $phone,
                        ]);                
            }
            return json_encode(["message" => "Profile has been updated."]);
        }
        return json_encode(["message" => "Access denied."]);
    }

    public function login(Request $req) {

        $email = $req->input('email');
        $password = $req->input('password');
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return json_encode([
                "message" => "Email and/or password incorrect"
            ]);
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
            if ($user->type == "admin"){
                return json_encode(
                    [
                    'auth' => $finalAuth,
                    'email' => $user->email, 
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'type' => $user->type
                ]);
            }
            else if ($user->type == "seller") {
                return json_encode(
                    [
                    'auth' => $finalAuth,
                    'email' => $user->email, 
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'type' => $user->type
                ]);
            }
            else {
                $customer = DB::table('customers')//->where('id_user', $user->id_user)->->first();
                    ->join('validations', 'validations.id_user', '=', 'customers.id_user')
                    ->where('customers.id_user', $user->id_user)
                    ->where('customers.active', 1)
                    ->where('validations.validated', 1)
                    ->select('customers.address', 'customers.street', 'customers.phone', 'customers.active')
                    ->first();
                if ($customer != null){
                    return json_encode(
                        [
                        'auth' => $finalAuth,
                        'email' => $user->email, 
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                        'address' => $customer->address,
                        'street' => $customer->street,
                        'phone' => $customer->phone,    
                    ]);
                }
                else {
                    return json_encode([
                        "message" => "Your account has not been confirmed yet or has been blocked."
                    ]);
                }
            }   
            

        }
        return json_encode([
            "message" => "Email and/or password incorrect"
        ]);
    }


    public function register(Request $req) {
        if (isset($_COOKIE["auth"])) {
            $auth = $_COOKIE["auth"];
            $type = (new authController)->getUserType($auth);
            //Register seller
            if ($type == "admin") {
                $email = $req->input('email');   
                $exists = $this->checkEmail($email);
                if($exists) {
                    return json_encode(["message" => "E-mail already in use."]);
                }
                $password = Hash::make($req->input('password'));
                $firstName = $req->input('firstName');
                $lastName = $req->input("lastName");

                try {
                    $userId = DB::table('users')->insertGetId(
                        ['email' => $email, 
                        'password' => $password, 
                        'first_name' => $firstName, 
                        'last_name' => $lastName, 
                        'type' => 'seller',
                        ]
                    );
                } catch (QueryException $ex) {
                    return json_encode(["message" => "An error occured"]);
                }
                $seller = DB::table('sellers')->insert(
                    [
                        'id_user' => $userId,
                        'active' => true,
                    ]
                );
                if (!$userId || !$seller) {
                    return json_encode([
                        "message" => "Registration failed."
                    ]);
                }
                return json_encode([
                    "message" => "Registration successful."
                ]);
            }

        }
        //Register customer
        $email = $req->input('email');   
        $exists = $this->checkEmail($email);
        if($exists) {
            return json_encode(["message" => "E-mail already in use."]);
        }
        $password = Hash::make($req->input('password'));
        $firstName = $req->input('firstName');
        $lastName = $req->input("lastName");
        $address = $req->input('address');
        $street = $req->input('street');
        $phone = $req->input('phone');

        $validation = Hash::make($email + $password);
        $validation = str_replace('/', '_', $validation);
        try {
            $userId = DB::table('users')->insertGetId(
                ['email' => $email, 
                'password' => $password, 
                'first_name' => $firstName, 
                'last_name' => $lastName, 
                'type' => 'customer',
                ]
            );
        } catch (QueryException $ex) {
            return json_encode(["message" => "An error occured"]);
        }
        $customer = DB::table('customers')->insert(
            [
                'id_user' => $userId,
                'address' => $address,
                'street' => $street,
                'phone' => $phone,
                'active' => true,
            ]
        );
        if (!$userId || !$customer) {
            return json_encode([
                "message" => "Registration failed."
            ]);
        }
        $valid = DB::table('validations')->insert(['id_user' => $userId, 'validation_code' => $validation, 'validated' => false]);
        //$emailSent = $this->sendEmail($email, $validation);
        return json_encode([
            "message" => "Registration successful."
        ]);
    }

    public function checkEmail($email) {
        $exists = DB::table('users')->where('email', $email)->first();
        if ($exists) {
            return true;
        }
        return false;
    }

    public function checkEmailUpdate($email, $id_user) {
        $ownEmail = DB::table('users')->where('email', $email)->where("id_user", $id_user)->first();
        if ($ownEmail) {
            return false;
        }
        $exists = DB::table('users')->where('email', $email)->where("id_user", "!=",  $id_user)->first();
        if($exists) {
            return true;
        }
        return false;
    }


    public function requestAuth() {
        $auth = str_random(60);
        while(DB::table('auth')->where('code', $auth)->get()->first()){
            $auth = str_random(60);
        }
        return $auth;
    }

    public function confirm($validation) {
        $confirm = DB::table('validations')->where('validation_code', $validation)->get()->first();
        if($confirm && !$confirm->validated){
            $userUpdate = DB::table('validations')->where('validation_code', $validation)->update(['validated' => true]);
            return view('activateaccount', ['message' => 'Activation successful.']);
        }
        return view('activateaccount', ['message' => 'Invalid token.']);;        
    }

}
