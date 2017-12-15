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
    <input type="button" id="update" value="Update" class="btn btn-primary">
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        ng.api.getProfile()
                .done(function (response){
                    console.log(response)
                    if (response.message != undefined){
                        alert(response.message);
                    }
                    else {
                        $("#email").val(response.email);
                        $("#first-name").val(response.firstName);
                        $("#last-name").val(response.lastName);
                        $("#address").val(response.address);
                        $("#street").val(response.street);
                        $("#phone").val(response.phone);
                    }
                })
                .fail(function (error){
                    console.log(error);
                });

        $("#update").on("click", function () {
            var data = {
                "email": $("#email").val(),
                "firstName": $("#first-name").val(),
                "lastName": $("#last-name").val(),
                "address": $("#address").val(),
                "street": $("#street").val(),
                "phone": $("#phone").val(),
            };

            if($("#password").val().length > 4) {
                data["password"] = $("#password").val();
            }

            ng.api.updateProfile(data)
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