@extends('layouts.layout')

@section('content')
<div class="container">
{{ $type }}
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
    <input type="button" id="update" value="Update profile" class="btn btn-primary">
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        ng.api.getProfile()
            .done(function (response) {
                if(response.message != undefined){
                    console.log(response.message);
                }
                else {
                    $("#email").val(response.email);
                    $("#first-name").val(response.first_name);
                    $("#last-name").val(response.last_name);
                }

            })
            .fail(function (error) {

            });


        $("#update").on("click", function () {
            var data = {
                "email": $("#email").val(),
                "firstName": $("#first-name").val(),
                "lastName": $("#last-name").val(),
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