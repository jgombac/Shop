@extends('layouts.layout')

@section('content')
<div class="">

    <table>
        <thead>
            <th>First name</th>
            <th>Last name</th>
            <th>Email</th>
            <th>Address</th>
            <th>Street</th>
            <th>Phone</th>
            <th>Active</th>
            <th></th>
            <th></th>
        </thead>
        <tbody class="js-customerTable">

        </tbody>
    </table>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        ng.api.getCustomers()
            .done(function (response) {
                if (response.message != undefined) {
                    console.log(response.message);
                }
                else {
                    var tmpl = $.templates("#customerTmpl");
                    $(".js-customerTable").append(tmpl.render(response));

                    $(".js-toggleChange").on("click", function () {
                        var context = $(this).closest("tr");
                        if ($("input", context).prop("disabled")) {
                            $("input", context).prop("disabled", false);
                        }
                        else {
                            $("input", context).prop("disabled", true);
                        }
                    });

                    $(".js-update").on("click", function () {
                        var context = $(this).closest("tr");
                        var data = {
                            "id_user": parseInt($(context).attr("data-ng-user")),
                            "first_name": $(".js-firstName", context).val(),
                            "last_name": $(".js-lastName", context).val(),
                            "email": $(".js-email", context).val(),
                            "active": $(".js-active", context).is(":checked"),
                            "address": $(".js-address", context).val(),
                            "street": $(".js-street", context).val(),
                            "phone": $(".js-phone", context).val(),
                        }
                        console.log(data);
                        ng.api.updateUser(data)
                            .done(function (response) {
                                console.log(response);
                            })
                            .fail(function (error) {
                                console.log(error);
                            });
                    });
                }
            })
            .fail(function (error) {
                console.log(error);
            });
    });
</script>

@stop