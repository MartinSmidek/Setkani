let SCROLL_LIMIT_AMOUNT = 300;
var FREE_ROOMS = null;
var FREE_ROOMS_INCL_ORDERS = null;
// ---------------------------------------------------------------------------- header image gallery
function swapImages() {
    if (jQuery(window).width() > 640) { //do not run on mobiles
        let active = jQuery("#header_gallery .act");
        var next = (active.next().length > 0) ? active.next() : jQuery("#header_gallery img:first");
        next.css('display', '');
        active.fadeOut(1500, function () {
            next.addClass("act");
            active.removeClass("act");
        });
    }
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

// ---------------------------------------------------------------------------- orders functionality
function getDaysData(self, ym, y) {
    jQuery(self).toggleClass('ordersSelectedDay' + y); //selection class
    jQuery(self).toggleClass('ordersSelectedDay');     //css class
    var from = 9999999999;
    var to = 0;
    jQuery('.ordersSelectedDay' + y).each(function(i, obj) {
        var current = parseInt(jQuery(obj).attr('content'));
        from = Math.min(from, current);
        to = Math.max(from, current);
    });
    if (from <= to) {
        jQuery('#objednavky-content-' + y).html("<b>Načítám...</b>");
        ask({cmd:'dum', dum:'get_days', fromday:from, untilday: to, year:y}, _getDaysData);
    } else {
        jQuery('#objednavky-content-' + y).addClass("nodisplay");
    }
}
function _getDaysData(ret) {
    let y = ret.year;
    ret = ret.days_data;
    var content = "<div style='overflow-x: scroll;width: 100%;'><table id='dum' class=dum>" + ret['0'];
    console.log(ret);
    let pokoju = parseInt(ret['1']);
    let current_tstamp = Math.floor(Date.now() / 1000);
    var odd_count = 0;
    for(var key in ret){
        let day_stamp = parseInt(key);
        if (day_stamp < 100) continue;

        let day = ret[key];
        let styl= day.obsazenych ? (day.obsazenych===pokoju ? "datum_plno" : "datum_poloplno") : "datum_prazdno";
        let odd_css = odd_count % 2 === 0 ? ' odd' : ' ';
        let date = new Date(day_stamp * 1000);

        let datum = date.getDate() + ". " + (date.getMonth()+1) + ".";
        let weekend= date.getDay();
        let free= weekend===0 || weekend===6 ? "_weekend" : "";
        content += "<tr><td class='datum"+free+odd_css+"'>"+datum+"</td>";
        if ( day.obsazenych===pokoju || day_stamp < current_tstamp ) {
            content += "<td class='order_section "+styl+odd_css+"'>"+pokoj_ikona(-3)+"</td>";
        } else {
            let den = date.getFullYear() + "-" + String(date.getMonth()+1).padStart(2, '0') + "-" +
                String(date.getDate()).padStart(2, '0');
            content += "<td class='order_section sent "+styl+odd_css+"' title='chci objednat pobyt'";
            content += "onclick=\"objednavka(arguments[0],'form',{den:'"+den+"'});return false;\" >";
            content += pokoj_ikona(-1) + "</td>";
        }

        if (day.ico2===0) {
            content += "<td class='order_section "+styl+odd_css+"'></td>";
        } else {
            content += "<td title='objednávka pobytu pro "+day.who+"' class='order_section sent "+styl+odd_css+"'";
            content += "onclick=\"objednavka(arguments[0],'wanted',{orders:'"+day.order+"'});\" >";
            content += pokoj_ikona(-2) + "</td>";
        }

        for(var room in day.pokoje) {
            let pokoj = day.pokoje[room];
            if (pokoj.pset) {
                if (pokoj.s > 1) {
                    content += "<td class='obsazen " + odd_css + "' style='border-right: 1px solid' title='"+pokoj.p+"'";
                    //if (pokoj.s == 5) content += " rowspan='17'";
                    content += " onclick=\"objednavka(arguments[0],'form',{order:" + pokoj.u + "});return false;\" >";
                    content += pokoj_ikona(parseInt(pokoj.s)) + "</td>";
                    //if (pokoj.s == 5) break; //all rooms same request
                } else content += "<td class='nic " + odd_css + "' style='border-right: 1px solid'></td>";
            } else content += "<td class='nic " + odd_css + "' style='border-right: 1px solid'>&nbsp;</td>";
        }
        let note = day.note;
        if (note === null) note = '';
        content += "<td class='pozn"+free+odd_css+"' title='"+note+"'>"+note+"</td></tr>";
        odd_count++;
    }

    var element = jQuery('#objednavky-content-' + y);
    if (odd_count === 0) {
        element.addClass('nodisplay');
    } else {
        element.removeClass('nodisplay');
        element.html(content + "</table></div>");
        jQuery('html, body').animate({scrollTop: (element.offset().top - 180)}, 500);
    }
}
function getRoomsForTimespan(isFromDay, self) {
    if (isFromDay) getRoomsForTimespanImpl(Date.parse(self.value) / 1000, Date.parse(jQuery("#untilday_input").val()) / 1000);
    else getRoomsForTimespanImpl(Date.parse(jQuery("#fromday_input").val()) / 1000, Date.parse(self.value) / 1000);
}
function getRoomsForTimespanImpl(fromday, untilday) {
    //always save last value to potentially restore from other JS functions (not changing time span)
    let roomsTitle = jQuery('#rooms_label');
    roomsTitle.attr("content", roomsTitle.innerHTML);

    if (untilday - fromday > 70000) { //at least one day
        ask({cmd:'dum', dum:'check_rooms', fromday:fromday, untilday: untilday}, _getRoomsForTimespan);
    } else {
        disableDumCheckbox(jQuery("#obj_cely_dum_check"));
        unsetRoomsAllBooked("pokoje: pobyt musí být alespoň na jednu noc");
    }
}
function _getRoomsForTimespan(ret) {
    FREE_ROOMS = ret.free_rooms["content"];
    FREE_ROOMS_INCL_ORDERS = ret.free_rooms["incl_orders"];

    let text = (FREE_ROOMS.length < 1) ? "žádné volné pokoje" : "volné pokoje: " + FREE_ROOMS.join(", ");

    let dumCheckBox = jQuery("#obj_cely_dum_check");
    if (ret.free_rooms["all_free"]) {
        enableDumCheckbox(dumCheckBox);
        if (!dumCheckBox.prop("checked")) unsetRoomsAllBooked(text);
    } else {
        disableDumCheckbox(dumCheckBox);
        unsetRoomsAllBooked(text);
    }
    runOrderCounter();
}
function setRoomsAllBooked() {
    jQuery("#rooms_label").html("pokoje jsou objednány");
    let input = jQuery("#input_rooms_label");
    input.attr("readonly", true);
    input.val("*");
    input.addClass("changed");
}
function unsetRoomsAllBooked(pokojeTitleText, eraseRooms=false) {
    jQuery("#rooms_label").html(pokojeTitleText);
    let input = jQuery("#input_rooms_label");
    input.attr("readonly", false);
    if (eraseRooms || input.attr("value") === "*") {
        input.val("");
        input.removeClass("changed");
    }
}
function getLastRoomsTitle() {
    return jQuery("#rooms_label").attr("content");
}
function disableDumCheckbox(inputField) {
    inputField.attr("disabled", true);
    inputField.prop("checked", false);
    jQuery("#obj_cely_dum_text").html("zájem o celý<br> dům (nelze)");
}
function enableDumCheckbox(inputField) {
    inputField.removeAttr("disabled");
    jQuery("#obj_cely_dum_text").html("zájem o celý<br> dům");
}
function pokoj_ikona(state) {
    switch (state) {
        case -3: return "<i class='fa fa-times'></i>";
        case -2: return "<i class='fa fa-envelope-o'></i>";
        case -1: return "<i class='fa fa-pencil-square-o'></i>";
        case 1: return "";
        case 2: return "<i class='fa fa-user'></i>";
        case 3: return "<i class='fa fa-futbol-o'></i>";
        case 4: return "<i class='fa fa-times-circle'></i>";
 //       case 5: return "<i class='fa fa-home'></i>";
    }
    return '';
}
function popupRoomView(title, roomID) {
    ask({cmd:'dum', dum:'get_room', room_number:roomID, title: title}, _popupRoomView);
}

function _popupRoomView(res) {
    var popup = jQuery('#popup');
    if (popup.length === 0) {
        jQuery('#web').append('<div id=\"popup\"><span>' + res.title +
            '</span><i class=\"fa fa-times popup_close\" style=\"position: absolute;right: 8px;\"' +
            'onclick=\"jQuery(\'#popup\').addClass(\'nodisplay\')\"></i><div id=\"popup_div\"></div></div>');
    } else {
        popup.removeClass('nodisplay');
    }
    jQuery('#popup_div').html(res.room_data);
}

function runOrderCounter() {
    if (FREE_ROOMS == null) {
        //get first free rooms, then calculate the price (called in ..Impl)
        getRoomsForTimespanImpl( Date.parse(jQuery("#fromday_input").val()) / 1000,
            Date.parse( jQuery("#untilday_input").val()) / 1000);
        return;
    }

    var data = getAllInputValues(jQuery("#order"), 'select,input');
    let fromday = Date.parse(data['fromday']),
        untilday = Date.parse(data['untilday']);
    data["days"] = (untilday - fromday) / 86400000;

    if (data["days"] < 1) setPrice("pobyt musí být alespoň na jednu noc",  "", false);
    else if (data["adults"] < 1) setPrice("nelze objednat pobyt bez dospělé osoby", "", false);
    else if (jQuery("#rooms_label").text().startsWith("žádné")) setPrice("nejsou volné pokoje", "", false);
    else if (!data["rooms1"]) setPrice("musíte vybrat pokoje", "", false);
    else ask({cmd:'dum', dum:'get_price', data:data, freeRooms:FREE_ROOMS, inclOrder:FREE_ROOMS_INCL_ORDERS},
            function (res) {setPrice(res.price.celk, res.price.error); jQuery("#info_price").html(res.price.info); });
}
function setPrice(text, warning, isPrice = true) {
    jQuery("#error_price").html(warning);
    if (!isPrice) jQuery("#order_final").html("<span style='color:gray'>"+text+"</span>");
    else jQuery("#order_final").html("&nbsp;"+text+",- Kč");
}


// ========================================================================================> RUNNING
jQuery(window).resize(function () {
    adjustGallery();
    if (jQuery(window).width() > 640) {
        noMobileAdjustMenu(jQuery("#menu"), jQuery(window).scrollTop());
    } else {
        jQuery('#web').css("padding-top", "30px");
        mobileAdjustMenu(jQuery("#menu"), jQuery(window).scrollTop());
    }
});


jQuery(window).on("load", function() {
    setInterval("swapImages()", 10000);
    adjustGallery();
});

jQuery(document).ready(function () {
    adjustGallery(); //once again, images now loaded and might be not centered properly
});

jQuery(window).bind('scroll', function () {
    let menu = jQuery('#menu'),
        win = jQuery(window),
        scrollAmount = win.scrollTop();

    if (win.width() > 640) {
        noMobileAdjustMenu(menu, scrollAmount);
    } else { //mobile
        mobileAdjustMenu(menu, scrollAmount);
    }
});
function mobileAdjustMenu(menu, scrollAmount) {
    let opacity = Math.round((255 * Math.min(SCROLL_LIMIT_AMOUNT, scrollAmount)) / SCROLL_LIMIT_AMOUNT);
    opacity = Math.max(80, opacity);
    menu.css("background", "#ffffff" + opacity.toString(16));
}
function noMobileAdjustMenu(menu, scrollAmount) {
    if (scrollAmount > SCROLL_LIMIT_AMOUNT) {
        menu.addClass('fixed');
        jQuery('#web').css("padding-top", menu.height());
    } else {
        jQuery('#web').css("padding-top", "0" /*same value as header bar*/);
        menu.removeClass('fixed');
    }
    menu.css("background", "#ffffff");
    jQuery("#gallery_shadow").css("opacity", (scrollAmount) / SCROLL_LIMIT_AMOUNT);
}