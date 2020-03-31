/* global Ezer */

// ---------------------------------------------------------------------------------------------- //
// uživatelské funkce aplikace Ezer/CMS specifické pro FE (nepřihlášené)                          //
//                                                                                                //
// CMS/Ezer3.1                                          (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

// ---------------------------------------------------------------------------------------------- go
function go(e,ref,mref,input) {
  if ( e ) e.stopPropagation();
  if ( input ) {
    // go je voláno přes <enter> v hledej
    var search= jQuery('#search').val();
    document.cookie= 'web_search='+search+';path=/';
    location.href= mref+'/'+search;
  }
  else {
    location.href= mref;
  }
  return false;
}
// ----------------------------------------------------------------------------------------- refresh
// obnoví stránku
function refresh() {
  location.reload(true);
}
// -----------------------------------------------------------------------------------==> . fe login
function fe_login(page) {
  var name= jQuery('#name').val(), pass= jQuery('#pass').val(), akey= jQuery('#akey').val(),
      type= jQuery('#user_login').data('login');
  ask({cmd:'fe_login',name:name,pass:pass,akey:akey,page:page,type:type},_fe_login,'jo?');
}
function _fe_login(y) {
  jQuery('#user_login').css('display','none');
  if ( window['Ezer'] ) {
    Ezer.web= y.web;
  }
  if ( !y.fe_user ) {
    alert('chybné přihlášení');
  }
  if ( y.be_user ) {
    window.location= 'index.php?page='+y.page;
  }
  else {
    refresh();
  }
}
// ----------------------------------------------------------------------------------------- fe init
// inicializuje stránku
function fe_init() {
  var dropZone= jQuery('div#user_login');
  dropZone.removeClass('key_in');
  fe_readKey();
  jump_fokus();
}
function accept_key(key) {
  if ( key ) {
    ask({cmd:'fe_key_ok',akey:key},_accept_key,'jo?');
  }
  else {
    fe_init();
  }
  if ( jQuery('#akey') ) {
    jQuery('#akey').val(key);
  }
}
function _accept_key(y) {
  if ( !y.key_ok ) {
    fe_init();
  }
}
// -------------------------------------------------------------------------------------- fe readKey
// pro příjem souboru s klíčem
function fe_readKey  () {
  // Setup the dnd listeners.
  if ( window.File ) {
    var dropZone= jQuery('div#user_login');
    dropZone.on({
      dragover: evt => {
        evt.stopPropagation();
        evt.preventDefault();
        dropZone.data('login','be').addClass('key_in');
      },
      dragleave: evt => {
        evt.stopPropagation();
        evt.preventDefault();
        dropZone.removeClass('key_in');
      },
      drop: evt => {
        evt.stopPropagation();
        evt.preventDefault();
        var files= evt.originalEvent.dataTransfer.files; // FileList object.
        if ( files[0] ) {
          var r= new FileReader();
          r.onload= function(e) {
            accept_key(e.target.result);
          };
          r.readAsText(files[0]);
        }
      }
    })
  }
}
