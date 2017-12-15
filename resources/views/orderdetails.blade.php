@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="js-orderTable">

    </div>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        var order = JSON.parse(@json($order));
        console.log(order);
        var tmpl = $.templates("#orderTmpl");
        @if(isset($type) && $type == "Seller")
        tmpl = $.templates("#orderSellerTmpl");
        @endif
        //$(".js-orderTable").append(tmpl.render(order.order));
        $(".js-orderTable").append(order.products.length + " products.");
        order.products.forEach(function (item) {
            item.images = item.images.slice(0, 1);
            item.sum = item.num_products * item.price;
            item.locked = true;
            tmpl = $.templates("#productTmpl");
            @if(isset($type) && $type == "Seller")
            tmpl = $.templates("#productSellerTmpl");
            @endif
            $(".js-orderTable").append(tmpl.render(item));
        });
        
        // ng.api.getOrder()
        //     .done(function (response) {
        //         console.log(response);
        //         response.forEach(function (item) {
        //             item.product["images"] = item.images;
        //             var tmpl = $.templates("#productTmpl");
        //             @if(isset($type) && $type == "Seller")
        //             tmpl = $.templates("#productSellerTmpl");
        //             @endif
        //             $(".js-productTable").append(tmpl.render(item.product));
        //         });
        //     })
        //     .fail(function (error) {
        //         console.log(error);
        //     });
    });
</script>

@stop