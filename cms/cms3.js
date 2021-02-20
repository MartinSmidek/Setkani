/* global Ezer */

// ---------------------------------------------------------------------------------------------- //
// uživatelské funkce aplikace Ezer/CMS společné pro FE (nepřihlášené) a BE (přihlášené)          //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //
// =========================================================================================> COMMON
// ----------------------------------------------------------------------------------------------- jQuery
function $() {
  // mootools relikt
  Ezer.fce.error("MooTools $-call");
  return 1;
}
function $$() {
  // mootools relikt
  Ezer.fce.error("MooTools $$-call");
  return 1;
}
// ----------------------------------------------------------------------------------------- fe init
// inicializace v případě volání z CMS $.onstart
function fe_init() {
  if ( Ezer.cms==undefined ) {
    Ezer.cms= {form:{}};
  }
}
// =========================================================================================> COMMON
jQuery.fn.extend({
  // ------------------------------------------------- + scrollIntoViewIfNeeded
  Ezer_scrollIntoView: function() {
    var target= this[0];
    let rect = target.getBoundingClientRect(),
        bound= this.parent()[0].getBoundingClientRect();
    if (rect.bottom > bound.bottom) {
        target.scrollIntoView(false);
    }
    else if (rect.top < bound.top) {
        target.scrollIntoView(true);
    }
  }
});
// -------------------------------------------------------------------------------------- jump fokus
// nastaví polohu stránky
// zamění <span style='neodkaz'> na alert
function jump_fokus(id) {
  var jump;
  // pokud je cíl definován
  if ( id ) {
    jump= jQuery('#'+id);
  }
  else {
  // najdi cíl podle priority
    jump= jQuery('#fokus_part');
    if ( !jump.length )
      jump= jQuery('#fokus_case');
    if ( !jump.length )
      jump= jQuery('#fokus_page');
  }
  if ( jump.length ) {
    jump[0].scrollIntoView(true);
  }
  return 1;
}
// ----------------------------------------------------------------------------------- block display
// nastaví display podle parametru
function block_display(id,on) {
  var block= jQuery('#'+id);
  if ( block ) {
    var shadow = jQuery('#web-shadow')
    if (on) {
      block.css('display','block');
      shadow.css('display', 'block');
    } else {
      block.css('display','none');
      shadow.css('display', 'none');
    }
  }

  return 1;
}
// ------------------------------------------------------------------------------------ block enable
// nastaví enable podle parametru - případně vynechá name=but
function block_enable(id,on,but) {
  var block= jQuery('#'+id);
  if ( block ) {
    block.disabled= 1-on;
    var elems= block.find('select,input');
    elems.each(function() {
      if ( this.name && this.name!==but) {
        this.disabled= 1-on;
      }
    });
  }
  return 1;
}
// ===========================================================================================> MODE
// -----------------------------------------------------------------------------------==> . bar menu
function close_mobile_menu() {
  jQuery('#web-shadow').css('display', 'none');
  return close_mobile_menu_leave_shadow();
}
function close_mobile_menu_leave_shadow() {
  var items= jQuery('#mobile_menu'), cross=jQuery("#menu-cross");
  items.removeClass("is-open");
  cross.addClass("nodisplay");
  return true;
}

