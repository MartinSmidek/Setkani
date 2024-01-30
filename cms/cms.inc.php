<?php # (c) 2016-2018 Martin Smidek <martin@smidek.eu>

  global $ezer_root, $ezer_local, $ezer_server;
  
  date_default_timezone_set('Europe/Prague');

  // nastavení zobrazení PHP-chyb klientem při &err=1
  if ( isset($_GET['err']) && $_GET['err'] ) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 'On');
  }

$ezer_server= 
    $_SERVER["SERVER_NAME"]=='setkani.bean'    ? 0 : (        // 0:lokální 
    $_SERVER["SERVER_NAME"]=='setkani.org'     ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='www.setkani.org' ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='setkani4.doma'   ? 2 : (        // Synology DOMA
    $_SERVER["SERVER_NAME"]=='setkani4.bean'   ? 3 : (        // 3:lokální VERZE 4 - Jirka
    $_SERVER["SERVER_NAME"]=='setkani4m.bean'  ? 4 : (        // 4:lokální VERZE 4 - Martin
    $_SERVER["SERVER_NAME"]=='setkani4.ide'    ? 5 : -1)))))); // 5:lokální VERZE 4 - Jirka desktop
  
  // rozlišení verze jádra
  $kernel= 'ezer'.$_SESSION['cms']['ezer'];

  $abs_root= $_SESSION['cms']['abs_root'];
  $rel_root= $_SESSION['cms']['rel_root'];
  chdir($abs_root);

  // inicializace objektu Ezer
  $EZER= (object)array(
      'version'=>$kernel,
      'options'=>(object)array(
          'mail' => "martin@smidek.eu",
          'phone' => "603&nbsp;150&nbsp;565",
          'author' => "Martin"
      ),
      'activity'=>(object)array());

  // on-line přihlášky
  $cms_root= $kernel=='ezer3.1' ? 'ezer3.1' : 'ezer3.2';
  require_once("cms/cms.par.php");
  require_once("$cms_root/server/ezer_cms3.php");

  // databáze
  $deep_root= "../files/setkani4";
  require_once("$deep_root/cms.dbs.php");
  
  // přístup na chlapi.cz přes servant_ch
  $chlapi_cz= $chlapi_cz[$ezer_server];

  $path_backup= "$deep_root/sql";
  
  // cesta k utilitám MySQL/MariaDB
  $ezer_mysql_path= array(
      "C:/Apache/bin/mysql/mysql5.7.21/bin",  // *.bean
      "/volume1/@appstore/MariaDB/usr/bin",   // Synology YMCA
      "/volume1/@appstore/MariaDB/usr/bin",   // Synology DOMA
      "C:/Apache/bin/mysql/mysql5.7.21/bin",  // *4m.bean
      "C:/Apache/bin/mysql/mysql5.7.21/bin",  // *4j.bean
      "D:\wamp64\bin\mysql\mysql5.7.31\bin"   //jirka desktop
    )[$ezer_server];

  // ostatní parametry
  $tracking= '_track';
  $tracked= ',osoba,rodina,pobyt,_user,';

  // PHP moduly aplikace 
  $app_php= array(
    "$ezer_root/template.php",
    "$ezer_root/web_fce.php",
    "$ezer_root/cms_fce.php"
  );

  // je to aplikace se startem v rootu
  chdir($abs_root);
//                                                                    echo("3:ezer{$EZER->version}/ezer_ajax.php");
  require_once("$kernel/ezer_ajax.php");
  
  // při reload odemkni zamknuté články
  if ( isset($_SESSION['cms']['refresh']) && $_SESSION['cms']['refresh'] ) {
    $_SESSION['cms']['refresh']= 0;
    record_unlock($uid,true);
  }
  
?>
