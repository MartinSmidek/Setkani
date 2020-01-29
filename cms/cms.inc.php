<?php # (c) 2016-2018 Martin Smidek <martin@smidek.eu>

  global $ezer_root, $ezer_local, $ezer_server;
  
  date_default_timezone_set('Europe/Prague');

  // nastavení zobrazení PHP-chyb klientem při &err=1
  if ( isset($_GET['err']) && $_GET['err'] ) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 'On');
  }

  // rozlišení verze jádra
  $kernel= 'ezer'.$_SESSION['cms']['ezer'];

  $abs_root= $_SESSION['cms']['abs_root'];
  $rel_root= $_SESSION['cms']['rel_root'];
  chdir($abs_root);

  // on-line přihlášky
  $cms_root= $kernel=='ezer2.2' ? 'ezer3' : 'ezer3.1';

  require_once("$cms_root/server/ezer_cms3.php");
  
  // parametrizace lokálními údaji a detekcí $ezer_server a definicí $dbs
  require_once("../files/setkani4/cms.par.php");

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
  
?>
