var ng = ng || {};

ng.api = {
    call: function (type, url, data, authenticate) {

        var headers = {};
        if (authenticate != undefined && authenticate) {
            headers["auth"] = ng.cookies.get("auth");
        }

        return $.ajax({
            type: type,
            url: "/api/" + url,
            contentType: "application/json",
            dataType: "json",
            data: data != null ? JSON.stringify(data) : "",
            headers: headers,
            async: true,
            cache: false,
        });
    },

    login: function (data) {
        return ng.api.call("POST", "login", data);
    },

    register: function (data) {
        return ng.api.call("POST", "register", data);
    },

    updateProfile: function (data) {
        return ng.api.call("POST", "profile", data, true);
    },

    updateUser: function (data) {
        return ng.api.call("POST", "userupdate", data, true);
    },

    updateProduct: function (data) {
        return ng.api.call("POST", "products/update", data, true);
    },

    rateProduct: function (data) {
        return ng.api.call("POST", "products/rate", data, true);
    },

    updateOrder: function (data) {
        return ng.api.call("POST", "orders/update", data, true);
    },

    finishOrder: function (data) {
        return ng.api.call("POST", "cart/finish", data, true);
    },

    updateCart: function (data) {
        return ng.api.call("POST", "cart/update", data, true);
    },

    getLatestOrder: function () {
        return ng.api.call("GET", "cart", null, true);
    },

    getCustomers: function () {
        return ng.api.call("GET", "customers", null, true);
    },

    getSellers: function () {
        return ng.api.call("GET", "sellers", null, true);
    },

    getProducts: function () {
        return ng.api.call("GET", "products", null, true);
    },

    getProduct: function (id) {
        return ng.api.call("GET", "products/" + id, null);
    },

    getOrders: function () {
        return ng.api.call("GET", "orders", null, true);
    },

    getProfile: function () {
        return ng.api.call("GET", "profile", null, true);
    },

    
}
ng.cookies = {
    set: function(name, value, days) {
        var expires;

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        } else {
            expires = "";
        }
        document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
    },

    get: function(name) {
        var nameEQ = encodeURIComponent(name) + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    },

    remove: function(name) {
        ng.cookies.set(name, "", -1);
    }

}