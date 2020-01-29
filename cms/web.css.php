<?php /* používá https://modernizr.com/download?mediaqueries-setclasses */
start(); header("Content-type: text/css"); echo <<<__EOD

/* ------------------------------------------------------------------------- ladění */

#info { position:fixed; background-color: $b_page; color:$b_pasive;
  display:none; z-index:9; font-size:7pt !important; }
#info * { font-size:7pt !important; }

/*
#dolni { z-index:3; min-height: 32px; border-top: 0; }
#status_bar { margin-top: 14px; top:0px !important; }
#status_bar span { padding: 1px 2px !important; margin: 0 1px !important; }
#trace { margin-top: 32px; }
*/

/* -------------------------------------------------------------------------- css */

div.Label { color:$c_page; }
div.LabelDrop { outline: 3px solid $b_close; background-color:$b_close; }
div.LabelDrop * { color:$c_close !important;  }

/* -------------------------------------------------------------------------- layout */
/* podle https://chrisbracco.com/css-sticky-footer-effect/ ??? */

html { height:100%; width:100%; }
/*html {box-sizing: border-box; }
*,*:before,*:after { box-sizing: inherit; } */
body { /*overflow-y:auto;*/ overflow-x:hidden; margin: 0;   min-height: 100%; background-color:$b_page; }
#page { margin: 0 auto; }
#work { background-color:$b_page; }
a.mereni { float:right; }

/* patička */

#page_foo { /*position: absolute; right:0; bottom:0; left:0;*/ clear:both; min-height:3em;
  border-top: 3px solid $b_page; background-color:$b_pasive; color:$c_open;
  text-align:center; padding:.4em; }

/* obecné, písmo */

#web {margin:0px; padding:0; font-size:1em; font-family:sans-serif; background-color:$b_page;
  /*position:absolute;*/ width: inherit;}
#web div, #web span, #web input {font-size:10pt}
#web h1,h2,h3 { -webkit-column-break-inside: avoid; -moz-column-break-inside: avoid; column-break-inside: avoid; }
#web a:hover { background-color:$b_active; color:$c_active; }
#web img { margin:.2em; height:auto !important; max-width:100%; }

/* ----------------------------------------------------------------------------- popup */

#popup {
/* left:10%; right:10%; top:10%; bottom:10%; */
  left:2%; right:2%; top:2%; bottom:2%;
  background-color:$b_open; color:$c_open; border-radius:.6em; padding:2em 1em 1em 1em; opacity:1;
  position:fixed; border:2px solid $b_active; box-shadow:black 4px 6px 20px; }
#popup > span { position:absolute; top:0em; left:0em; width:100%; text-align:center; height:2em;
  background:$b_active; color:$c_active; border-radius:.4em .4em 0 0; font-weight:bold;
  line-height:22pt;}
#popup > div { top:2em; height:100%; overflow:auto; text-align: center; }
#popup_div { padding-top: 1em; }
#popup_bot { margin-top:-3.6em; height:1.4em !important; padding-top:0.2em;
  z-index:3; background-color:$b_open; position:relative;}

/* -------------------------- ------------------------------------------------- login */

div#user {
  position:fixed; left:6em; top:4em; line-height: 22pt;
  background-color:$b_page; color:$c_page; border-radius:.6em; padding:1em; opacity:1;
  border:2px solid $b_active; box-shadow: black 4px 6px 20px; }
div#user input { position:absolute; left:4em; width:8.5em; }
div#user span { position:absolute; top:0em; left:0em; width:100%; text-align:center; height: 2em;
  background:$b_active; border-radius:.4em .4em 0 0; color:$c_active; font-weight:bold; }
div#user button { position:initial; font-size:10pt; padding: 1px 6px; }
#fe_login { margin-left:3em; }
div#user.key_in { background-color:$b_frame; !important; }

/* -------------------------- ------------------------------------------------- box */

div.box {
  position:fixed; left:50%; top:50%;  transform: translate(-50%, -50%);  line-height: 2em;
  background-color:$b_page; color:$c_page; border-radius:.6em; padding: 1em 1em 0em 1em;
  border:2px solid $b_active; box-shadow: black 4px 6px 20px; }
div.box_title { position:absolute; top:0em; left:0em; width:100%; text-align:center; height: 2em;
  background:$b_active; border-radius:.4em .4em 0 0; color:$c_active; font-weight:bold; }
