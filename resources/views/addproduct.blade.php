@extends('layouts.layout')

@section('content')   
<div class="container">
    <div class="panel row">
    <div class="col-xs-12 col-md-8">
        <input class="js-file" type="file"/>
    </div>
    <div class="col-xs-12 col-md-4">
        <div class="form-group">
            <label>Name</label>
            <input class="js-name" type="text" class="form-control">
        </div>
        <div class="form-group">
            <label>Price</label>
            <input class="js-price" type="text"  class="form-control">
        </div>
        <div class="form-group">
            <label>Active</label>
            <input class="js-active" type="checkbox" name="Active">
        </div>
        <div>
        <button class="btn btn-primary js-add">Add product</button>                
        </div>

    </div>

    </div>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {

        $(".js-add").on("click", function () {
            var form = new FormData();
            form.append("image", $(".js-file").prop("files")[0]);
            form.append("name", $(".js-name").val());
            form.append("price", parseFloat($(".js-price").val()));
            form.append("active", $(".js-active").is(":checked"));
            console.log(form);

            ng.api.addProduct(form)
                .done(function (response) {
                    window.location = "/";
                    console.log(response);
                })
                .fail(function (error) {
                    console.log(error);
                });

        });
        
    });
</script>

@stop