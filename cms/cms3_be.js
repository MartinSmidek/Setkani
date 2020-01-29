// ---------------------------------------------------------------------------------------------- //
// uživatelské funkce aplikace Ezer/CMS specifické pro BE (přihlášené)                            //
//                                                                                                //
// CMS/Ezer                                        (c) 2016-2017 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

// -------------------------------------------------------------------------------------==> CKEditor
CKEDITOR.plugins.add('ezer', {
  requires: 'widget,filetools',
  init: function (editor) {
    var max_size= 800,
       theIMG, loadingImage=
    'data:image/gif;base64,R0lGODlhDgAOAIAAAAAAAP///yH5BAAAAAAALAAAAAAOAA4AAAIMhI+py+0Po5y02qsKADs=';
    // ---------------------------------------------- rotace obrázku
    editor.addMenuItems({
      ezer_rotate_l: {label:'Otočit 90° doleva',  command:'ezer_rotate_l',group:'image',order:2},
      ezer_rotate_r: {label:'Otočit 90° doprava', command:'ezer_rotate_r',group:'image',order:3},
      ezer_rotate_s: {label:'Otočit 180°',        command:'ezer_rotate_s',group:'image',order:4}
    });
    editor.contextMenu.addListener(function (element,selection) {
      theIMG= 0;
      if ( element && element.$.nodeName=='IMG' )
        theIMG= element;
      else {
        var imgs= element.getElementsByTag('IMG').$;
        if ( imgs.length ) theIMG= imgs[0];
      }
      if ( theIMG ) { return {
        ezer_rotate_l:CKEDITOR.TRISTATE_OFF,
        ezer_rotate_r:CKEDITOR.TRISTATE_OFF,
        ezer_rotate_s:CKEDITOR.TRISTATE_OFF
      }}
    });
    editor.addCommand('ezer_rotate_l', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:90},imageback);
    }});
    editor.addCommand('ezer_rotate_r', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:-90},imageback);
    }});
    editor.addCommand('ezer_rotate_s', { exec: function (editor) {
      var src= theIMG.getAttribute('src');
      ask({cmd:'cke',cke:'img_rotate',src:src,deg:180},imageback);
    }});
    var imageback= function(y) {
      if ( y.ok ) {
        var W= theIMG.$ ? theIMG.$.naturalWidth  : theIMG.width;  // theIMG.getAttribute('width'),
            H= theIMG.$ ? theIMG.$.naturalHeight : theIMG.height; // theIMG.getAttribute('height');
        if ( y.deg!=180 ) {
          theIMG.setAttribute('width',H);
        }
        theIMG.setAttribute('src',y.src);
        theIMG.setAttribute('data-cke-saved-src',y.src);
        editor.fire('change');
      }
    };
    // ---------------------------------------------- vložení obrázku
    editor.on('paste', function (evt) {
      // nalezení instance Ezer.EditHtml
      var EditHtml= jQuery(editor.element.$.parentNode).data('ezer'), LabelDrop= 0, ok= 1;
      ok= EditHtml.label_drop!=undefined;
      if ( ok ) {
        LabelDrop= EditHtml.label_drop;
        editor.widgets.add( 'ezer', {
          allowedContent: 'img[src]',
          requiredContent: 'img',
          pathName: 'ezer',
        });
        // This feature does not have a button, so it needs to be registered manually.
        editor.addFeature(editor.widgets.registered.ezer);
        ok= evt.data.dataTransfer.getFilesCount();
      }
      else Ezer.fce.error("CKEditor - chybí provázání EditHtml s LabelDrop!");
      if ( ok ) {
        var data= evt.data.dataTransfer.getData('img');
        var file= evt.data.dataTransfer.getFile(0);
        // dále budeme zpracovávat jen obrázky
        ok= file.type.substr(0,5)=='image';
        if ( !ok ) Ezer.fce.warning("přetažením do okna editoru lze vkládat jen obrázky");
      }
      if ( ok ) {
        // pokud existuje ondrop, zavolej a pokračuj jen pokud vrátí 1
        if ( EditHtml.part['ondrop'] ) {
          ok= EditHtml._call(0,'ondrop',file);  // uživatelská funkce ondrop pokud vrátí 0 končíme
        }
      }
      if ( ok ) {
        var loader= editor.uploadRepository.create(file );
        loader.on( 'loaded', function(evt) {
          if ( ok ) {
            LabelDrop.onUploaded= function(file) {
              theIMG.setAttribute('src',file.folder+file.name);
              theIMG.setAttribute('data-cke-saved-src',file.folder+file.name);
            }.bind(this);
            LabelDrop.DOM_addFile(file);
            Resample2(this.data,max_size,function(data64){ // výstup je base64
              theIMG= this._.events.loaded.$;
              theIMG.setAttribute('src', data64);
              file.data= dataURItoBlob(data64);
              LabelDrop.DOM_upload(file,1);
              // záměna src za cestu na serveru
              file.orig= 'drop';
            }.bind(this));
          }
        });
        var element= editor.document.createElement('img',{attributes:{src:loadingImage}} );
        editor.insertElement(element);
        var widget= editor.widgets.initOn(element);
        loader.define('loaded',element);
        loader.load();
      }
    });
  }
});
// --------------------------------------------------------------------------------------- Resample2
// http://stackoverflow.com/questions/18922880/html5-canvas-resize-downscale-image-high-quality
//   - hledat Hermite resize - je tam i Update: version 2.0
//     (faster, web workers + transferable objects) - https://github.com/viliusle/Hermite-resize
function Resample2(uri, size, onresample) {
  var canvas = this.document.createElement("canvas");
  var ctx = canvas.getContext("2d");
  var img = new Image();
  img.src= uri;
  img.onload = function(){
    var W = img.width;
    var H = img.height;
    if ( W>=size || H>=size ) {
      canvas.width = W;
      canvas.height = H;
      ctx.drawImage(img, 0, 0); //draw image
      // resize
      if ( W>H ) { H= (size/W)*H; W= size; }
      else { W= (size/H)*W; H= size; }
      resample_single(canvas, W, H, onresample);
    }
    else {
      onresample(uri);
    }
  };
}
/**
 * Hermite resize - fast image resize/resample using Hermite filter. 1 cpu version!
 *
 * @param {HtmlElement} canvas
 * @param {int} width
 * @param {int} height
 * @param {boolean} resize_canvas if true, canvas will be resized. Optional.
 */
