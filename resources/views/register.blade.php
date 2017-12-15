@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" class="form-control">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="text" id="password" class="form-control">
    </div>
    <div class="form-group">
        <label for="first-name">First name</label>
        <input type="text" id="first-name" class="form-control">
    </div>
    <div class="form-group">
        <label for="last-name">Last name</label>
        <input type="text" id="last-name" class="form-control">
    </div>
    <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" class="form-control">
    </div>
    <div class="form-group">
        <label for="strees">Street</label>
        <input type="text" id="street" class="form-control">
    </div>
    <div class="form-group">
    <label for="phone">Phone</label>
    <input type="text" id="phone" class="form-control">
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
                "phone": $("#phone").val(),
            };

            ng.api.register(data)
                .done(function (response){
                    console.log(response)
                    if (response.message != undefined){
                        alert(response.message);
                    }
                })
                .fail(function (error){
                    console.log(error);
                });
        });

    });
</script>

@stop