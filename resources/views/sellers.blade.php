@extends('layouts.layout')

@section('content')
<div class="container">

    <table>
        <thead>
            <th>First name</th>
            <th>Last name</th>
            <th>Email</th>
            <th>Active</th>
            <th></th>
            <th></th>
        </thead>
        <tbody class="js-sellerTable">

        </tbody>
    </table>
</div>
@stop

@section('scripts')
<script>
    $(document).ready(function () {
        ng.api.getSellers()
            .done(function (response) {
                if (response.message != undefined) {
                    console.log(response.message);
                }
                else {
                    var tmpl = $.templates("#sellerTmpl");
                    $(".js-sellerTable").append(tmpl.render(response));

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