<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Shop | @isset($type){{ $type }}@endisset</title>

        <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}" />
    </head>
    <body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <!-- <a class="navbar-brand" href="#">Shopper</a> -->
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="products"><a href="/products">Products</a></li>

            </ul>
            <ul class="nav navbar-nav navbar-right">
                @if (!isset($type) || $type == "anon")
              <li class="login"><a href="/login">Login</a></li>
              <li class="register"><a href="/register">Register</a></li>
              @else
              
              @switch($type)
              @case('Customer')
              <li class="cart"><a href="/cart">Cart</a></li>
              <li class="orders"><a href="/orders">Orders</a></li>
              @break
              @case('Seller')
              <li class="customers"><a href="/customers">Customers</a></li>
              <li class="orders"><a href="/orders">Orders</a></li>
              @break
              @case ('Admin')
              <li class="sellers"><a href="/sellers">Sellers</a></li>
              @break
              @endswitch
              
              <li class="profile"><a href="/profile">Profile</a></li>
              <li><a class="js-logout" href="#">Logout</a></li>
              @endif
            </ul>
          </div>
        </div>
      </nav>
        <div class="container">

            @yield('content')
        </div>      

        <script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/main.js') }}"></script>
        @include('templates')

        <script>
            $(document).ready(function () {

                var place = window.location.pathname.split("/")[1].replace("/", "");
                if (place == "" || place == undefined) {
                    place = "products";
                }
                console.log(place);
                $("nav ." + place).addClass("active");

                $(".js-logout").on("click", function (e) {
                    e.preventDefault();
                    ng.cookies.remove("auth");
                    window.location = "/";
                });
            });
        </script>

        @yield('scripts')

    </body>
</html>