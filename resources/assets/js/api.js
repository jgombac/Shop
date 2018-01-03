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

    fileUpload: function (url, data, authenticate) {

        var headers = {};
        if (authenticate != undefined && authenticate) {
            headers["auth"] = ng.cookies.get("auth");
        }

        return $.ajax({
            dataType: "json",
            type: "POST",
            url: "/api/" + url,
            data: data,
            headers: headers,
            cache: false,
            contentType: false,
            processData: false,   
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

    removeImage: function (data) {
        return ng.api.call("POST", "products/image", data, true);
    },

    addImage: function (data) {
        return ng.api.fileUpload("products/image", data, true);
    },

    addProduct: function (data) {
        return ng.api.fileUpload("products/add", data, true);
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