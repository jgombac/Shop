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

use App\User;
use App\Customer;
use App\Seller;

class userController extends BaseController
{

    public function customers(Request $req) {
        $auth = $req->header('auth');
        $type = (new authController)->getUserType($auth);
        if ($type && $type == "seller") {
            $customers = DB::table('users')
            ->join('customers', 'users.id_user', '=', 'customers.id_user')
            ->select('users.id_user', 'users.first_name', 'users.last_name', 'users.email', 'customers.address', 'customers.street', 'customers.phone', "customers.id_postal", 'customers.active')
            ->get();
            return response()->json($customers);
        }
        else {
            return response()->json("Unauthorized", 403);
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
            return response()->json($customers);
        }
        else {
            return response()->json("Unauthorized", 403);
        }
    }

    public function updateUser(Request $req) {
        $auth = $req->header("auth");
        $type = (new authController)->getUserType($auth);
        $id_user = $req->input("id_user");
        $first_name = $req->input("first_name");
        $last_name = $req->input("last_name");
        $email = $req->input("email");
        $password = $req->input("password");
        $repeatPassword = $req->input("repeatPassword");

        $exists = $this->checkEmailUpdate($email, $id_user);
        if($exists) {
            return response()->json("Email already in use", 400);
        }

        $verified = $this->verifyFields(["first_name" => $first_name, "last_name" => $last_name, "email" => $email]);
        if ($verified != 1){
            return response()->json("Bad input: ".$verified, 400);
        }
        $user_type = User::find($id_user)->type;


        if ($type == "seller" || $type == "admin") {
            $user = User::find($id_user)->update([
                    'email' => $email, 
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ]);
            if($password != null) {
                $verified = $this->verifyFields(["password" => $password, "repeat_pass" => $repeatPassword]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }
                $hashed = Hash::make($password);
                User::find($id_user)->update([
                    "password" => $hashed
                ]);
            }
        }
       
        if ($type == "seller") {
            $address = $req->input("address");
            $street = $req->input("street");
            $phone = $req->input("phone");
            $postal = $req->input("postal");
            $active = $req->input("active");
            $verified = $this->verifyFields(["address" => $address, "street" => $street, "phone" => $phone, "postal" => $postal, "active" => $active]);
            if ($verified != 1){
                return response()->json("Bad input: ".$verified, 400);
            }

            $postalcheck = DB::table("postals")->where("id_postal", $postal)->first();
            if (!$postalcheck) {
                return response()->json("Invalid postal number", 400);
            }

            $customer = Customer::find($id_user)->update([
                'address' => $address, 
                'street' => $street,
                'phone' => $phone,
                "id_postal" => $postal,
                'active' => $active, 
            ]);
            return response()->json("Customer updated");
        }

        else if ($type == "admin") {
            $active = $req->input("active");
            $verified = $this->verifyFields(["active" => $active]);
            if ($verified != 1){
                return response()->json("Bad input: ".$verified, 400);
            }
            $seller = Seller::find($id_user)->update([
                'active' => $active, 
            ]);
            return response()->json("Seller updated");
        }
        return response()->json("Unauthorized", 403);
    }

    public function profile(Request $req) {
        $auth = $req->header('auth');
        $user = (new authController)->getUser($auth);
        if ($user) {
            $profile = DB::table("users")->where("id_user", $user->id_user)->first();
            if ($user->type == "customer") {
                $customer = DB::table("customers")->where("id_user", $user->id_user)->first();
                $postal = DB::table("postals")->where("id_postal", $customer->id_postal)->first();
                return json_encode(
                    [
                        'email' => $profile->email, 
                        'first_name' => $profile->first_name,
                        'last_name' => $profile->last_name,
                        'address' => $customer->address,
                        'street' => $customer->street,
                        'postal' => $postal->id_postal,
                        'city' => $postal->city,
                        'phone' => $customer->phone,   
                    ]
                );
            }

            return json_encode($profile);
        }
        return response()->json("Can not view this profile", 401);
    }