div.box_text { margin-top:2em; line-height:1.2em; }
div.box_ok { text-align:center; padding:.5em; }
div.box_ok button { position:relative; }
div.box_input { padding:2em 0 1em; }

/* --------------------------------------------------------------------------- menu */

.jump { display:inline-block; padding:0.1em 0.3em; font-weight:bold;
         text-decoration:none; cursor:pointer; border-radius:.4em; }
#menu a span, #menu span span { vertical-align: middle; height: inherit; display: table-cell; }
#menu a.active { background-color:$b_active !important; color:$c_active !important;}
#menu a:hover { background-color:$b_active !important; opacity:0.5; }
#menu a:hover span { color:$c_active !important; }

/* top menu */
#page_tm { text-align:right; padding-right:.4em; height:36pt; }
#page_tm input {float:right; margin:.7em .1em 1em .5em; width:10em;
  background-color:$b_pasive; border:none; padding:.4em; }
#page_tm a.jump { margin:0.1em 0.1em; background-color:$b_pasive; color:$c_pasive; }
#page_tm #mode { float:right; color:$b_pasive; padding-top:.7em; font-size:1.4em !important;
  width:1.2em; cursor:pointer; }

/* main menu */
#page_hm {width:100%; display:flex; margin-bottom:.4em; text-align:center; }
#page_hm a.jump { margin:0.1em 0.3em; background-color:$b_pasive; color:$c_pasive; }

#vyber {width:100%; color:$c_active; font-weight:bold; }
#vyber input { margin-left:1em; }

/* sub menu */
#page_sm {width:100%; display:inline-flex; margin-top: 0.3em;}
#page_sm .jump { margin:0.1em 0.3em; color:$c_pasive; }
#page_sm a.jump { background-color:$b_pasive; }
#page_sm span.jump { color:$b_pasive;  }

/* ------------------------------------------------------------------------------ obsah */

/* odkazy */
div.text a span { background-color:$b_pasive; color:$c_pasive; text-decoration:underline; cursor:pointer }
div.text a span:hover { background-color:$b_active !important; color:$c_active !important; }

/* seznamy abstraktů */
#list {width:100%; }
div.abstrakt { background-color:$b_close; color:$c_close; border-radius:.4em; margin:0.5em;
   cursor:pointer; padding:.4em;  }
div.abstrakt_hidden { background-color:silver }
div.abstrakt_deleted { background-color:silver; border:5px solid black; }

/* abstrakty */
div.abstr { display:inline-block; vertical-align:top; }
div.mini { float:right; width:8em; height:8em; background-repeat:no-repeat; background-position:center; }

/* help */
div.help { border:5px solid $b_frame; box-shadow: black 4px 6px 20px; }
span.input { background-color:$b_pasive; }

/* vlákno */
#vlakno { clear:both; }
div.text { padding:.4em;}

/* fotky */
/*span.foto { width: 80px; height: 80px; overflow: hidden; display: inline-block; }*/
span.foto { width:80px; height:80px; overflow:hidden; display:inline-block;
  background-repeat:no-repeat; background-position:center; }
