<?php
  global $ezer_server;
  
  // startem je složka cms
  chdir('cms');

  // volba verze jádra Ezer
//  $kernel= "ezer".(isset($_GET['ezer'])?$_GET['ezer']:'2.2'); 
//  $kernel= "ezer".(isset($_GET['ezer'])?$_GET['ezer']:'3.1'); 
  $kernel= "ezer3.1";
//  $kernel= "ezer2.2";
  $k3= substr($kernel,0,5)=='ezer3' ? '3' : '';

  // parametry aplikace MAN
  $app_name=  "setkani.org";
  $app_root=  'cms';
  $app_js=    array(
      "/cms/cms{$k3}.js?v=4.4",
      "/cms/cms{$k3}_be.js",
      "/cms/modernizr-custom.js",
      "/cms/custom.js?v=4.22",
      $k3 ? false : "/cms/fotorama/jquery-1.12.4.min.js",
      $k3 ? false : "<script type='text/javascript'>jQuery.noConflict();</script>",
      "/cms/fotorama/fotorama.js");
  $app_css=   array(
      "/cms/mini{$k3}.css",
      "/cms/web.css", 
      "/cms/web_edit.css",
      "/cms/fotorama/fotorama.css");
  $skin=      'ck';
  $abs_roots= array(
      "C:/Ezer/beans/setkani.org",
      "/volume1/web/www/setkani4", //"/var/services/web/www/setkani4",
      "/volume1/web/www/setkani4", //"/var/services/web/www/setkani",
      "C:/Ezer/beans/setkani4",
      "C:/Ezer/beans/setkani4",
      "D:/Ezer/beans/setkani4"
    );
  $rel_roots= array(
      "http://setkani.bean:8080",
      "https://www.setkani.org",
      "http://setkani4.doma",
      "http://setkani4.bean:8080",
      "http://setkani4m.bean:8080",
      "http://setkani4.ide"
    );
  
  // on-line přihlášky
  $cms_root= $kernel=='ezer3.1' ? 'ezer3.1' : 'ezer3';
  $app_css[]= "$cms_root/client/ezer_cms3.css";
  $app_js[]= "/$cms_root/client/ezer_cms3.js";

  // specifická část aplikace předávaná do options
  specific($template_meta,$template);
  

  $abs_root= $abs_roots[$ezer_server];

// (re)definice Ezer.options
  $add_options= (object)array(
    'to_trace' => 1,
    'prelogin'     => "1",
    'skill'        => "'w'",
    'autoskill'    => "'!w'",
    'mini_debug' => 1,
    'must_log_in'  => "1",
    'path_files_href' => "'$rel_roots[$ezer_server]'",
    'path_files_s' => "'$abs_roots[$ezer_server]/'"  // absolutní cesta pro přílohy
  );

  // (re)definice Ezer.options
  $add_pars= array(
    'log_login' => false,   // nezapisovat standardně login do _touch (v ezer2.php)
    'favicon' => array('cms_local.png','cms.png','cms_dsm.png','cms_local.png','cms_local.png','cms_local.png')[$ezer_server],
    'template' => "user",
    'template_meta' => $template_meta,
    'template_body' => $template,
    'CKEditor' => "{
      version:'4.6',
      CMS:{
        skin:'moono-lisa',
        toolbar:[['Maximize','Styles','-','Bold','Italic','TextColor','BGColor', 'RemoveFormat',
          '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Outdent', 'Indent', 'Blockquote',
          '-','NumberedList','BulletedList','Table',
          '-','Anchor','Link','Unlink','HorizontalRule','Image','Embed',
          '-','Source','ShowBlocks']],
        // Configure the Enhanced Image plugin to use classes instead of styles and to disable the
        // resizer (because image size is controlled by widget styles or the image takes maximum
        // 100% of the editor width).
        image2_alignClasses: [ 'image-align-left', 'image-align-center', 'image-align-right' ],
        image2_disableResizer: false,
        extraPlugins:'widget,filetools,ezer,embed', 
        entities:true,  // →
        embed_provider: '//iframe.ly/api/oembed?url={url}&callback={callback}&api_key=313b5144bfdde37b95c235',
        uploadUrl:'cms/upload.php?root=cms&type=Images',
        stylesSet:[
          {name:'nadpis článku', element:'h2'},
          {name:'nadpis sekce', element:'h3'},
          {name:'nadpis podsekce', element:'h4'},
          {name:'text',  element:'p'},
          {name:'text na novém řádku', element:'p',    attributes:{'class':'p-clear'}},
          {name:'upozornění',      element:'div',    attributes:{'class':'notice_style notice_warning'}},
          {name:'zajímavost',      element:'div',    attributes:{'class':'notice_style notice_info'}},
          {name:'citace',      element:'div',    attributes:{'class':'pquote'}},
          {name:'stín odstavce',      element:'p',    attributes:{'class':'shadow'}},
          {name:'stín obrázku',    element:'img',  attributes:{'class':'shadow'}},
          {name:'plná šířka tabulky',  element:'table', attributes:{'class':'fill_parent'}},
          {name:'styl tabulky 1',     element:'table',    attributes:{'class':'table1'}},
          {name:'styl tabulky 2',     element:'table',    attributes:{'class':'table2'}},
          {name:'styl tabulky 3',     element:'table',    attributes:{'class':'table3'}}
        ],
        contentsCss:'/cms/web_edit.css'
      }
    }"
  );
  
  // je to aplikace se startem v podsložce 
  require_once("$abs_root/$kernel/ezer_main.php");
  