function bar_menu(e,x) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  let is_mobile_menu = jQuery(window).width() <= 623;

  var items= jQuery(is_mobile_menu ? '#mobile_menu' : '#bar_items'),
      body= jQuery(document.body),
      bar=jQuery(e.toElement),
      cross=jQuery("#menu-cross");

  if ( x==='menu_on' ) {   //opened
    if (!items.hasClass("is-open")) {
      items.addClass("is-open");
      if (is_mobile_menu) {
        jQuery('#web-shadow').css('display', 'block');
        cross.removeClass("nodisplay");
      }
    } else if (!is_mobile_menu) {
      items.removeClass("is-open");
    }
  } else {
      switch (x) {
        case 'new1':
        case 'new7':
        case 'new30':
          var dnu= x.substr(3);
          document.cookie= 'web_show_changes='+dnu+';path=/';
          refresh();
          break;
        case 'grid': change_mode(1,1); break;
        case 'rows': change_mode(1,0); break;
        case 'fe_login':
          jQuery('#user_login').css({display:'block'}).removeClass('key_in').data('login','fe');
          if (is_mobile_menu) close_mobile_menu_leave_shadow();
          else jQuery('#web-shadow').css({display:'block'});
          return true;
        case 'be_login':
          jQuery('#user_login').css({display:'block'}).addClass('key_in').data('login','be');
          if (is_mobile_menu) close_mobile_menu_leave_shadow();
          else jQuery('#web-shadow').css({display:'block'});
          return true;
        case 'me_login':
          jQuery('#user_mail').css({display:'block'}).addClass('key_in').data('login','me');
          if (is_mobile_menu) close_mobile_menu_leave_shadow();
          else jQuery('#web-shadow').css({display:'block'});
          return true;
        default:
          return true;
      }

      if (is_mobile_menu) {
        close_mobile_menu();
      } else {
        items.removeClass("is-open");
      }
  }

  return false;
}
// -----------------------------------------------------------------------------------==> . me login
function me_login(page) {
  me_ip({run:me_login_,page:page});
}
function me_login_(page,myip) {
  var mail= jQuery('#mail').val(), pin= jQuery('#pin').val();
  ask({cmd:'me_login',mail:mail,pin:pin,page:page,web:'setkani.org',cond:'web',local_ip:myip},me_login__);
}
function me_login__(y) {
  if ( y && y.txt ) {
    jQuery('#user_mail_txt').html(y.txt);
  }
  if ( y && y.msg ) {
    jQuery('#user_mail').html(y.msg);
  }
  if ( y && y.fe_user ) {
    refresh();
  }
}
function me_ip(then) {
  then.run(then.page,'-'); return;                              // vypnutí
//   // podle https://github.com/diafygi/webrtc-ips
//   window.RTCPeerConnection= window.RTCPeerConnection
//     || window.mozRTCPeerConnection
//     || window.webkitRTCPeerConnection;                          // compatibility for firefox and chrome
//   if ( !window.RTCPeerConnection )                              // for edge not yet, ... later we use
//     then.run(then.page,'?');                                    // https://github.com/webrtc/adapter
//   var pc= new RTCPeerConnection({iceServers:[]}),
//       noop= function(){};
//   pc.createDataChannel("");                                     //create a bogus data channel
//   pc.createOffer(pc.setLocalDescription.bind(pc), noop);        // create offer and set local description
//   pc.onicecandidate= function(ice){                             //listen for candidate events
//     if (!ice || !ice.candidate || !ice.candidate.candidate)
//       return;
//     var myIP= /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/
//       .exec(ice.candidate.candidate)[1];
//     console.log('my IP: ', myIP);
//     then.run(then.page,myIP);
//     pc.onicecandidate= noop;
//   };
}
// ------------------------------------------------------------------------------------- change mode
// mode=1 - dlaždicové zobrazení
function change_mode(mode,on) {
//   var on= jQuery('#bar_menu').getProperty('data-mode');
  switch (mode) {
  case 1:
    document.cookie= 'web_mode_1='+on+';path=/';
    refresh();
    break;
  }
}
// ------------------------------------------------------------------------------------- change info
// alternace informačního rohu
function change_info() {
  var info= jQuery('#info');
  if ( info ) {
    var on= info.css('display');
    info.css('display',on==='block'?'none':'block');
//    if ( on==='none' ) {
//      var scr= info.getElement('#info_screen');
//      var xy= document.getElement('body').getSize();
//      scr.html(xy.x+'*'+xy.y+'<br>('+screen.width+'*'+screen.height+')');
//    }
  }
}
// ========================================================================================> HISTORY
// ----------------------------------------------------------------------------------- history push2
function history_push2(href) {
  history.pushState({},'',href);
}
// ------------------------------------------------------------------------------------ history push
//todo probably delete
function history_push(href,checks,level,kdy) {
  var ref='', named;
  checks.split(',').forEach(function(check) {
    named= jQuery('input[name^="'+check+'"]');
    named.each(function() {
      if ( this.checked )
        ref+= (ref?',':'')+this.value;
    });
  });
  var re= level===1 ? /[?&]page=(\w+)(.*)/ : /[?&]page=\w+!(\w+)(.*)/,
      m= re.exec(href);
  ref= ref||m[1];
// Cookie.write(id,v,{duration:100});
  let duration= 100, // days
      date= new Date();
  date.setTime(date.getTime() + duration * 24 * 60 * 60 * 1000);
  document.cookie= m[1] + '=' + encodeURIComponent(ref) + ';expires=' + date.toGMTString();
  if ( Ezer.version===undefined ) {
    window.location= '/'+ref;
  }
  else {
    go(0,href+'!'+ref+(kdy?','+kdy:''),'/'+ref);
  }
}
// ------------------------------------------------------------------------------------ history back
function history_back() {
  history.back();
}

