@extends('layouts.layout')

<?php
        $client_cert = $_SERVER["SSL_CLIENT_S_DN_CN"];

        if ($client_cert == null) {
            echo 'err: Spremenljivka SSL_CLIENT_CERT ni nastavljena.';
        }


        $cert_data = openssl_x509_parse($client_cert);
        $commonname = (is_array($cert_data['subject']['CN']) ?
                        $cert_data['subject']['CN'][0] : $cert_data['subject']['CN']);
        echo $client_cert;

?>

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

            adminLogin(data)
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

    var adminLogin =  function (data) {
        return ng.api.call("POST", "admin", data);
    }
</script>

@stop