div.drop_back { z-index:2; background-color:#c9d5e5; opacity:.7; }
div.drop_foto { z-index:3; margin:2px; width:76px; height:76px; outline:2px dashed #fff; display:table }
div.drop_foto span { display:table-cell; text-align:center; vertical-align:middle; }
#popup_div>img { max-width:100%; margin:0; }

/* skupiny */
table#skupiny th { background-color:$b_pasive; color:$c_pasive; min-width:8em; }
table#skupiny input { width:100%; }

/* článek */
div.clanek { background-color:$b_open; color:$c_open; overflow:hidden; margin:0.5em; position:relative; }
.mediaqueries div.clanek { columns:35em 2; column-rule:1em solid $b_page; column-gap: 2em;
  -moz-column-count:2 35em; -moz-column-rule:1em solid $b_page; -moz-column-gap: 2em; }
span.datum { background-color:$b_signal; color:$c_signal; }
div.clanek span.datum { position: absolute; top: 0; left: 0; }
div.podpis { text-align:right; font-style:italic; clear:both; }

/* knihy */
div.kapitola  { background-color:$b_open; color:$c_open; overflow:hidden; margin:0.5em; position:relative; }
div.kniha_br { clear:both; padding:1em 0; text-align:center; }
div.kniha_bg { clear:both; background-color:$b_frame; color:$c_frame; border-radius:.8em; }
.mediaqueries div.kniha_bg div.clanek { column-rule:1em solid $b_frame; -moz-column-rule:1em solid $b_frame; }

/* fotogalerie */
#galerie { padding:1em; }

/* ----------------------------------------------------------------------------- home page */

#home {width: 100%; }

#home_akce {width: 100%; display:inline-block; }
div.home_akce>b { color:$c_page; }
div.home_akce { }

#home_telo {width: 100%; display:inline-block; color:$c_page; }

#home_info {width: 100%; display:inline-block; }
div.home_info {  }
div.home_info>b { color:$c_page; }

/* ----------------------------------------------------------------------------- dům */

/* popis */

#pokoje { clear:both; }
div.pokoje { background-color:$b_open; color:$c_open; overflow:hidden; margin:0.5em; position:relative; }
span.datum { background-color:$b_signal; color:$c_signal; }
div.pokoje span.datum { position: absolute; top: 0; left: 0; }

/* tabulka */

table.dum  { border-collapse: collapse; empty-cells: hide; width: 100%; }
table.dum td { border: 1px solid navy; min-width:1.2em; text-align: center; }

#dum td.room:hover, #dum td.obsazen:hover, #dum td.sent:hover
  { background-color:$b_active; color:$c_active; }
#dum td.room { background-color: $b_pasive;  font-weight: bold;  cursor: pointer; }
#dum td.hlavicka { background-color: $b_open;  font-weight: bold; }
#dum td.pozn { background-color: $b_pasive;  text-align: left; }
#dum td.pozn_weekend { background-color: $b_active;  text-align: left; }
#dum td.datum {background-color: $b_pasive;  text-align:right; width:3em; }
#dum td.datum_weekend { background-color:$b_active;  text-align:right; width:3em; }

#dum .datum_plno, td.obsazeno { background-color: red; width:2em; }
#dum .datum_prazdno, td.volno { background-color: lightgreen; width:2em; }
#dum .datum_poloplno, td.konflikt { background-color: yellow; width:2em; }
#dum td.sent { cursor:pointer; }
#dum td.nic { background-color: $b_pasive; width:1.4em;}
#dum td.obsazen { background-color: $b_pasive; width:1.4em;}

#order {
  position:fixed; left:100px; top:100px; width:710px; height:230px;
  background-color:$b_open; color:$c_open; border-radius:.6em; padding:2em 1em 1em 1em; opacity:1;
  border:2px solid $b_active; box-shadow:black 4px 6px 20px; }
#order > span { position:absolute; top:0em; left:0em; width:100%; text-align:center; height:2em;
  background:$b_active; color:$c_active; border-radius:.4em .4em 0 0; font-weight:bold;
  line-height:22pt;}
#order > div { top:2em; height:100%; overflow:auto; }
#order label { display:inline-block; font-size:small; margin-top:.5em; }
#order input, #order select { display:block; }

/* ====================================================================== non responsive design */

.no-mediaqueries #info::before { content:"?"; }
/* omezení šířky */
.no-mediaqueries #menu { margin-left: 11em; }
.no-mediaqueries #web {width:100%; max-width:70em; margin:auto; }
.no-mediaqueries #logo_setkani { position:absolute; margin-left:0em; top:0em; width:6em; }
.no-mediaqueries #logo_ymca { position:absolute; margin: 0 0 0 6em !important; top:.5em; width:3em; }
/* home page na na šířku */
.no-mediaqueries #home_akce {float: left; width: 25%; min-height:5em; }
.no-mediaqueries #home_telo {float: left; width: 50%; }
.no-mediaqueries #home_info {float: left; width: 25%; }
.no-mediaqueries #cist_vlevo { display:block; }
.no-mediaqueries #cist_dole { display:none; }
/* velká tlačítka */
.no-mediaqueries #page_sm span.label { padding-top:0.4em; }
.no-mediaqueries #page_sm .jump, #page_hm a.jump, #page_tm a.jump { height:2.5em; }
/* větší písmo menu */
.no-mediaqueries #menu div, #menu span, #menu input {font-size:10pt; }
/* abstrakty */
.no-mediaqueries div.abstr { margin-right:.5em; }
/* popup */
.no-mediaqueries #popup { left:16%; right:16%; }

/* ========================================================================== responsive design */

/* -------------------------------------------------------------------------- MV mobil na výšku */