    public function updateProfile(Request $req) {
        $auth = $req->header('auth');
        $user = (new authController)->getUser($auth);
        if ($user) {
            $email = $req->input('email');   
            $exists = $this->checkEmailUpdate($email, $user->id_user);
            if($exists) {
                return response()->json("Email already in use", 400);
            }
            if (strlen($req->input('password')) > 4) {
                $password = $req->input('password');
                $repeatPassword = $req->input('repeatPassword');
                $verified = $this->verifyFields(["password" => $password, "repeat_pass" => $repeatPassword]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }
                $hashed = Hash::make($password);
                $updatedUser = DB::table("users")->where("id_user", $user->id_user)
                ->update([
                    'password' => $hashed,
                ]);
            }

            $firstName = $req->input('firstName');
            $lastName = $req->input("lastName");
            $verified = $this->verifyFields(["first_name" => $firstName, "last_name" => $lastName, "email" => $email]);
            if ($verified != 1){
                return response()->json("Bad input: ".$verified, 400);
            }
            $updatedUser = DB::table("users")->where("id_user", $user->id_user)
                        ->update([
                            'email' => $email, 
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ]);

            if ($user->type == "customer") {
                $id_postal = $req->input('postal');
                $city = $req->input('city');
                $postalcheck = DB::table("postals")->where("id_postal", $id_postal)->where("city", "like", '%'.ucfirst($city).'%')->first();
                if (!$postalcheck) {
                    return response()->json("Postal code doesnt match the city or doesn't exist.", 400);
                }
                $address = $req->input('address');
                $street = $req->input('street');
                $phone = $req->input('phone');
                $verified = $this->verifyFields(["address" => $address, "street" => $street, "phone" => $phone, "postal" => $id_postal, "city" => $city]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }
                $customer = DB::table("customers")->where("id_user", $user->id_user)
                        ->update([
                            "address" => $address,
                            "street" => $street,
                            "id_postal" => $postalcheck->id_postal,
                            "phone" => $phone,
                        ]);                
            }
            return response()->json("Profile has been updated");
        }
        return response()->json("Can not update", 400);
    }

