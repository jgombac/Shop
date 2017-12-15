@extends('layouts.layout')

@section('content')
<div class="modal fade" id="finalizeModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Confirm purchase</h4>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button id="finish-order" type="button" class="btn btn-primary">Complete purchase</button>
        </div>
      </div>
      
    </div>
  </div>
<div class="container">
    <div class="panel container">
        <input id="checkout" type="button" class="btn btn-primary" data-toggle="modal" data-target="#finalizeModal" value="Checkout">
        
    </div>
    <div class="js-orderTable">

    </div>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        var orderId = null;
        ng.api.getLatestOrder()
            .done(function (response) {
                // var tmpl = $.templates("#orderTmpl");
                // $(".js-orderTable").append(tmpl.render(response.order));
                orderId = response.order.id_order;
                var tmpl = $.templates("#productTmpl");
                // $(".js-orderTable").append(response.products.length + " products.");
                if (response.products.length == 0) {
                    $("#checkout").prop("disabled", true);
                }
                response.products.forEach(function (item) {
                    item.images = item.images.slice(0, 1);
                    item.sum = item.num_products * item.price;
                    item.cart = true;
                    $(".js-orderTable").append(tmpl.render(item));
                });
                var check = createCheck(response.products);
                var tmpl = $.templates("#checkTmpl");
                $(".modal-body").append(tmpl.render(check));
            })
            .fail(function (error) {
                console.log(error);
            });

            $(".js-orderTable").on("click", ".js-updateCart", function () {
                var context = $(this).closest(".panel");
                var data = {
                    "id_product": parseInt($(this).attr("data-ng-product")),
                    "num_products" : parseInt($(".js-numProducts", context).val())
                }

                ng.api.updateCart(data)
                    .done(function (response) {
                        if (response.code == 200) {
                            window.location.reload();
                        }
                        else {
                            alert(response.message)
                        }
                    })
                    .fail(function (error) {
                        console.log(error);
                    });

            });

            $("#finish-order").on("click", function () {
                data = {
                    "id_order": orderId,
                }

                ng.api.finishOrder(data)
                    .done(function (response) {
                        if (response.code == 200) {
                            window.location.pathname = "/products";
                        }
                        else {
                            alert(response.message)
                        }
                    })
                    .fail(function (error) {
                        console.log(error);
                    });
            });

    });

    function createCheck(products) {
        var check = {
            "items": [],
            "total": 0.0
        }
        products.forEach(function (item) {
            check["items"].push(item);
            check["total"] += item.price * item.num_products;
        });
        return check;
    }
</script>

@stop