@media all and (max-width:30em) {
  #info::before { content:"mobil portrait"; }
/*
  #logo_setkani { position:absolute; margin-left:0em; top:0em; width:3.6em; z-index:1; }
  #logo_ymca { position:absolute; margin: 0 0 0 3.4em !important;top:.2em; width:2.5em; z-index:-1; }
*/
  #logo_setkani { position:absolute; margin-left:4em !important; top:0em; width:4em; z-index:1; }
  #logo_ymca { position:absolute; margin: 0.2em 0em 0 0.2em !important;top:.2em; width:3.5em; z-index:1; }
  /* home page na na výšku */
  #page_tm .jump { width:12%; white-space:wrap; overflow:hidden; }
  #page_hm .jump { width:16.6667%; white-space:wrap; overflow:hidden; }
  #page_sm .jump, #page_sm span.label {
          width:16.6667%; white-space:wrap; overflow:hidden; }
  #cist_vlevo { display:none; }
  #cist_dole { display:block; }
  /* velká tlačítka */
  #page_sm span.label { padding-top:0.4em; }
  #page_sm .jump, #page_hm a.jump, #page_tm a.jump { height:2.5em; }
  #page_sm a.active, #page_hm a.active, #page_tm a.active { background-color:$b_active; }
  #page_tm input { width:6em; }
  /* menší písmo menu */
  #menu div, #menu span, #menu input {font-size:8pt; }
  /* abstrakty */
  div.abstr { width:100%; }
}

/* -------------------------------------------------------------------------- MŠ mobil na šířku */

@media all and (min-width: 30em) and (max-width: 40em) {
  #info::before { content:"mobil lanscape"; }
  #menu { margin-left: 8em; }
/*
  #logo_setkani { position:absolute; margin-left:0em; top:0em; width:6em; }
  #logo_ymca { position:absolute; margin: 0 0 0 6em !important; top:.3em; width:2.8em; }
*/
  #logo_setkani { position:absolute; margin-left:5em !important; top:0em; width:5.5em; }
  #logo_ymca { position:absolute; margin: 0.2em 0em 0 0.2em !important; top:.3em; width:5em; }
  /* home page na na šířku */
  #home_akce {float: left; width: 25%; min-height:5em; }
  #home_telo {float: left; width: 50%; }
  #home_info {float: left; width: 25%; }
  #cist_vlevo { display:block; }
  #cist_dole { display:none; }
  /* velká tlačítka */
  #page_sm span.label { padding-top:0.4em; }
  #page_sm .jump, #page_hm a.jump, #page_tm a.jump { height:2.5em; }
  /* střední písmo menu */
  #menu div, #menu span, #menu input {font-size:9pt; }
  /* abstrakty */
  div.abstr { width:50%; }
}

/* -------------------------------------------------------------------------- T tablet */

@media all and (min-width:40em) and (max-width:70em) {
  #info::before { content:"tablet"; }
  #menu { margin-left: 11em; }
/*
  #logo_setkani { position:absolute; margin-left:0em; top:0em; width:6em; }
  #logo_ymca { position:absolute; margin: 0 0 0 6em !important; top:.5em; width:3em; }
*/
  #logo_setkani { position:absolute; margin-left:5.5em !important; top:0em; width:6em; }
  #logo_ymca { position:absolute; margin: 0.2em 0em 0 0.2em !important; top:.5em; width:5em; }
  /* home page na na šířku */
  #home_akce {float: left; width: 25%; min-height:5em; }
  #home_telo {float: left; width: 50%; }
  #home_info {float: left; width: 25%; }
  #cist_vlevo { display:block; }
  #cist_dole { display:none; }
  /* velká tlačítka */
  #page_sm span.label { padding-top:0.4em; }
  #page_sm .jump, #page_hm a.jump, #page_tm a.jump { height:2.5em; }
  /* větší písmo menu */
  #menu div, #menu span, #menu input {font-size:10pt; }
  /* abstrakty */
  div.abstr { width:33.3%; }
}

/* -------------------------------------------------------------------------- PC velká obrazovka */

@media all and (min-width: 70em) {
  #info::before { content:"desktop"; }
  /* omezení šířky */
  #web,#page_foo {width:84em; margin:auto; }
/*
  #logo_setkani { position:absolute; margin-left:0em; top:0em; width:6em; }
  #logo_ymca { position:absolute; margin: 0 0 0 6em !important; top:.5em; width:5em; }
*/
  #logo_setkani { position:absolute; margin-left:5.5em !important; top:0em; width:6em; }
  #logo_ymca { position:absolute; margin: 0.2em 0em 0 0.2em  !important; top:.5em; width:5em; }
  #menu { margin-left: 11em; }
  /* home page na na šířku */
  #home_akce {float: left; width: 25%; min-height:5em; }
  #home_telo {float: left; width: 50%; }
  #home_info {float: left; width: 25%; }
  #cist_vlevo { display:block; }
  #cist_dole { display:none; }
  /* velká tlačítka */
  #page_sm span.label { padding-top:0.4em; }
  #page_sm .jump, #page_hm a.jump, #page_tm a.jump { height:2.5em; }
  /* větší písmo menu */
  #menu div, #menu span, #menu input {font-size:10pt; }
  /* abstrakty */
  div.abstr { width:25%; }
  /* popup */
  #popup { left:16%; right:16%; }
}
__EOD;