    public function login(Request $req) {

        $email = $req->input('email');
        $password = $req->input('password');
        $verified = $this->verifyFields(["email" => $email, "password" => $password]);
        if ($verified != 1){
            return response()->json("Bad input: ".$verified, 400);
        }
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return response()->json("Email or password incorrect", 400);
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
            if ($user->type == "admin" || $user->type == "seller"){
                return response()->json("Email or password incorrect", 400);
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
                    //check if user has a cart
                    $latest = DB::table("orders")->where("id_user", $user->id_user)->where("status", 0)->where("finished", 0)->where("processed", 0)->orderBy("created", "desc")->first();
                    if (!$latest){                   
                        $order = DB::table("orders")->insertGetId([
                            "id_user" => $user->id_user,
                            "status" => 0,
                            "finished" => 0,
                            "processed" => 0,
                        ]);
                    }
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
                    return response()->json("Your account has not been confirmed yet or has been blocked", 401);
                }
            }   
            

        }
        return response()->json("Email or password incorrect", 400);
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
                    return response()->json("Email already in use", 400);
                }
                $password = Hash::make($req->input('password'));
                $firstName = $req->input('firstName');
                $lastName = $req->input("lastName");
                $verified = $this->verifyFields(["email" => $email, "password" => $password, "first_name" => $firstName, "last_name" => $lastName]);
                if ($verified != 1){
                    return response()->json("Bad input: ".$verified, 400);
                }               
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
                    return response()->json("Error", 500);
                }
                $seller = DB::table('sellers')->insert(
                    [
                        'id_user' => $userId,
                        'active' => true,
                    ]
                );
                if (!$userId || !$seller) {
                    return response()->json("Registration failed", 400);
                }
                return response()->json("Registration successful.");
            }

        }
        //Register customer
        $email = $req->input('email');   
        $exists = $this->checkEmail($email);
        if($exists) {
            return response()->json("Email already in use", 400);
        }
        $password = $req->input("password");
        $repeatPassword = $req->input("repeatPassword");
        $firstName = $req->input('firstName');
        $lastName = $req->input("lastName");
        $address = $req->input('address');
        $street = $req->input('street');
        $postal = $req->input("postal");
        $city = $req->input("city");
        $phone = $req->input('phone');
        $verified = $this->verifyFields(["email" => $email, "password" => $password, "repeat_pass" => $repeatPassword, "first_name" => $firstName, "last_name" => $lastName, 
        "address" => $address, "street" => $street, "postal" => $postal, "city" => $city, "phone" => $phone]);
        if ($verified != 1){
            return response()->json("Bad input: ".$verified, 400);
        }  
        //postal check
        $postalcheck = DB::table("postals")->where("id_postal", $postal)->where("city", "like", $city)->first();
        if (!$postalcheck) {
            return response()->json("Postal code doesnt match the city or doesn't exist.", 400);
        }

        $validation = Hash::make($email . $password);
        $validation = str_replace('/', '_', $validation);
        $hashed = Hash::make($password);
        $userId = DB::table('users')->insertGetId(
            ['email' => $email, 
            'password' => $hashed, 
            'first_name' => $firstName, 
            'last_name' => $lastName, 
            'type' => 'customer',
            ]
        );

        $customer = DB::table('customers')->insert(
            [
                'id_user' => $userId,
                'address' => $address,
                'street' => $street,
                'phone' => $phone,
                "id_postal" => $postalcheck->id_postal,
                'active' => true,
            ]
        );
        if (!$userId || !$customer) {
            return response()->json("Registration failed", 400);
        }
        $valid = DB::table('validations')->insert(['id_user' => $userId, 'validation_code' => $validation, 'validated' => false]);
        $emailSent = $this->sendEmail($email, $validation);
        return response()->json("Registration successful");
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

    public function sendEmail($email, $confirmation) {
        Mail::send('email.confirm', array('confirmation' => "https://dev.dovigom.com/confirm/".$confirmation), function($message) use ($email)
        {
            $message->to($email)->subject('Aktivacija računa');
        });
    }

    public function verifyFields($fields) {

        foreach ($fields as $key => $val) {
            if($val == null){
                return $key;
            }
            switch ($key) {
                case "email":
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        return $key;
                    }
                    break;
                case "password":
                    if (strlen($val) < 6) {
                        return $key;
                    }
                    break;
                case "repeat_pass":
                    if ($fields["password"] != $val) {
                        return $key;
                    }
                    break;
                case "active":
                    if ($val == 0 || $val == 1 || $val == "true" || $val == "false") {
                        continue;
                    } 
                    else{
                        return $key;
                    }
                    break;
                case "first_name":
                    if (!preg_match("/^\s*([a-zA-Z]+\s*){1,4}$/", $val)) {
                        return $key;
                    }  
                    break;
                case "last_name":
                    if (!preg_match("/^\s*([a-zA-Z]+\s*){1,4}$/", $val)) {
                        return $key;
                    }  
                    break;
                case "address":
                    if (!preg_match("/^\s*([a-zA-Z]+\s*){1,4}$/", $val)) {
                        return $key;
                    }  
                    break;
                case "street":
                    if (!preg_match("/^[a-zA-Z0-9]{1,10}$/", $val)) {
                        return $key;
                    } 
                    break;
                case "postal":
                    if (!preg_match("/^\d+$/", $val)) {
                        return $key;
                    }    
                break;
                case "city":
                    if (!preg_match("/^\s*([a-zA-Z]+\s*){1,4}$/", $val)) {
                        return $key;
                    } 
                    break;
                case "phone":
                    if (!preg_match("/^([0-9]{2,3}|\(?\+{0,1}[0-9]{2,3}\)?)\s?[0-9]{2,3}\s?[0-9]{2,3}\s?[0-9]{0,3}$/", $val)) {
                        return $key;
                    }                
                    break;
            }
        }
        return true;
    }

}
