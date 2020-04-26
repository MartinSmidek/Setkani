// ---------------------------------------------------------------------------------------------- //
// uživatelské funkce aplikace Ezer/CMS specifické pro FE (nepřihlášené)                          //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

// ---------------------------------------------------------------------------------------------- go
function go(e,ref,mref,input) {
  if ( e ) e.stopPropagation();
  if ( input ) {
    // go je voláno přes <enter> v hledej
    var search= $('search').value;
    document.cookie= 'web_search='+search+';path=/';
    location.href= ref+'/'+search;
  }
  else {
    location.href= ref;
  }
//   if ( Browser.name=='ie' ) { // && Browser.version<9 ) {
//     if ( input ) {
//       ref+= '!!'+$('search').value;
//     }
//     location.href= ref;
//   }b
//   else {
//     var selection= document.getSelection(),
//         caret= selection && !selection.isCollapsed;       // selection.type=='Caret' FF neumí
//     if ( !caret ) {
//       if ( input ) {
//         ref+= '!!'+$('search').value;
//       }
//       location.href= ref;
//     }
//   }
  return false;
}
// ----------------------------------------------------------------------------------------- refresh
// obnoví stránku
function refresh() {
  location.reload(true);
}
// -----------------------------------------------------------------------------------==> . fe login
function fe_login(page) {
  var name= $('name').value, pass= $('pass').value, akey= $('akey').value,
      type= $('user_login').getAttribute('data-login');
  ask({cmd:'fe_login',name:name,pass:pass,akey:akey,page:page,type:type},_fe_login,'jo?');
}
function _fe_login(y) {
  $('user_login').setStyle('display','none');
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
  file_drop({goal:'user_login',handler:accept_key,css_hover:'key_in'});
  jump_fokus();
}
function accept_key(info) {
  var key= info.text.match(/base64,(.*)/)[1];
  if ( key ) {
    key= base64_decode(key);
    ask({cmd:'fe_key_ok',akey:key},_accept_key,'jo?');
  }
  else {
    fe_init();
  }
  if ( $('akey') ) {
    $('akey').value= key;
  }
}
function _accept_key(y) {
  if ( !y.key_ok ) {
    fe_init();
  }
}
// ----------------------------------------------------------------------------------==> . file drop
var file_drop_info= null;                          // stavový objekt
function file_drop (user_options) {
  var options= Object.append(
    {goal:'goal',css_hover:'drop_area_hover',css_run:'drop_area_run',
     handler:alert,transfer:'url'}
    , user_options||{});
  this.file_drop_obj= {state:'wait'};
  var ctx= [];
  var area= $(options.goal);
  var goal= area;
  var ok= window.File && goal && area ? 1 : 0;
  if ( ok ) {
    goal.removeClass(options.css_hover).removeClass(options.css_run);
    area.addEventListener('dragover', function(evt) {
      evt.preventDefault();
      goal.addClass(options.css_hover);
      goal.setAttribute('data-login','be');
    }, true);
    area.addEventListener('dragleave', function(evt) {
      evt.preventDefault();
      goal.removeClass(options.css_hover);
    }, true);
//    area.addEventListener('drop', function(evt) {
//      evt.preventDefault();
//    }, true);
    goal.addEventListener('drop', function(evt) {
      if ( this.file_drop_obj.state=='wait' ) {
        this.file_drop_obj.state= 'busy';
        goal.removeClass(options.css_hover).addClass(options.css_run);
        evt.stopPropagation();
        evt.preventDefault();
        var f= evt.dataTransfer.files[0]; // first from FileList object
        if ( f ) {
          this.file_drop_info= {name:f.name,size:f.size,type:f.type,text:null};
          var r= new FileReader();
          r.onload= function(e) {
            var x= e.target.result;
            // pokud je definováno omezení velkosti, zmenši obrázek
            if ( options.max_width || options.max_height ) {
              Resample(x,options.max_width,options.max_height, function(data64){
                this.file_drop_info.text= data64; // výstup je base 64 encoded
                //$("StatusIcon_idle").src= data64;
                this._call(0,options.handler,this.file_drop_info)
                //$("StatusIcon_idle").src= null;
              }.bind(this));
            }
            else {
              if ( options.transfer=='base64' )
//                x= x.match(/base64,(.*)=/)[1];
                x= base64_encode(x);
              this.file_drop_info.text= x;
//               this._call(0,options.handler,this.file_drop_info);  // uživatelská funkce ondrop
              goal.addClass(options.css_hover);
              options.handler.call(this,file_drop_info);
            }
          }.bind(this);
          switch(options.transfer) {
          case 'base64':
            r.readAsBinaryString(f); break;
          case 'text':
            r.readAsText(f); break;
          case 'url':
            r.readAsDataURL(f); break;
          }
        }
      }
    }.bind(this),false);
  }
  else
    ok= 0;
  return ok;
};
// ----------------------------------------------------------------------------------- base64 decode
function base64_decode (data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Thunder.m
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    // *     returns 1: 'Kevin van Zonneveld'
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['atob'] == 'function') {
    //    return atob(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec;
}
// ----------------------------------------------------------------------------------- base64 encode
function base64_encode (data) {
    // Encodes string using MIME base64 algorithm
    // discuss at: http://phpjs.org/functions/base64_encode
    // +   original by: Tyler Akins (http://rumkin.com)
    // -   binary input: Martin Šmídek
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];
    if (!data) {
        return data;
    }
//     data = this.utf8_encode(data + '');      //-MŠ
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);
        bits = o1 << 16 | o2 << 8 | o3;
        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;
        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    enc = tmp_arr.join('');
    var r = data.length % 3;
    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