function resample_single(canvas, width, height, onresample) {
  var resize_canvas= true;
  var width_source = canvas.width;
  var height_source = canvas.height;
  width = Math.round(width);
  height = Math.round(height);

  var ratio_w = width_source / width;
  var ratio_h = height_source / height;
  var ratio_w_half = Math.ceil(ratio_w / 2);
  var ratio_h_half = Math.ceil(ratio_h / 2);

  var ctx = canvas.getContext("2d");
  var img = ctx.getImageData(0, 0, width_source, height_source);
  var img2 = ctx.createImageData(width, height);
  var data = img.data;
  var data2 = img2.data;

  for (var j = 0; j < height; j++) {
    for (var i = 0; i < width; i++) {
      var x2 = (i + j * width) * 4;
      var weight = 0;
      var weights = 0;
      var weights_alpha = 0;
      var gx_r = 0;
      var gx_g = 0;
      var gx_b = 0;
      var gx_a = 0;
      var center_y = (j + 0.5) * ratio_h;
      var yy_start = Math.floor(j * ratio_h);
      var yy_stop = Math.ceil((j + 1) * ratio_h);
      for (var yy = yy_start; yy < yy_stop; yy++) {
        var dy = Math.abs(center_y - (yy + 0.5)) / ratio_h_half;
        var center_x = (i + 0.5) * ratio_w;
        var w0 = dy * dy; //pre-calc part of w
        var xx_start = Math.floor(i * ratio_w);
        var xx_stop = Math.ceil((i + 1) * ratio_w);
        for (var xx = xx_start; xx < xx_stop; xx++) {
          var dx = Math.abs(center_x - (xx + 0.5)) / ratio_w_half;
          var w = Math.sqrt(w0 + dx * dx);
          if (w >= 1) {
            //pixel too far
            continue;
          }
          //hermite filter
          weight = 2 * w * w * w - 3 * w * w + 1;
          var pos_x = 4 * (xx + yy * width_source);
          //alpha
          gx_a += weight * data[pos_x + 3];
          weights_alpha += weight;
          //colors
          if (data[pos_x + 3] < 255)
            weight = weight * data[pos_x + 3] / 250;
          gx_r += weight * data[pos_x];
          gx_g += weight * data[pos_x + 1];
          gx_b += weight * data[pos_x + 2];
          weights += weight;
        }
      }
      data2[x2] = gx_r / weights;
      data2[x2 + 1] = gx_g / weights;
      data2[x2 + 2] = gx_b / weights;
      data2[x2 + 3] = gx_a / weights_alpha;
    }
  }
  //clear and resize canvas
  if (resize_canvas === true) {
    canvas.width = width;
    canvas.height = height;
  } else {
    ctx.clearRect(0, 0, width_source, height_source);
  }
  //draw
  ctx.putImageData(img2, 0, 0);
  // retrieve the canvas content as base64 encoded image and pass the result to the callback
  onresample(canvas.toDataURL("image/jpeg"));
}
// ----------------------------------------------------------------------------------------- context
// předá kontext _SESSION[web]
function context(web) {
  Ezer.web= {};
//  $each(web,function(val,id) {
  for ([id,val] of Object.entries(web)) {
    Ezer.web[id]= val;
  };
  return 1;
}
// ----------------------------------------------------------------------------------------- noadmin
// zpřístupní ladící a administrátorské prvky pro a=1 znepřístupní pro a=0
function admin(a) {
  var logo=  jQuery('#logo'),
      work=  jQuery('#work'),
      dolni= jQuery('#dolni');
  if ( !a ) work.css({height:'inherit'});
  if ( logo ) logo.css({zIndex:a?99999:0});
  if ( dolni ) dolni.css({display:a?'block':'none'});
  return 1;
}
// ------------------------------------------------------------------------------------- cms_cid_pid
// předá CMS žádost o popup pro informaci o cid/pid
function cms_cid_pid(e,cid,pid) {
  if ( e ) e.stopPropagation();
  Ezer.run.$.part.p._call(0,'cms_cid_pid',cid,pid)
}
// ---------------------------------------------------------------------------------------------- go
// předá CMS info na kterou stránku webu přepnout
function go(e,href,mref,input,nojump) {
  if ( e ) e.stopPropagation();
  nojump= nojump||0;
  var url, http, page, u= href.split('page=');
  if ( u.length==2 ) {
    http= u[0];
    page= u[1].split('#');
    page= page[0];
  }
  else {
    http= u;
    page= 'home';
  }
  if ( input ) {
    // go je voláno přes <enter> v hledej
    var search= jQuery('#search').val();
    document.cookie= 'web_search='+search+';path=/';
    page= page + '!!'+ search;
  }
  history.pushState({},'',mref ? mref : http+'page='+page);
  Ezer.run.$.part.p._call(0,nojump?'cms_menu':'cms_go',page)
  return false;
}
// ----------------------------------------------------------------------------------------- refresh
// požádá předá CMS o obnovu stránky
function refresh() {
  Ezer.run.$.part.p._call(0,'refresh')
  return 1;
}
// ------------------------------------------------------------------------------------------- vyber
function vyber() {
  var list= '', checks;
  checks= jQuery('#vyber');
  if ( checks ) {
    checks= checks.find('input[type=checkbox]');
    checks.each(function() {
      let ch= jQuery(this);
      if ( ch.prop('checked') )
        list+= (list?',':'')+ch.data('value');
    });
  }
  return list;
}
// ----------------------------------------------------------------------------------------- seradit
function seradit(ids,typ) {
  Ezer.run.$.part.p._call(0,'seradit',ids,typ);
  return 1;
}
// ---------------------------------------------------------------------------------------- vytvorit
function vytvorit(typ,pgid,mid) {
  Ezer.run.$.part.p._call(0,'vytvorit',typ,pgid,mid);
  return 1;
}
// ------------------------------------------------------------------------------------------ pridat
// přidání casepart
function pridat(typ,cid,kapitola) {
  kapitola= kapitola || 0;
  Ezer.run.$.part.p._call(0,'pridat',typ,cid,kapitola);
  return 1;
}
// ----------------------------------------------------------------------------------------- opravit
function opravit(typ,id,cid) {
  Ezer.run.$.part.p._call(0,'opravit',typ,id,cid);
  return 1;
}
/** ------------------------------------------------------------------------------------------ zrusit
 * 
 * @param {type} typ -- článek | kniha
 * @param {type} pid
 * @param {type} on
 * @returns {Number}
 */
