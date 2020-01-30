<?php
// ------------------------------------------ LADÍCÍ VERZE 4 ------------------------------------ //
$microtime_start= microtime();
if ( !isset($_SESSION) ) session_start();

error_reporting(0);
if ( isset($_GET['err']) && $_GET['err'] ) error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 'On');

if ( isset($_GET['try'])) $_SESSION['web']['try']= $_GET['try'];

// pro již přihlášeného přejdi do CMS
if ( !count($_POST) && isset($_SESSION['cms']['user_id']) && $_SESSION['cms']['user_id'] ) {
  // pokud je přihlášený be_user proveď reload
  global $feu;
  $feu= 'refresh';
  require_once("cms/cms.php");
  die();
}
// -------------------- nový web

$FREE= 0; // ponechává lokální odkazy na obrázky
//$kernel= "ezer".(isset($_GET['ezer'])?$_GET['ezer']: '3.1'); 
$kernel= "ezer3.1"; 

if ( $kernel=='ezer3.1' ) {
  require_once("$kernel/mysql.inc.php"); // nastavení const EZER_PDO_PORT=1;
  require_once("$kernel/server/ezer_pdo.php");
}

require_once("cms/template.php");
require_once("cms/web_fce.php");
require_once("cms/mini.php");

// on-line přihlášky
$cms_root= $kernel=='ezer2.2' ? 'ezer3' : 'ezer3.1';

// parametrizace lokálními údaji a detekcí $ezer_server a definicí $dbs
require_once("../files/setkani4/cms.par.php");
$ezer_db= $dbs[$ezer_server];

require_once("$cms_root/server/ezer_cms3.php");

$index= "index.php";
$_SESSION['web']['index']=  $index;
$_SESSION['web']['server']= $ezer_server;

$totrace= $ezer_server!=1 ? (isset($_GET['trace']) ? $_GET['trace'] : 'u') : '';  // Mu

ezer_connect('setkani4');
$mysql_db_track= $tracking= '_track';
$mysql_tracked= $tracked= ',osoba,rodina,pobyt,_user,';
if ( count($_POST) ) {
//   $y= (object)array('cmd'=>$x->cmd);
  $x= array2object($_POST);
  $y= $x;
  if ( $y->cms ) {
    $ok= cms_server($y);
  }
  else {
    server($x);
  }
  header('Content-type: application/json; charset=UTF-8');
  $yjson= json_encode($y);
  echo $yjson;
  exit;
}
$href= $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].
    $_SERVER['SCRIPT_NAME'].'?page=';
$path= isset($_GET['page']) ? explode('!',$_GET['page']) : array('home');
javascript_init();
$fe_user= isset($_SESSION['web']['fe_user']) ? $_SESSION['web']['fe_user'] : 0;
$be_user= isset($_SESSION['cms']['user_id']) ? $_SESSION['cms']['user_id'] : 0;
$fe_host= $fe_user || $be_user ? 0 : (
          isset($_SESSION['web']['fe_host']) ? $_SESSION['web']['fe_host'] : 0);
$mode= array(1=>isset($_COOKIE['web_mode_1']) ? $_COOKIE['web_mode_1'] : 1); // mode1=dlaždice
$fe_user_display= isset($_GET['login']) ? 'block' : 'none';

// absolutní cesta
global $ezer_path_root;
$ezer_path_root= $_SESSION['web']['path']= $_SERVER['DOCUMENT_ROOT'];

def_menu(0);
template($href,$path,$fe_host,$fe_user,$be_user);
die();
?>
