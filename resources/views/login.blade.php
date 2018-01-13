@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" class="form-control">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" class="form-control">
    </div>
    <input type="button" id="login" value="Login" class="btn btn-primary">
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        $("#login").on("click", function () {
            var data = {
                "email": $("#email").val(),
                "password": $("#password").val()
            };

            ng.api.login(data)
                .done(function (response){
                    if (response.auth != undefined) {
                        console.log("successfull login");
                        ng.cookies.set("auth", response.auth);
                        window.location = "/products";
                    }
                    else if (response.message != undefined){
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