function zrusit(typ,pid,on) {
  Ezer.run.$.part.p._call(0,'zrusit',typ,pid,on);
  return 1;
}
// ------------------------------------------------------------------------------------------- skryt
function skryt(typ,pid,on) {
  Ezer.run.$.part.p._call(0,'skryt',typ,pid,on);
  return 1;
}
// ------------------------------------------------------------------------------------- foto delete
function foto_delete(span) {
  if ( span.tagName=='IMG' )
    span= span.parentNode;
  Ezer.run.$.part.p._call(0,'foto','delete',span.title,'');
  return 1;
}
// --------------------------------------------------------------------------------------- foto note
function foto_note(li) {
  var div= li.getElement('div'),
      note= div ? div.get('text') : '';
  Ezer.run.$.part.p._call(0,'foto','note',li.title,note);
  return 1;
}
// ----------------------------------------------------------------------------------- foto sortable
var sortable= null;
function foto_sortable(op) {
  var foto= jQuery('#foto'), ret=1;
  switch (op) {
  case 'start':
    sortable= new Sortables(foto,{clone:true});
    break;
  case 'order':
    ret= sortable ? sortable.serialize(0,function(el,i){
      return el.dataset.fotoN;
    }).join(',') : '';
    break;
  case 'checked': // vrací n+1 kvůli kódování zápornou hodnotou
    ret= sortable ? sortable.serialize(0,function(el,i){
      var n= 1+el.dataset.fotoN.toInt();
      return el.dataset.checked ? n : -n;
    }).join(',') : '';
    break;
  }
  return ret;
}
