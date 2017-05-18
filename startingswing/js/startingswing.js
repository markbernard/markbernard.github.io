var SwingBook = {
    params: {}
};

function setSizes() {
    "use strict";
    var height = $(window).height() - 100;
    var titleHeight = $("h1").height();
    var headerHeight = $(".panel-heading").height();
    var footerHeight = $(".panel-footer").height();

    height -= titleHeight;
    height -= headerHeight;
    height -= footerHeight;
    $(".scroll").css("max-height", height + "px");
}

function parseParameters() {
    "use strict";
    var params = decodeURIComponent(window.location.href.split("?")[1]);
    var keyValues;
    var pair;

    if (!(params === "undefined")) {
        keyValues = params.split("&");
        keyValues.forEach(function (keyValue) {
            pair = keyValue.split("=");
            SwingBook.params[pair[0]] = pair[1];
        });
    }
}

function loadPage() {
    $.ajax({
        url: "/startingswing/" + SwingBook.params["page"] + ".html",
        async: false,
        success: function (result) {
            $('#content').html(result);
            found = true;
        }
    });
}

$(function () {
    parseParameters();
    loadPage();
    setSizes();
});
