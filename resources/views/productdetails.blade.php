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
                @endif
                var item = response.product;
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