/* ======================================================================================== PHP */

function start() {
 //session_start();
 get_paletton();
}

function get_paletton() {
/* background:b_, color:c_ */
/* navigace, popisy */
global $b_page, $c_page, $b_pasive, $c_pasive, $b_active, $c_active;
/* zavřený text */
global $b_close, $c_close, $b_signal, $c_signal;
/* orámování text */
global $b_frame, $c_frame;
/* otevřený text */
global $b_open, $c_open;
$color= "\$color";
/* --------------------------------------------------------- zelená s béžovou */
$pallette= <<<__EOD
// ----------------  import palety z http://paletton.com/ -  barvy sousedící nebo triáda
// SASS style sheet */
// Palette color codes */
// Palette URL: http://paletton.com/#uid=32p1m0klxmn3oUod0vkugdiR52L */

// Feel free to copy&paste color codes to your application */


// As hex codes */

$color-primary-0: #7CA536;	// Main HlavnĂ­ barva */
$color-primary-1: #F1FBE1;
$color-primary-2: #C4E789;
$color-primary-3: #406205;
$color-primary-4: #0D1400;

$color-secondary-1-0: #B28A3A;	// Main VedlejĹˇĂ­ barva (1) */
$color-secondary-1-1: #FFF6E4;
$color-secondary-1-2: #FAD794;
$color-secondary-1-3: #6A4806;
$color-secondary-1-4: #160E00;

$color-secondary-2-0: #323F7A;	// Main VedlejĹˇĂ­ barva (2) */
$color-secondary-2-1: #D8DCEE;
$color-secondary-2-2: #6E78AA;
$color-secondary-2-3: #0C1748;
$color-secondary-2-4: #01030F;
__EOD;
/* --------------------------------------------------------- zelená s béžovou */
$pallette= <<<__EOD
// ----------------  import palety z http://paletton.com/ -  barvy sousedící nebo triáda
// SASS style sheet */
// Palette color codes */
// Palette URL: http://paletton.com/#uid=32p1m0kmXmE47TneBuJuZdBTd2p */

// Feel free to copy&paste color codes to your application */


// As hex codes */

$color-primary-0: #7BA72F;	// Main HlavnĂ­ barva */
$color-primary-1: #EFFBDA;
$color-primary-2: #BDE37B;
$color-primary-3: #406403;
$color-primary-4: #0B1200;

$color-secondary-1-0: #B48933;	// Main VedlejĹˇĂ­ barva (1) */
$color-secondary-1-1: #FFF4DE;
$color-secondary-1-2: #F5CF85;
$color-secondary-1-3: #6C4903;
$color-secondary-1-4: #130D00;

$color-secondary-2-0: #2E3B7B;	// Main VedlejĹˇĂ­ barva (2) */
$color-secondary-2-1: #D1D6EC;
$color-secondary-2-2: #6470A7;
$color-secondary-2-3: #0B164A;
$color-secondary-2-4: #00030D;
__EOD;

/* --------------------------------------------------------- konec importu palety */
$pallette= strtr($pallette,array('-'=>'_',':'=>'=','#'=>"'#",';'=>"';"));
eval($pallette);

/* navigace, popisy */
$b_page=   $color_primary_3;     $c_page=   $color_primary_2;
$b_pasive= $color_secondary_1_2; $c_pasive= $color_primary_3;
$b_active= $color_secondary_1_0; $c_active= $color_primary_1;

/* zavřený text */
$b_close=  $color_primary_2;     $c_close=  $color_primary_3;
$b_signal= $color_secondary_1_2; $c_signal= $color_secondary_1_4;

/* orámování text */
$b_frame=  $color_secondary_2_2; $c_frame=  $color_secondary_2_1;

/* otevřený text */
$b_open=   $color_secondary_1_1; $c_open=   $color_secondary_1_4;

}
?>