function proc_kdo(href, kdy) {
  jQuery(this).parent().toggleClass("checked");
  let named= jQuery('input[name^="komu"]');
  var ref='';
  named.each(function() {
    if ( this.checked )
      ref+= (ref?',':'')+this.value;
  });
  if (ref === '') {
    ref = 'rodiny,manzele,chlapi,zeny,mladez';
  }

  let date = new Date();
  date.setTime(date.getTime() + 365 * 24 * 60 * 60 * 1000);
  document.cookie = 'akce=' + ref + ';expires=' + date.toGMTString(); + '; path=/';

  if ( Ezer.version===undefined ) {
    window.location= '/akce';
  } else {
    go(0,href+'!akce'+(kdy?','+kdy:''),'/'+ref);
  }
}

// ============================================================================================> MSG
// ------------------------------------------------------------------------------------ go chlapi_cz
// zobrazí upozornění a přepne na stránku na webu chlapi.cz
function go_chlapi_cz(ref) {
  msg_on("Plný text této akce najdete na webu chlapi.cz","Přepnutí na chlapi.cz za 3s");
  setTimeout(function(){ location.href= ref; }, 3000);
}
// ------------------------------------------------------------------------------------------ msg on
// zobrazí zprávu - alert
function msg_on(text,title) {
  if ( !title ) title= 'Upozornění';
  jQuery('#msg div.box_title').html(title);
  jQuery('#msg div.box_text').html(text);
  jQuery('#msg').css({display:'block'});
}
// ------------------------------------------------------------------------------------------ msg on
// zobrazí zprávu - alert
function msg4_on(text,title) {
  if ( !title ) title= 'Upozornění';
  jQuery('#user_msg span').html(title);
  jQuery('#user_msg div').html(text);
  jQuery('#user_msg').css({display:'block'});
}
// ----------------------------------------------------------------------------------------- box off
// zhasne všechny typy boxů
function box_off() {
  jQuery('div.box').css({display:'none'});
}
// ==========================================================================================> TABLE
var table_x= {};
// -------------------------------------------------------------------------------------- table test
// vyhodnotí odpověď na testovací otázky a případně přihlásí jako fe_host
function table_test(e) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  var prompt= jQuery('#prompt');
  prompt.find('input').val('');
  prompt.css({display:'block'});
}
function _table_test(test) {
  ask({cmd:'table_tst',test:test},_table_test_);
}
function _table_test_(y) {
  if ( y.ok )
    refresh();
  else {
    jQuery('#prompt').css({display:'none'});
    msg_on("Richard "+(y.test?y.test:'---')+"? <br><br>to nebylo dobře :-(");
  }
}
// -------------------------------------------------------------------------------------- table add1
// zobrazí jméno přihlášeného účastníka jako vzor
function table_add1(e,den,skup,cid) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  jQuery('#skupiny input').css({display:'none'});
  var input= jQuery('#table-'+skup);
  if ( window['Ezer'] && Ezer.web && Ezer.web.fe_username ) input.val(Ezer.web.fe_username);
  input.css({display:'block'});
}
// -------------------------------------------------------------------------------------- table add2
// přidá účastníka do skupiny
function table_add2(e,den,skup,cid) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  var input= jQuery('#table-'+skup);
  table_x= {cmd:'table_add2',datum:den,skupina:skup,jmeno:input.val(),cid:cid};
  ask(table_x,_table_add2);
}
function _table_add2(y) {
  if ( y.msg ) {
    if ( y.choose )
      table_choose(y.msg,'Volba z možností');
    else
      msg_on(y.msg);
  }
  else
    refresh();
}
// ------------------------------------------------------------------------------------ table choose
// zobrazí zprávu s výběrovými tlačítky
function table_choose(text,title) {
  jQuery('#table_choose div.box_title').html(title);
  jQuery('#table_choose div.box_text').html(text);
  jQuery('#table_choose').css({display:'block'});
}
// -------------------------------------------------------------------------------------- table chng
// vyjme účastníka ze skupiny
function table_chng(op) {
  table_x.cmd= 'table_'+op;
  ask(table_x,_table_chng);
}
function _table_chng(y) {
  if ( y.msg ) {
    msg_on(y.msg);
  }
  else
    refresh();
}
// =====================================================================================> OBJEDNAVKA
// -------------------------------------------------------------------------------------- objednavka
// den je Y-m-d prvního dne objednávky
// vrátí hodnoty formuláře tzn. input, select jako json
// pro create a update p.rooms=seznam pokojů pro match
function objednavka(e,op,p,self=null) {
  function verify() {
    // kontroly správného vyplnění
    let jmeno= x.form['name'].replace(' ',''),
        email= x.form['email'].replace(' ','');
    if ( !jmeno ) errors["ord_name_error"] = "Napište prosím své <b>jméno a příjmení </b> abychom vás mohli kontaktovat.";
    if ( !email ) errors["ord_email_error"] = "Chybí emailová adresa.";
    else if ( !email.match(/\S+@\S+\.\S+/) ) errors["ord_email_error"] = "Zadaná emailová adresa '" + email + "' není korektní.";
    if (jQuery("#rooms_label").text().startsWith("žádné")) errors["ord_date_error"] = "Žádné volné pokoje ve zvoleném období.";
    for (let fld of ['rooms1','adults','untilday']) {
      let val= x.form[fld];
      if (val == null) { continue; } //todo error? these are compulsory...
      val= val.replace(' ','').trim();
      switch (fld) {
        case 'rooms1':{
          let pokoj= `(${p.rooms})`,
              qry= new RegExp(`^\s*\\*|${pokoj}(\s*,\s*${pokoj})*\s*$`,'g');
          if (!val) {
            errors["ord_rooms_error"] = "Objednejte pokoje jejich číslem (detaily jsou v záhlaví tabulky).";
          } else if (!qry.test(val)) {
            errors["ord_rooms_error"] = "Čísla <b>existujících</b> pokojů musí být oddělena čárkou.";
          }
          break;}
        case 'adults':{
          if ( !val || val < 1 ) {
            errors["ord_adults_error"] = "Zadejte předpokládaný počet <b>dospělých</b> osob.";
          }
          break;
        }
        case 'untilday': {
          let fromday = Date.parse(x.form['fromday']),
              untilday = Date.parse(val),
              max_days = 22,
              days = (untilday - fromday) / 86400000;
          if (!val || isNaN(days)) {
            errors["ord_date_error"] = "Opravte prosím <b>datum pobytu</b>.";
          } else if (days < 0 || days > max_days) {
            errors["ord_date_error"] = "Délka pobytu musí být mezi jedním dnem a 21 dny.";
          }
          // convert to UNIX timestamp
          // x.form['fromday-str'] = x.form['fromday'];
          // x.form['untilday-str'] = x.form['untilday'];
          x.form['fromday'] = Math.abs(fromday / 1000);
          x.form['untilday'] = Math.abs(untilday / 1000);
          break;
        }
      }
    }
  }
  //todo perform date parsing on the server side?
  function convertDate() {
    // x.form['fromday-str'] = x.form['fromday'];
    // x.form['untilday-str'] = x.form['untilday'];
    if (x.form.hasOwnProperty("fromday")) x.form['fromday'] = Math.abs(Date.parse(x.form['fromday']) / 1000);
    if (x.form.hasOwnProperty("untilday")) x.form['untilday'] = Math.abs(Date.parse(x.form['untilday']) / 1000);
  }

  if ( e ) e.stopPropagation();
  var x= {cmd:'dum',dum:op},
      f= jQuery('#order'),
      errors= {};
  orderHideErrors(f);

  switch (op) {
  case 'wanted':{
    x.orders= p.orders;
    x.order= p.order||0;
    jQuery('#web-shadow').css('display', 'block');
    break;}
  case 'form':{
    x.den= p.den;
    x.order= p.order||0;
    jQuery('#web-shadow').css('display', 'block');
    break;}
  case 'create':{
    x.order= 0;
    x.form= getAllInputValues(f, 'select,input');
    verify();
    break;}
  case 'delete':{
    x.order= p && p.order ? p.order : 0;
    if (!confirm('Opravdu smazat objednávku číslo ' + x.order + '?')) return;
    break;}
  case 'update':{
    x.order= p && p.order ? p.order : 0;
    x.form= getAllInputValues(f, 'select,input');
    verify();
    x.form= getAllInputValues(f, 'select.changed,input.changed');
    convertDate();
    break;}
  default:{
    alert('objednavka('+op+'/'+p+') NYI !!!');
    break; }
  }

  var counter = 0;
  for( var key in errors ) {
    orderShowError(key, errors[key]);
    counter++;
  }
  if (counter === 0) {
    jQuery(self).attr("value", "Prosím čekejte...");
    ask(x,_objednavka, self);
  }
}
function _objednavka(y, caller) {
  if ( !y ) return;
  var tit= jQuery('#order_tit');
  switch (y.dum) {
  case 'wanted':{
    var order= jQuery('#order'), div= jQuery('#order_div');
    order.css('display','block');
    div.html(y.html);
    var n= 0, list='', del= '', orders= y.orders.split(',');
    orders.forEach(ord => {
      if ( ord!==y.order ) {
        ord= "<u style='cursor: pointer;' onclick=\"objednavka(0,'wanted',\{orders:'"+y.orders+"',order:'"+ord+"'\})\">"+ord+"</u>";
      }
      else {
        ord= "<span class='choose' style='font-size: 15pt;padding: 0 5px; cursor: default;'>"+ord+"</span>";
      }
      list+= del+ord;
      del= ', ';
      n++;
    });
    tit.html("Čekající objednávk"+(n===1?"a ":"y: ")+list);
    break;}
  case 'form':{
    var order= jQuery('#order'), div= jQuery('#order_div');
    order.css('display','block');
    div.html(y.html);
    tit.html(
      y.dum==='wanted' ? "Čekající objednávky "+y.orders : (
      y.order==0 ? 'Nová objednávka' : 'Objednávka '+y.order
    ));
    break;}
  case 'create':{
    if ( y.ok ) {
      block_display('order', 0);
    } else {
      msg4_on(y.msg, "Chyba v objednávce");
      jQuery(caller).attr("value", "Přidat objednávku.");
      return;
    }
    if ( y.hasOwnProperty('completion') ) {
      let msg = jQuery("#order_completion");
      msg.removeClass('nodisplay');
      msg.html(y.completion);
      jQuery('html, body').animate({scrollTop: (msg.offset().top - 200)}, 500);
    }
    return;}
  case 'delete':{
    tit.html(y.ok ? 'Objednávka '+y.order + ' byla smazána' : 'Smazání se nepovedlo');
    if ( y.ok ) block_display('order',0);
    refresh();
    break;}
  case 'update':{
    tit.html(y.ok ? 'Objednávka '+y.order + ' byla upravena' : 'Oprava se nepovedla');
    if ( y.ok ) block_display('order',0);
    refresh();
    break;}
  }
  if ( y.msg ) alert(y.msg);
}
function getAllInputValues(rootElem, selector) {
  var result = {};
  rootElem.find(selector).each(function() {
    if ( this.name ) {
      if (this.name === 'obj_cely_dum') return;
      result[this.name]= this.value;
    }
  });
  return result;
}
function orderShowError(errElemId, msg) {
  let element = jQuery("#" + errElemId);
  element.removeClass("nodisplay");
  element.html(msg);
  element.addClass("block");
}
function orderHideErrors(parentElement) {
  parentElement.find(".order_error_msg").each(function () {
    jQuery(this).addClass("nodisplay");
    jQuery(this).removeClass("block");
  });
}
// ===========================================================================================> FOTO
// --------------------------------------------------------------------------------------- foto show
// n je pořadí dvojice
function foto_show(e,n) {
  if ( e ) e.stopPropagation();
  var popup= jQuery('#popup');
  var lst= popup.data('foto-lst');
  lst= lst.split('~');
  var film= lst[1].split(',');       // včetně popisů
  var n2= n*2;
  popup.css('display','block');
  if ( n>=0 && n2<film.length-1 ) {
    var path= "fileadmin/photo/"+lst[0]+"/."+film[n2];
    var title= film[n2+1].replace(/##44;/g,',');
    popup.data('foto-n',n);
    jQuery('#popup_div').html("<img src='"+path+"'>");
    jQuery('#popup_bot').html(title);
  }
}
// --------------------------------------------------------------------------------------- foto next
function foto_next(forward) {
  var n= jQuery('#popup').data('foto-n').toInt();
  foto_show(0,n+forward);
}
// --------------------------------------------------------------------------------------- foto back
function foto_back() {
  jQuery('#popup').css('display','none');
}
// ===================================================================================> LOGIN, LOGOUT
// --------------------------------------------------------------------------------------- be logout
function be_logout(page) {
  ask({cmd:'be_logout',page:page},_be_logout,'jo?');
}
function _be_logout(y) {
  window.location= 'index.php?page='+y.page;
}
// ===========================================================================================> AJAX
// --------------------------------------------------------------------------------------------- ask
// ask(x,then): dotaz na server se jménem funkce po dokončení
function ask(x,then,arg) {
  var xx= x;
  x.totrace= Ezer&&Ezer.App&&Ezer.App.options ? Ezer.App.options.ae_trace : 'u';
  x.secret= "WEBKEYNHCHEIYSERVANTAFVUOVKEYWEB";
  jQuery.ajax({url:Ezer.web.index, data:x, method: 'POST',
    success: function(y) {
      if ( typeof(y)==='string' )
        error("Došlo k chybě 1 v komunikaci se serverem - '"+xx.cmd+"'");
      else {
        if ( y.error )
          Ezer.error(y.error,'C');
        if ( y.trace && Ezer.trace )
          Ezer.trace('u',"<span style='color:#cc0000'>"+y.trace+"</span>");
        if ( y.warning )
          Ezer.fce.warning(y.warning);
        if ( then ) {
          then.apply(undefined,[y,arg]);
        }
      }
    },
    error: function(xhr) {
      error("Došlo k chybě 3 v komunikaci se serverem");
    }
  });
}
// ------------------------------------------------------------------------------------------- error
function error(msg) {
  alert(msg + " pokud napises na martin@smidek.eu pokusim se pomoci, Martin");
}
