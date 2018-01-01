@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="row">
        <div class="form-group col-sm-6">
            <label for="email">Email</label>
            <input type="text" id="email" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-sm-3">
            <label for="password">Password</label>
            <input type="text" id="password" class="form-control">
        </div>
        <div class="form-group col-sm-3">
            <label for="repeatpassword">Repeat password</label>
            <input type="text" id="repeatpassword" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-3">
            <label for="first-name">First name</label>
            <input type="text" id="first-name" class="form-control">
        </div>
        <div class="form-group col-sm-3">
            <label for="last-name">Last name</label>
            <input type="text" id="last-name" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-4">
            <label for="address">Address</label>
            <input type="text" id="address" class="form-control">
        </div>
        <div class="form-group col-sm-2">
            <label for="street">Street</label>
            <input type="text" id="street" class="form-control">
        </div>
    </div>


    <div class="row">
        <div class="form-group col-sm-2">
            <label for="postal">Postal code</label>
            <input type="text" id="postal" class="form-control">
        </div>
        <div class="form-group col-sm-4">
            <label for="city">City</label>
            <input type="text" id="city" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-6">
            <label for="phone">Phone</label>
            <input type="text" id="phone" class="form-control">
        </div>      
    </div>


    <input type="button" id="register" value="Register" class="btn btn-primary">
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        $("#register").on("click", function () {
            var data = {
                "email": $("#email").val(),
                "password": $("#password").val(),
                "firstName": $("#first-name").val(),
                "lastName": $("#last-name").val(),
                "address": $("#address").val(),
                "street": $("#street").val(),
                "postal": parseInt($("#postal").val()),
                "city": $("#city").val(),
                "phone": $("#phone").val(),
            };

            ng.api.register(data)
                .done(function (response){
                    window.location.href =  "/login"
                })
                .fail(function (error){
                    console.log(error);
                });
        });

    });
</script>

@stop