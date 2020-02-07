

// ---------------------------------------------------------------------------- header image gallery
function swapImages() {
    var active = jQuery("#header_gallery .act");
    var next = (jQuery("#header_gallery .act").next().length > 0) ? jQuery("#header_gallery .act").next()
        : jQuery("#header_gallery img:first");
    next.css('display', '');
    active.fadeOut(1500, function () {
        next.addClass("act");
        active.removeClass("act");
    });
}

function adjustGallery() {
    jQuery('#header_gallery').children('img').each(function (i) {
        var fromTop = (jQuery(this).height() - jQuery("#header_gallery").height()) / 2 * (-1);
        jQuery(this).css("top", fromTop);
    });
}

function searchByQuery() {
    var item = document.getElementById("search").value;
    var form = document.getElementById("search_form");
    form.action = "/hledej/" + item + "/";
    form.submit();
}


// ========================================================================================> MOBILE (todo move to file that is loaded on mobile only)


// ========================================================================================> RUNNING
jQuery(window).resize(function () {
    adjustGallery();
});

jQuery(document).ready(function () {
    adjustGallery();
    setInterval("swapImages()", 10000);
});

jQuery(window).bind('scroll', function () {
    var amount = 400;
    jQuery("#gallery_shadow").css("opacity", (jQuery(window).scrollTop()) / amount);
    if (jQuery(window).scrollTop() > amount) {
        jQuery('#menu').addClass('fixed');
        jQuery('#web').css("padding-top", jQuery('#menu').height());
        //jQuery('#logo_ymca').addClass('fixed');
        //jQuery("#logo_ymca.fixed").css("top", "8px");
    } else {
        //jQuery("#logo_ymca").css("width", (17600 - (37 * jQuery(window).scrollTop())) / 80);
        //jQuery("#logo_ymca").css("left", (2250 - jQuery(window).scrollTop()) / 150 + "vw");
        //jQuery("#logo_ymca").css("top", (8000 + (61 * jQuery(window).scrollTop())) / 80 + "px");
        jQuery('#web').css("padding-top", "0" /*same value as header bar*/);
        jQuery('#menu').removeClass('fixed');
        //jQuery('#logo_ymca').removeClass('fixed');
    }
});