function specific(&$template_meta,&$template) {
  $debugger= '';
  if ( isset($_GET['dbg']) && $_GET['dbg'] ) {
    $dbg_script= isset($_SESSION['cms']['dbg_script'])
      ? trim($_SESSION['cms']['dbg_script'])
      : "set_trace('m',1,'init,set,key');";
    $debugger= <<<__EOD
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <textarea id="dbg" name='query' class='sqlarea jush-sql' spellcheck='false' wrap='off'
        >$dbg_script</textarea>
        <script type='text/javascript'>focus(document.getElementsByTagName('textarea')[0]);</script>
      </form>
__EOD;
  }

  // předání kontextu pro FE
  $Ezer_web= $del= '';
  if ( isset($_SESSION['web'])) {
    foreach ($_SESSION['web'] as $wi=>$w) {
      $Ezer_web.= "$del$wi:'$w'";
      $del= ',';
    }
  }

  $template_meta= <<<__EOD
    <meta name="robots" content="noindex, nofollow" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta name="viewport" content="width=device-width,user-scalable=yes,initial-scale=1" />
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C700%2C700i%2C800%2C800i&amp;ver=0.3.5" type="text/css" media="all">
__EOD;

  $template= <<<__EOD
%header
<body id="body" onload="context({ $Ezer_web});">
<!-- bez menu a submenu -->
  <div id='horni' class="MainBar">
    <div id='logo'>
      <button id='logoContinue' style='display:none;outline:3px solid orange;'>continue</button>
      <img class="StatusIcon" id="StatusIcon_idle" src="cms/img/-logo.gif" />
      <img class="StatusIcon" id="StatusIcon_server" src="cms/img/+logo.gif" />
    </div>
  </div>
  <div id='ajax_bar'></div>
<!-- pracovní plocha -->
  <div id="stred">
    <!-- div id="shield"></div -->
    <div id="work"></div>
  </div>
<!-- paticka -->
  <div id="paticka">
    <div id="warning"></div>
    <div id="kuk_err"></div>
    <div id="error"></div>
  </div>
  <div id="popup_mask3"></div>
  <div id="top_mask3">
    <div id="popup3">
      <div class="pop_head"></div>
      <div class="pop_body"></div>
      <div class="pop_tail"></div>
    </div>
  </div>
  <div id="dolni">
    <div id="status_bar" style='width:100%;height:16px;padding: 1px 0pt 0pt'>
      <div id='status_left' style="float:left;"></div>
      <div id='status_center' style="float:left;"></div>
      <div id='status_right' style="float:right;"></div>
    </div>
    <div id="trace">
      $debugger
      <pre id="kuk"></pre>
    </div>
  </div>
<!-- konec -->
  <form><input id="drag" type="button" /></form>
</body>
%html_footer
__EOD;
}

?>
