<?php

# ------------------------------------------ IP test

$ip_ok= 1; //in_array($ip,$ips);

# ------------------------------------------ init

$microtime_start= microtime();
if ( !isset($_SESSION) ) session_start();
error_reporting(0);
require_once("ezer3.2/pdo.inc.php");
require_once("ezer3.2/server/ezer_pdo.php");
require_once("cms/web_fce.php");
require_once("cms/mini.php");
//$ezer_local= preg_match('/^\w+\.ezer|\w+\.(ezer|bean)|^localhost|^192\.168\./',$_SERVER["SERVER_NAME"]);

# ------------------------------------------ ajax

$secret= "WEBKEYNHCHEIYSERVANTAFVUOVKEYWEB";
if ( count($_POST) && !isset($_POST['post']) ) {
  $x= array2object($_POST);
  // ochrana heslem
  if ( $_POST['secret']!==$secret ) { echo "?"; exit; }
  $y= $x;
  server($x);
  header('Content-type: application/json; charset=UTF-8');
  $yjson= json_encode($y);
  echo $yjson;
  exit;
}
elseif ( $_GET['secret']!=$secret ) {
  // ochrana heslem
  echo "?";
  exit;
}

# ------------------------------------------ dotaz na záznam v Answeru resp. webu

//global $ezer_db, $ezer_local;
//$hst1= 'localhost';
//$nam1= $ezer_local ? 'gandi' : 'proglas';
//$pas1= $ezer_local ? ''      : 'pr0gl8s';
//$hst2= $ezer_local ? 'localhost' : '192.168.1.145';
//$nam2= $ezer_local ? 'gandi'     : 'gandi';
//$pas2= $ezer_local ? ''          : 'r8d0st';
//$ezer_db= array( /* lokální */
//  'setkani'  =>  array(0,$hst1,$nam1,$pas1,'utf8'),
//  'ezer_db2' =>  array(0,$hst2,$nam2,$pas2,'utf8')
//);

global $ezer_db, $ezer_server;
$ezer_server= 
    $_SERVER["SERVER_NAME"]=='setkani.bean'    ? 0 : (        // 0:lokální 
    $_SERVER["SERVER_NAME"]=='setkani.org'     ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='www.setkani.org' ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='setkani4.doma'   ? 2 : (        // Synology DOMA
    $_SERVER["SERVER_NAME"]=='setkani4.bean'   ? 3 : (        // 3:lokální VERZE 4 - Jirka
    $_SERVER["SERVER_NAME"]=='setkani4m.bean'  ? 4 : -1))))); // 4:lokální VERZE 4 - Martin

// databáze
$deep_root= "../files/setkani4";
require_once("$deep_root/cms.dbs.php");
$ezer_db= $dbs[$ezer_server];

global $y, $trace;
$y= (object)array('servant'=>'yes');
if ( $ip_ok && isset($_GET['cmd']) && $_GET['cmd']=='re_login' ) {
  $x= (object)array('cmd'=>'re_login','ido'=>$_GET['fe_user']);
  server($x);  // web_fce.php + mini.php
}
elseif ( isset($_GET['mail']) ) {
  $pin= isset($_GET['pin']) ? $_GET['pin'] : '';
  $web= isset($_GET['web']) ? $_GET['web'] : ''; 
  $lang= isset($_GET['lang']) ? $_GET['lang'] : ''; 
  $mail= str_replace('*','',$_GET['mail']);
  $x= (object)array('cmd'=>'me_login','mail'=>$mail,'pin'=>$pin,'cond'=>'mrop','web'=>$web,'lang'=>$lang);
  server($x);  // web_fce.php + mini.php
  $y->my_ip= "$ip=".($ip_ok?'ok':'ko');
  $y->trace.= $trace;
}
elseif ( $ip_ok && isset($_GET['mapa']) ) {
  $x= (object)array('cmd'=>'mapa','mapa'=>$_GET['mapa']);
  server($x);
}
elseif ( $ip_ok && isset($_GET['kalendar']) ) {
  $x= (object)array('cmd'=>'kalendar');
  server($x);
}
elseif ( $ip_ok && isset($_GET['mapa2']) ) {
  $x= (object)array('cmd'=>'mapa2','mapa'=>$_GET['mapa']);
  server($x);
}
elseif ( $ip_ok && isset($_GET['roky']) ) {
  $x= (object)array('cmd'=>'roky');
  server($x);
//   $y->roky= 'ahoj!';
}
elseif ( $ip_ok && isset($_GET['foto']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'foto','rok'=>$_GET['rok'],'id'=>$_GET['id'],'groups'=>$_GET['groups']);
  server($x);
}
elseif ( $ip_ok && isset($_GET['free']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'free');
  server($x);
}
elseif ( $ip_ok && isset($_GET['clanek']) ) {
  $FREE= 0; // 1 -- mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'clanek','pid'=>$_GET['clanek'],'groups'=>$_GET['groups']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['clanky']) ) {
  $FREE= 0; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'clanky','chlapi'=>$_GET['clanky'],'back'=>$_GET['back'],'groups'=>$_GET['groups']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['akce']) ) {
  $FREE= 0; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'akce','chlapi'=>$_GET['akce'],'back'=>$_GET['back'],'groups'=>$_GET['groups']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['knihy']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'knihy','chlapi'=>$_GET['knihy'],'back'=>$_GET['back'],'groups'=>$_GET['groups']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['kapitoly']) ) {
  $FREE= 0; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'kapitoly','pid'=>$_GET['kapitoly']);
  server($x);
}
elseif ( $ip_ok && isset($_GET['abstrakt']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'abstrakt','ids'=>$_GET['abstrakt']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['galerie']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  $x= (object)array('cmd'=>'galerie','pid'=>$_GET['galerie']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( $ip_ok && isset($_GET['kniha']) ) {
  $FREE= 1; // mění lokální odkazy na obrázky atp. na globální
  list($cid,$pid)= explode(',',$_GET['kniha']);
  $x= (object)array('cmd'=>'kniha','cid'=>$cid,'pid'=>$pid,
      'page'=>$_GET['page'],'kapitola'=>$_GET['kapitola'],'groups'=>$_GET['groups']);
  server($x);
  $y->my_ip= $ip;
}
elseif ( isset($_GET['upd_menu']) ) {
  global $trace;
  $qry= "DELETE FROM tx_gnmenu WHERE wid=2"; // 1=setkani.org, 2=chlapi.online
  query($qry,'setkani');
  $sub= $_POST['post'];
  $qry= "INSERT INTO tx_gnmenu (wid,mid,mid_top,ref,typ,nazev,next,val,elem) VALUES $sub";
  query($qry,'setkani');
  $y->msg= "post=".strlen($_POST).", get=".strlen($sub).", trace=$trace";
}
elseif ( isset($_GET['redaktor']) ) {
  global $trace;
  $y->id= $_GET['redaktor'];
  ezer_connect("ezer_db2");
  list($y->jmeno,$y->prijmeni,$y->web_level)= 
      select("jmeno,prijmeni,web_level","ezer_db2.osoba","id_osoba=$y->id");
  $y->msg= "trace=$trace";
}
elseif ( isset($_GET['test']) ) {
  $x= (object)array('cmd'=>'test');
  server($x);
  $y->my_ip= $ip;
}
// unset($y->trace);
// var_export($y);
$answer= json_encode($y);
header('Content-type: application/json; charset=UTF-8');
echo $answer;
exit;

# -------------------------------------------------------------------------------------------- my ip
# zjištění klientské IP
function my_ip() {
  return isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
}
?>
