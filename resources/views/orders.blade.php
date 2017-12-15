@extends('layouts.layout')

@section('content')
<div class="container">
    <table class="table">
        <thead>
        @if(isset($type) && $type == "Seller")
            <th>Created</th>
            <th>Status</th>
            <th>Processed</th>
            <th></th>
            <th></th>
        @endif
        @if(isset($type) && $type == "Customer")
            <th>Created</th>
            <th></th>
        @endif

        </thead>
        <tbody class="js-orderTable">

        </tbody>
    </table>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        ng.api.getOrders()
            .done(function (response) {
                if (response.message != undefined) {
                    console.log(response);
                }
                else {
                    var tmpl = $.templates("#orderTmpl");
                    @if(isset($type) && $type == "Seller")
                    tmpl = $.templates("#orderSellerTmpl");
                    @endif
                    $(".js-orderTable").append(tmpl.render(response));
                }
            })
            .fail(function (error) {
                console.log(error);
            });

            @if(isset($type) && $type == "Seller")
                $(".js-orderTable").on("click", ".js-update", function () {
                    var context = $(this).closest("tr");
                    var data = {
                        "id_order": parseInt($(context).attr("data-ng-order")),
                        "status": parseInt($(".js-status").val()),
                        "processed": parseInt($(".js-processed").val())
                    }


                    ng.api.updateOrder(data)
                        .done(function (response) {
                            console.log(response);
                        })
                        .fail(function (error) {
                            console.log(error);
                        });
                })
            @endif
    });
</script>

@stop