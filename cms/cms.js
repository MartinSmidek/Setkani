/* global Ezer, Cookie */

// ---------------------------------------------------------------------------------------------- //
// uživatelské funkce aplikace Ezer/CMS společné pro FE (nepřihlášené) a BE (přihlášené)          //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //
// =========================================================================================> COMMON
// ----------------------------------------------------------------------------------------- fe init
// inicializace v případě volání z CMS $.onstart
function fe_init() {
  if ( Ezer.cms==undefined ) {
    Ezer.cms= {form:{}};
  }
}
// -------------------------------------------------------------------------------------- jump fokus
function jump_fokus() {
  // najdi cíl podle priority
  var jump=$('fokus_part') || $('fokus_case') || $('fokus_page');
  if ( jump )
    jump.scrollIntoView(true);
  return 1;
}
// ----------------------------------------------------------------------------------- block display
// nastaví display podle parametru
function block_display(id,on) {
  var block= $(id);
  if ( block )
    block.setStyle('display',on ? 'block' : 'none');
  return 1;
}
// ------------------------------------------------------------------------------------ block enable
// nastaví enable podle parametru - případně vynechá name=but
function block_enable(id,on,but) {
  var block= $(id);
  if ( block ) {
    block.disabled= 1-on;
    var elems= block.getElements('select');
    elems= elems.append(block.getElements('input'));
    elems.each(function(elem) {
      if ( elem.name && elem.name!==but) {
        elem.disabled= 1-on;
      }
    });
  }
  return 1;
}
// ===========================================================================================> MODE
// -----------------------------------------------------------------------------------==> . bar menu
function bar_menu(e,x) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  var items= $('bar_items'), body= $(document.body);
  var off= function(e) {
    items.setStyles({display:'none'});
    body.removeEvents({click:off,contextmenu:off});
  };
  if ( x==='menu_on' ) {
    items.setStyles({display:'block'});
    (function(){
      body.addEvents({click:off,contextmenu:off});
    }).delay(1);
  }
  else {
    switch (x) {
    case 'new1': case 'new7': case 'new30':
      var dnu= x.substr(3);
      document.cookie= 'web_show_changes='+dnu+';path=/';
      refresh();
      break;
    case 'grid': change_mode(1,1); break;
    case 'rows': change_mode(1,0); break;
    case 'fe_login':
      $('user_login').setStyles({display:'block'}).removeClass('key_in').setAttribute('data-login','fe');
      break;
    case 'be_login':
      $('user_login').setStyles({display:'block'}).addClass('key_in').setAttribute('data-login','be');
      break;
    case 'me_login':
      $('user_mail').setStyles({display:'block'}).addClass('key_in').setAttribute('data-login','me');
      break;
    }
    items.setStyles({display:'none'});
  }
  return false;
}
// -----------------------------------------------------------------------------------==> . me login
function me_login(page) {
  me_ip({run:me_login_,page:page});
}
function me_login_(page,myip) {
  var mail= $('mail').value, pin= $('pin').value;
  ask({cmd:'me_login',mail:mail,pin:pin,page:page,web:'setkani.org',cond:'web',local_ip:myip},me_login__);
}
function me_login__(y) {
  if ( y && y.txt ) {
    $('user_mail_txt').set('html',y.txt);
  }
  if ( y && y.msg ) {
    $('user_mail').set('html',y.msg);
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
//   var on= $('bar_menu').getProperty('data-mode');
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
  var info= $('info');
  if ( info ) {
    var on= info.getStyle('display');
    info.setStyle('display',on==='block'?'none':'block');
    if ( on==='none' ) {
      var scr= info.getElement('#info_screen');
      var xy= document.getElement('body').getSize();
      scr.set('html',xy.x+'*'+xy.y+'<br>('+screen.width+'*'+screen.height+')');
    }
  }
}
// ========================================================================================> HISTORY
// ----------------------------------------------------------------------------------- history push2
function history_push2(href) {
  history.pushState({},'',href);
}
// ------------------------------------------------------------------------------------ history push
function history_push(href,checks,level,kdy) {
  var ref='', named, check;
  checks.split(',').each(function(check) {
    named= $$('input[name^="'+check+'"]');
    named.each(function(el) {
      if ( el.checked )
        ref+= (ref?',':'')+el.value;
    });
  });
  var re= level===1 ? /[?&]page=(\w+)(.*)/ : /[?&]page=\w+!(\w+)(.*)/,
      m= re.exec(href);
  ref= ref||m[1];
  Cookie.write(m[1],ref,{duration:100});
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
// ============================================================================================> MSG
// ------------------------------------------------------------------------------------------ msg on
// zobrazí zprávu - alert
function msg_on(text,title) {
  if ( !title ) title= 'Upozornění';
  $$('#msg div.box_title').set('html',title);
  $$('#msg div.box_text').set('html',text);
  $$('#msg').setStyles({display:'block'});
}
// ----------------------------------------------------------------------------------------- box off
// zhasne všechny typy boxů
function box_off() {
  $$('div.box').setStyles({display:'none'});
}
// ==========================================================================================> TABLE
var table_x= {};
// -------------------------------------------------------------------------------------- table test
// vyhodnotí odpověď na testovací otázky a případně přihlásí jako fe_host
function table_test(e) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  var prompt= $('prompt');
  prompt.getElement('input').value= '';
  prompt.setStyles({display:'block'});
}
function _table_test(test) {
  ask({cmd:'table_tst',test:test},_table_test_);
}
function _table_test_(y) {
  if ( y.ok )
    refresh();
  else {
    $('prompt').setStyles({display:'none'});
    msg_on("Richard "+(y.test?y.test:'---')+"? <br><br>to nebylo dobře :-(");
  }
}
// -------------------------------------------------------------------------------------- table add1
// zobrazí jméno přihlášeného účastníka jako vzor
function table_add1(e,den,skup,cid) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  $$('#skupiny input').setStyles({display:'none'});
  var input= $('table-'+skup);
  if ( window['Ezer'] && Ezer.web && Ezer.web.fe_username ) input.value= Ezer.web.fe_username;
  input.setStyles({display:'block'});
}
// -------------------------------------------------------------------------------------- table add2
// přidá účastníka do skupiny
function table_add2(e,den,skup,cid) {
  if ( e ) { e.stopPropagation(); e.preventDefault(); }
  var input= $('table-'+skup);
  table_x= {cmd:'table_add2',datum:den,skupina:skup,jmeno:input.value,cid:cid};
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
  $$('#table_choose div.box_title').set('html',title);
  $$('#table_choose div.box_text').set('html',text);
  $$('#table_choose').setStyles({display:'block'});
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
function objednavka(e,op,p) {
  if ( e ) e.stopPropagation();
  var x= {cmd:'dum',dum:op}, f= $('order');
  switch (op) {
  case 'wanted':
    x.orders= p.orders;
    x.order= p.order||0;
    break;
  case 'form':
    x.den= p.den;
    x.order= p.order||0;
    break;
  case 'create':
    x.order= 0;
    x.form= {};
    var elems= f.getElements('select');
    elems= elems.append(f.getElements('input'));
    elems.each(function(elem) {
      if ( elem.name ) {
        x.form[elem.name]= elem.value;
      }
    });
    break;
  case 'update':
    x.order= p && p.order ? p.order : 0;
    x.form= {};
    var elems= f.getElements('select');
    elems= elems.append(f.getElements('input'));
    elems.each(function(elem) {
      if ( elem.name && elem.hasClass('changed') ) {
        x.form[elem.name]= elem.value;
      }
    });
    break;
  default: alert('objednavka('+op+') NYI');
  }
  ask(x,_objednavka);
}
function _objednavka(y) {
  if ( !y ) return;
  var tit= $('order_tit');
  switch (y.dum) {
  case 'wanted':
    var order= $('order'), div= $('order_div'), tit= $('order_tit');
    order.setStyle('display','block');
    div.set('html',y.html);
    var n= 0, list= del= '', orders= y.orders.split(',');
    orders.each(function(ord) {
      if ( ord!==y.order ) {
        var p= "{orders:'"+y.orders+"',order:'"+ord+"'}";
        ord= "<u onclick=\"objednavka(0,'wanted',"+p+")\">"+ord+"</u>";
      }
      else {
        ord= "<span class='choose'>"+ord+"</span>";
      }
      list+= del+ord;
      del= ', ';
      n++;
    });
    tit.set('html',"Čekající objednávk"+(n===1?"a ":"y: ")+list);
    break;
  case 'form':
    var order= $('order'), div= $('order_div'), tit= $('order_tit');
    order.setStyle('display','block');
    div.set('html',y.html);
    tit.set('html',
      y.dum==='wanted' ? "Čekající objednávky "+y.orders : (
      y.order===0 ? 'Nová objednávka' : 'Objednávka '+y.order
    ));
    break;
  case 'create':
    tit.set('html','Objednávka '+y.order + ' byla zaslána správci Domu setkání');
    if ( y.ok ) block_display('order',0);
    refresh();
    break;
  case 'update':
    tit.set('html',y.ok ? 'Objednávka '+y.order + ' byla upravena' : 'Oprava se nepovedla');
    if ( y.ok ) block_display('order',0);
    refresh();
    break;
  }
  if ( y.msg ) alert(y.msg);
}
// ===========================================================================================> FOTO
// --------------------------------------------------------------------------------------- foto show
// n je pořadí dvojice
function foto_show(e,n) {
  if ( e ) e.stopPropagation();
  var popup= $('popup');
  var lst= popup.getProperty('data-foto-lst');
  lst= lst.split('~');
  var film= lst[1].split(',');       // včetně popisů
  var n2= n*2;
  popup.setStyle('display','block');
  if ( n>=0 && n2<film.length-1 ) {
    var path= "fileadmin/photo/"+lst[0]+"/."+film[n2];
    var title= film[n2+1].replace(/##44;/g,',');
    popup.setProperty('data-foto-n',n);
    $('popup_div').set('html',"<img src='"+path+"'>");
    $('popup_bot').set('html',title);
  }
}
// --------------------------------------------------------------------------------------- foto next
function foto_next(forward) {
  var n= $('popup').getProperty('data-foto-n').toInt();
  foto_show(0,n+forward);
}
// --------------------------------------------------------------------------------------- foto back
function foto_back() {
  $('popup').setStyle('display','none');
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
  x.totrace= Ezer&&Ezer.App&&Ezer.App.options ? Ezer.App.options.ae_trace : 'u';
  x.secret=  "WEBKEYNHCHEIYSERVANTAFVUOVKEYWEB";
  var ajax= new Request({url:'index.php', data:x, method:'post',
    onSuccess: function(ay) { var y;
      try { y= JSON.decode(ay); } catch (e) { 
        y= null; 
        error('Doslo k chybe 1 v komunikaci se serverem');
//        error('Oh, AJAX error (1) ['+ay+']'); 
      }
      if ( y ) {
        if ( y.error && Ezer && Ezer.error )
          Ezer.error(y.error,'C');
        if ( y.trace && Ezer && Ezer.trace )
          Ezer.trace('u',"<span style='color:#cc0000'>"+y.trace+"</span>");
        if ( y.warning )
          Ezer.fce.warning(y.warning);
      }
      else {
        error('Doslo k chybe 2 v komunikaci se serverem');
//        error('Oh, AJAX error (2) ['+ay+']');
      }
      if ( then ) {
        then.apply(undefined,[y,arg]);
      }
    },
    onFailure: function() { 
      error('Doslo k chybe 3 v komunikaci se serverem');
//      error('AJAX error (3)'); 
    }
  });
  ajax.send();
}
// ------------------------------------------------------------------------------------------- error
function error(msg) {
  alert(msg + " pokud napises na martin@smidek.eu pokusim se pomoci, Martin");
}
