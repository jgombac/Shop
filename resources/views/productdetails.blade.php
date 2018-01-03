@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="js-productTable">

    </div>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        var productId = JSON.parse(@json($id_product));
        ng.api.getProduct(productId)
            .done(function (response) {

                var tmpl = $.templates("#productTmpl");
                @if(isset($type) && $type == "Seller") 
                tmpl = $.templates("#productSellerTmpl");

                $(".js-productTable").on("click", ".js-removeImage", function () {
                    var context = $(this).closest(".panel[data-ng-product]");
                    var data = {
                        "action": "remove",
                        "path": $(this).attr("data-ng-img"),
                        "id_product": parseInt($(context).attr("data-ng-product")),
                    }

                    ng.api.removeImage(data)
                        .done(function (response) {
                            window.location.reload();
                        })
                        .fail(function (error) {
                            console.log(error);
                        });

                })

                $(".js-productTable").on("click", ".js-addImage", function () {

                    var context = $(this).closest(".panel[data-ng-product]");
                    
                    var form = new FormData();
                    form.append("photo", $(this).closest(".form").find("input[type='file']").prop("files")[0]);
                    form.append("action", "add");
                    form.append("path",  $(this).closest(".form").find(".js-filename").val());
                    form.append("id_product", parseInt($(context).attr("data-ng-product")));
                    
                    ng.api.addImage(form)
                        .done(function (response) {
                            window.location.reload();
                        })
                        .fail(function (error) {
                            console.log(error);
                        });

                });

                
                $(".js-productTable").on("click", ".js-update", function () {
                    var context = $(this).closest(".panel[data-ng-product]");

                    var data = {
                        "id_product": parseInt($(context).attr("data-ng-product")),
                        "name": $(".js-name", context).val(),
                        "price": parseFloat($(".js-price", context).val()),
                        "active": $(".js-active", context).is(":checked")
                    }

                    console.log(data);

                    ng.api.updateProduct(data)
                        .done(function (response) {
                            if (response.code == 200) {
                                window.location.reload();
                            }
                            else {
                                alert(response.message);
                            }

                        })
                        .fail( function (error) {
                            console.log(error);
                        })
                    });
                @endif
                var item = response;
                item.images = response.images;
                $(".js-productTable").append(tmpl.render(item));
                $(".rate-area[data-ng-product='"+ item.id_product +"'] ."+Math.round(item.rating)+"-star").prop("checked", true);
                $(".rate-area[data-ng-product='"+ item.id_product +"'] [data-ng-stars]").on("click", function () {
                        var stars = $(this).attr("data-ng-stars");
                        $(".rate-area[data-ng-product='"+ item.id_product +"'] .star").prop("checked", false);
                        $(".rate-area[data-ng-product='"+ item.id_product +"'] ." + stars).prop("checked", true);
                        var rating = stars.split("-")[0];
                        var data = {
                            "id_product": item.id_product,
                            "rating": rating,
                        };
                        ng.api.rateProduct(data)
                            .done(function (response) {
                                console.log(response);
                            })
                            .fail(function (error) {
                                console.log(error);
                            });
                    });
                console.log(response);
            })
            .fail(function (error) {
                console.log(error);
            });

            $(".js-productTable").on("click", ".js-purchase", function () {
                @if(!isset($type) || isset($type) && $type == "anon") 
                window.location = "/login";
                @endif

                @if(isset($type) && $type == "Customer") 
                var context = $(this).closest(".panel");
                var data = {
                    "add": true,
                    "id_product": parseInt($(this).attr("data-ng-product")),
                    "num_products" : parseInt($(".js-numProducts", context).val())
                }

                ng.api.updateCart(data)
                    .done(function (response) {
                        console.log(response);
                    })
                    .fail( function (error) {
                        console.log(error);
                    })
                @endif

            });
    });
</script>

@stop