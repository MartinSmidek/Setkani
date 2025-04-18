<?php

// ---------------------------------------------------------------------------------------------- //
// funkce aplikace Ezer/CMS společné pro FE (nepřihlášené) a BE (přihlášené)                      //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

/** =========================================================================================> LOCKS */
# -------------------------------------------------------------------------------------- record lock
# pokud je rekord volný tj. lock_kdo=0 vrátí {kdo:''} a zapíše kdy kdo uzamkl
# pokud je rekord zamknutý tj. lock_kdo=id_user vrátí {kdo:_user.username,kdy:datetime}
function record_lock ($pid) {
  $ret= (object)array();
  list($kdo,$kdy)= select("lock_kdo,lock_kdy",'tx_gncase_part',"uid=$pid");
  if ( $kdo ) {
    // zamknutý záznam
    $ret->idu= $kdo;
    $ret->kdo= select("username","_user","id_user='$kdo'") ?: "???";
    $ret->kdy= sql_date($kdy);
  }
  else {
    // volný záznam - zmkni jej
    $ret->idu= 0;
    $ret->kdo= '';
    $id_user= $_SESSION['cms']['user_id'];
    query("UPDATE tx_gncase_part SET lock_kdo='$id_user',lock_kdy=NOW() WHERE uid=$pid");
  }
  return $ret;
}
# ------------------------------------------------------------------------------------ record unlock
# uvolni rekord
# pokud je $unlock_all - uvolni všechny které jsi zamkl 
# (děje se při přihlášení a odhlášení tedy i při refresh)
function record_unlock ($pid,$unlock_all=false) {
  if ( $unlock_all ) {
    $id_user= $_SESSION['cms']['user_id'];
    query("UPDATE tx_gncase_part SET lock_kdo=0,lock_kdy=NOW() WHERE lock_kdo='$id_user'");
  }
  else {
    query("UPDATE tx_gncase_part SET lock_kdo=0,lock_kdy=NOW() WHERE uid=$pid");
  }
  return 1;
}
/** ======================================================================================> GMAIL */
# -------------------------------------------------------------------------------------- gmail_token_go
# ASK - vrátí obsah souboru
function gmail_token_go($par) {
  $_SESSION["gmail_api_refresh_token"] = $par->mail;
  $message = "";
  //TODO DUPLICATE CODE
  $credentials_path = $_SERVER['DOCUMENT_ROOT'].'/../files/setkani4/credential.json';
  $required_privileges = array(
    //"https://www.googleapis.com/auth/gmail.settings.basic", //to view email metadata
    //"https://www.googleapis.com/auth/gmail.send" //to send emails
    // OR
      "https://mail.google.com/" //global privilege
  );
  $tokenPathPrefix = $_SERVER['DOCUMENT_ROOT'].'/../files/setkani4/token_';
  $tokenPathSuffix = '.json';
  $gmail_api_library = $_SERVER['DOCUMENT_ROOT'].'/ezer3.1/server/licensed/google_api/vendor/autoload.php';

  $filePath = $tokenPathPrefix . $par->mail . $tokenPathSuffix;
  if (!is_file($filePath) || !is_readable($filePath)) {
    $message = "Tento email ještě nebyl autentizován pomocí OAuth 2.0. Posílání mailů jím není možné.";
  } else {
    try {
      require_once $gmail_api_library;

      $client = new Google_Client();
      $client->setAuthConfig($credentials_path);
      $client->setPrompt("consent");
      $client->setScopes($required_privileges);
      $client->setAccessType('offline');
      $client->setIncludeGrantedScopes(true);

      //access token
      $accessToken = json_decode(file_get_contents($filePath), true);
      $client->setAccessToken($accessToken);
      //refresh token automatically if necessary
      if ($client->isAccessTokenExpired()) {
        $refreshToken = $client->getRefreshToken();
        if ($refreshToken) {
          $client->fetchAccessTokenWithRefreshToken($refreshToken);
        } else {
          $message = "Token <b>EXPIROVAL</b>. Je nutné jej obnovit.";
        }
      }
    } catch (Exception $e) {
      $message = "Něco se pokazilo. Je možné, že nelze posílat emaily: " . $e->getMessage();
    }
  }
  if ($message == "") $message = "Email je aktivní.";

  return "$message <br><br> <button onclick='window.open(\"/gmail_autentizace\",\"_blank\",\"resizable=yes\")'>Autentizovat</button>";
}


/** ======================================================================================> DATABASE */
# -------------------------------------------------------------------------------------- db get_file
# ASK - vrátí obsah souboru
function db_get_file($path) {
  $html= '';
  if ( file_exists($path) ) {
    $html= file_get_contents($path);
  }
  return $html;
}
# ------------------------------------------------------------------------------------- db transform
# ASK - tranform database setkani -> setkani4
function db_transform($par) {
  $html= '';
  switch ($par->op) {
    // ----------------------------------------- fix 'dum'
    case 'dum setkani: article':
      $je= query("UPDATE setkani4.tx_gncase_part SET tags='K' WHERE 
        cid='1046' OR 
        cid='1586' OR
        cid='1644' OR
        cid='1562'
        ");
      if ( $je ) {
        $html.= "ok";
      }
      break;
    // ----------------------------------------- calendar
    case 'calendar':
      $je= query("UPDATE setkani4.tx_gncase_part SET tags='K' WHERE 
        cid='1046' OR 
        cid='1586' OR
        cid='1644' OR
        cid='1562'
        ");
      if ( $je ) {
        $html.= "ok";
      }
      break;
  // ----------------------------------------- test pass 
  case 'test-pass':
    $je= select("COUNT(*)",'information_schema.tables',
         "table_schema='setkani4' AND table_name='{$par->table}'");
    if ( $je ) {
      $html.= "ok";
    }
    break;
  // ----------------------------------------- DROP unused tables  
  case 'old-tables':
    foreach(explode(',',
        "...bible,...fe_setkani,...fe_sessions,...host,...pages,...room,...tx_gnvip"
        ) as $table ) {
      $ok= query("DROP TABLE `$table`");
      if ( $ok ) {
        $html.= " $table ";
      }
    }
    $html.= " DROPPED ";
    break;
  // ----------------------------------------- DROP unused fields
  case 'old-fields':
    foreach(explode(',',
        "tx_gncase.humor,tx_gncase.akce,tx_gncase.medium,tx_gncase.menu,tx_gncase.status,"
      . "tx_gncase_part.edit_session,tx_gncase_part.edit_userid,tx_gncase_part.ustamp"  
        ) as $TableField ) {
      list($table,$field)= explode('.',$TableField);
      $ok= query("ALTER TABLE `$table` DROP `$field`");
      if ( $ok ) {
        $html.= " $table.$field ";
      }
    }
    $html.= " REMOVED ";
    break;
  // ----------------------------------------- replace _user by temporary content
  case 'tmp-user':
    query("TRUNCATE TABLE _user");
    query("INSERT INTO _user (id_user,abbr,username,password,skills,ips,state,options,forename,surname) VALUES 
           (77,'JHO','jirka','krasnebosovice','a ac aw m r w','','++UuMSaE','Jirka','{}','Horák'),
           (78,'MSM','martin','vysokekohoutovice','a ac aw m r w','127.0.0.1','++UuMSaE','{}','Martin','Šmídek')      
      ");
    query("TRUNCATE TABLE fe_users");
    query("INSERT INTO fe_users (username,password,usergroup,ezer,name,firstname,userlevel) VALUES 
      ('jirka','krasnebosovice','14,2,3,4,5',77,'Horák','Jiří',4),
      ('martin','vysokekohoutovice','14,2,3,4,5',78,'Šmídek','Martin',4)
      ");
    $html.= " obnoveny dočasné účty jirka a martin ";
    break;
  }
  return $html;
}
# ----------------------------------------------------------------------------------- db drop_tables
# ASK - drop all tables from db setkani4
function db_drop_tables_but($db,$but_tables='') {
  $but= explode(',',$but_tables); 
  $cr= pdo_qry("
    SELECT table_name FROM information_schema.columns WHERE table_schema='$db' GROUP BY table_name
  ");
  while ( $cr && (list($table)= pdo_fetch_row($cr)) ) {
    if ( !in_array($table,$but)) {
      query("DROP TABLE $db.`$table`");
    }
  }
  return 1;
}
# ---------------------------------------------------------------------------------- db clear_tables
# ASK - tranform database setkani -> setkani4
function db_clear_tables($tables) {
  $html= '';
  foreach(explode(',',$tables) as $table) {
    switch ($table) {
    // ----------------------------------------- _TOUCH - ponechej pouze login, me_login, ...
    case '_touch':
      $n= query("DELETE FROM _touch WHERE NOT menu REGEXP 'log|time'");
      $html.= "<br>$table $n rec deleted ";
      break;
    }
  }
  return $html;
}
/** =========================================================================================> ADMIN */
# ------------------------------------------------------------------------------------- admin report
# vrátí seznam chybných pokusů o přihlášené do chlapi.online
function admin_report($par) { trace();
  $html= "";
  switch ($par->cmd) {
  case 'me_login':
    $cr= pdo_qry("
      SELECT day,time,msg
      FROM setkani._touch
      WHERE module='chlapi.onlin' AND menu='me_login'
      ORDER BY day DESC, time desc
    ");
    while ( $cr && (list($day,$time,$msg)= pdo_fetch_row($cr)) ) {
      $html.= "$day $time $msg<br>";
    }
    break;
  }
  return $html;
}
# -------------------------------------------------------------------------------------- sitemap url
/**
 * 
 * @param type $ref
 * @param type $last
 * @param type $freq
 * @param type $prior
 * @return string
 */
function sitemap_url($ref,$last,$freq='monthly',$prior='0.5') {
  $loc= "https://www.setkani.org/$ref";
  $xml=  "\n<url>";
  $xml.= "\n<loc>$loc</loc>";
  $xml.= "\n  <lastmod>$last</lastmod>";
  $xml.= "\n  <changefreq>$freq</changefreq>";
  $xml.= "\n  <priority>$prior</priority>";
  $xml.= "\n</url>";
  return $xml;
}
# -------------------------------------------------------------------------------------- tstamp2date
/**
 * 
 * @param type $ts
 * @return type
 */
function tstamp2date($ts,$par='') {
  return date('j.n.Y',$ts.$par);
}
# ------------------------------------------------------------------------------------------ def mid
function def_mid($elem,$mid,$overwrite=0) { trace();
  list($typ,$ids)= explode('=',$elem);
  if ( !$ids ) goto end;
  $AND= $overwrite ? ' WHERE mid=0' : '';
  switch ($typ) {
  case 'vlakno':
    query("UPDATE tx_gncase SET mid=$mid WHERE uid=$ids $AND");
    break;
  case 'clanky':
  case 'knihy':
    query("UPDATE tx_gncase SET mid=$mid WHERE pid IN ($ids) $AND");
    break;
  }
end:
  return;
}
# ---------------------------------------------------------------------------------------- admin web
# web odkazy  - vrátí seznam článků s odkazem na jin článek ve starém formátu
# web sitemap - vygeneruje aktuální sitemap.xml
function admin_web($typ,$uid=0) { trace();
  global $CMS, $href0, $mode, $def_block;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden;
  global $ezer_path_root, $ezer_server;
  switch ($typ) {

  case 'web_reload':   // ------------------------------------------------ reload menu
    def_menu(0);
  case 'web_reconstr': // ------------------------------------------------ definice menu
    $wid= 1;
    $qry= "INSERT INTO tx_gnmenu (wid,mid,ref,typ,site,mref,event,nazev,next,val,elem,title) VALUES";
    $del= "\n";
    $letos= date('Y');
    $qries= array();
    foreach ($def_block as $ref=>$def) {
      $def= explode(':',$def);
      $def= array_map(trim,$def);
      $def= array_map(pdo_real_escape_string,$def);
      list($typ_bloku,$mid,$site,$mref,$context,$nazev,$next,$default,$elems,$title)= $def;
      $mref= str_replace($letos,'Y',$mref);
      if ( $typ_bloku=='sm' ) continue;
      $qry.= "$del($wid,$mid,'$ref','$typ_bloku','$site','$mref','$context','$nazev','$next',"
        . "'$default','$elems','$title')";
      $del= ",\n";
      list($elem)= explode(';',$elems);
      list($typ,$ids)= explode('=',$elem.'=');
      if ( $typ=='menu' ) {
        $mids= $delm= '';
        foreach (explode(',',$ids) as $ref2) {
          $def2= $def_block[$ref2];
          $def= explode(':',$def2);
          $def= array_map(trim,$def);
          $def= array_map(pdo_real_escape_string,$def);
          list($typ_bloku,$mid2,$site,$mref,$context,$nazev,$next,$default,$elems,$title)= $def;
          $mids.= "$delm$mid2"; $delm= ',';
          $mref= str_replace($letos,'Y',$mref);
          $qry.= "$del($wid,$mid2,'$ref!$ref2','$typ_bloku','$site','$mref','$context','$nazev','$next',"
            . "'$default','$elems','$title')";
        }
        $qries[$mid]= $mids;
      }
    }
    query("DELETE FROM tx_gnmenu WHERE wid=1"); // 1=setkani.org, 2=chlapi.online
    query($qry);
    foreach($qries as $mid=>$mids) {
      query("UPDATE tx_gnmenu SET mid_top=$mid WHERE mid IN ($mids)");
    }
    // doplnění invariantů NEW/UPD: c.tstamp>=c.crdate, c.tstamp>=p.tstamp, p.tstamp>=p.crdate=p.date
    query("UPDATE tx_gncase_part SET crdate=date WHERE date>crdate");
    query("UPDATE tx_gncase_part SET tstamp=crdate WHERE crdate>tstamp");
    query("
      UPDATE tx_gncase AS c JOIN (
        SELECT cid,MAX(p.tstamp) AS p_tstamp,MAX(p.crdate) AS p_crdate
        FROM tx_gncase_part AS p
        WHERE p.deleted=''
        GROUP BY cid
      ) AS p ON p.cid=c.uid
      SET c.tstamp=p_tstamp, c.crdate=p_crdate");
    // obnova tx_gnmenu.tstamp ze tx_gncase_part.tstamp
    // obnova tx_gnmenu.crdate ze tx_gncase_part.crdate
    query("
      UPDATE tx_gnmenu AS m JOIN (
        SELECT mid,MAX(p.tstamp) AS p_tstamp,MAX(c.crdate) AS c_crdate
        FROM tx_gncase_part AS p JOIN tx_gncase AS c ON cid=c.uid
        WHERE mid GROUP BY mid
      ) AS p ON p.mid=m.mid
      SET m.tstamp=p_tstamp, m.crdate=c_crdate");
    // aktualizace změn na home-page
    $nejnovejsi= select1("MAX(tstamp)","tx_gncase_part","homepage IN (1,2,6,7,8)");
    query("UPDATE setkani4.tx_gnmenu SET tstamp=$nejnovejsi WHERE mid='32'");
    // přenesení změn ze submenu do hlavního menu
    query("
      UPDATE tx_gnmenu AS h JOIN (
        SELECT mid_top,MAX(tstamp) AS s_tstamp,MAX(crdate) AS s_crdate
        FROM tx_gnmenu GROUP BY mid_top
      ) AS s ON s.mid_top=h.mid
      SET h.tstamp=s_tstamp,h.crdate=s_crdate");
    $h= "menu obnoveno";
    $h= "<div class='vlakno'><div><div class='clanek'><div class='text'>$h</div></div></div></div>";
    break;

//  case 'web_reconstr_1': // ---------------------------------------------- definice fe_groups
//    $cr= pdo_qry("
//      SELECT g.uid,c.uid,g.fe_group,c.fe_groups
//      FROM tx_gncase AS c
//      JOIN pages AS g ON g.uid=c.pid
//      WHERE g.fe_group>0 AND !g.hidden AND !g.deleted AND g.fe_group!=c.fe_groups
//      ORDER BY g.uid
//    ");
//    while ( $cr && (list($pid,$cid,$fe_group,$fe_groups)= pdo_fetch_row($cr)) ) {
//      $h.= "$pid,$cid,$fe_group,$fe_groups<br>";
//      query("UPDATE tx_gncase SET fe_groups=$fe_group WHERE uid=$cid");
//    }
//    $h= "<div class='vlakno'><div><div class='clanek'><div class='text'>$h</div></div></div></div>";
//    break;

//  case 'web_reconstr_0': // ---------------------------------------------- definice mid
//    $pocatek= 2003;
//    $letos= date('Y');
//    $today= date('Y-m-d');
////     $xml= '';
//    // odkazy do archivů
////     $xml.= sitemap_url('archiv/index.htm','2000-01-01','yearly');       // Manželská setkání
////     $xml.= sitemap_url('archiv2/setkani.htm','2001-01-01','yearly');    // chlapi
//    // odkazy z definice menu
//    foreach ($def_block as $ref=>$def) {
//      list($typ_bloku,$mid,$site,$mref,$_,$_,$_,$_,$elems1)= explode(':',$def);
//      $mref= trim($mref);
//      $elems1= str_replace(' ','',$elems1);
//      list($priority)= explode(',',trim($site));
//      $priority= $priority ?: '0.5';
//      // top menu
//      if ( $typ_bloku=='tm' /*&& $mref!='-'*/ ) {
//                                                        display("$ref $elems1");
//        foreach (explode(';',$elems1) as $elem) {
//          def_mid(trim($elem),$mid);
//        }
////         $xml= sitemap_url($mref,$today,'daily',$priority).$xml;
//      }
//      // hlavní menu
//      elseif ( $typ_bloku=='hm' /*&& $mref!='-'*/ ) {
//        list($elem)= explode(';',$elems1);
//        list($typ,$ids)= explode('=',$elem.'=');
//        if ( $typ=='menu' ) {
//          foreach (explode(',',$ids) as $ref2) {
//            // submenu
//            list($typ2,$mid2,$site2,$mref2,$_,$_,$_,$_,$elems2)= explode(':',$def_block[$ref2]);
//            list($priority2)= explode(',',trim($site2));
//            $priority2= $priority2 ?: '0.5';
//            if ( $mref2!='-' ) {
//              $mref2= trim($mref2);
//              if ( $mref2=="alberice/$letos" )  {
////                 for ($rok= $letos; $rok>$pocatek; $rok--) {
////                   $change= $rok==$letos ? $today : "$rok-12-31";
////                   $freq=   $rok==$letos ? 'weekly' : 'yearly';
////                   $prior=  $rok==$letos ? '0.9' : '0.1';
////                   $xml.= sitemap_url("alberice/$rok",$change,$freq,$prior);
////                 }
//              }
//              else {
//                foreach (explode(';',$elems2) as $elem) {
//                  def_mid(trim($elem),$mid2);
//                }
////                 $xml.= sitemap_url($mref2,$today,'weekly',$priority2);
//              }
//            }
//          }
//        }
//        elseif ( $mref=='akce' )  {
////           $xml.= sitemap_url('akce/nove',$today,'weekly',1);
//          for ($rok= $letos; $rok>=$pocatek; $rok--) {
//            $change= $rok==$letos ? $today : "$rok-12-31";
//            $freq=   $rok==$letos ? 'weekly' : 'yearly';
//            $prior=  $rok==$letos ? '0.9' : '0.1';
////             $xml.= sitemap_url("akce/$rok",$change,$freq,$prior);
//          }
//        }
//        else {
//          foreach (explode(';',$elems1) as $elem) {
//            def_mid(trim($elem),$mid);
//          }
////           $xml.= sitemap_url($mref,$today,'weekly',$priority);
//        }
//      }
//    }
/*
    $xml= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
        . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
        . $xml
        . "\n</urlset>";
    $ok= file_put_contents("$ezer_path_root/sitemap.xml",$xml);
    if ( $ok ) {
      if ( $ezer_server ) {
        // upozorníme google, pokud jsme na ostrém serveru
        $ping= "http://www.google.com/webmasters/tools/ping?sitemap=https%3A%2F%2Fwww.setkani.org%2Fsitemap.xml";
        $google= file_get_contents($ping);
        $google= strip_tags($google,'<p><a>');
        $h= "<h3>Soubor sitemap.xml byl zapsán a bylo posláno upozornění pro Google</h3>";
        $h.= "<div style='border-left:3px solid black;font-size:8pt;padding:5px;margin:10px;'>$google</div>";
      }
      else {
        $h= "<h3>Soubor sitemap.xml byl zapsán jen lokálně</h3>";
      }
    }
    else {
      $h= "<b style='color:red'>Soubor sitemap.xml nebyl zapsán</b><br><br>";
    }
    $h.= nl2br(strtr(htmlentities($xml),array(
      ' '=>'&nbsp;','&lt;loc&gt;'=>'&lt;loc&gt;<b>','&lt;/loc&gt;'=>'</b>&lt;/loc&gt;')));
*/
//    $h= "<div class='vlakno'><div><div class='clanek'><div class='text'>$h</div></div></div></div>";
//    break;

  case 'web_sitemap':  // ------------------------------------------------ sitemap
//                                                         debug($def_block,$ezer_server);
    $pocatek= 2003;
    $letos= date('Y');
    $today= date('Y-m-d');
    $xml= '';
    // odkazy do archivů
    $xml.= sitemap_url('archiv/index.htm','2000-01-01','yearly');       // Manželská setkání
    $xml.= sitemap_url('archiv2/setkani.htm','2001-01-01','yearly');    // chlapi
    // odkazy z definice menu
    foreach ($def_block as $ref=>$def) {
      list($typ_bloku,$mid,$site,$mref,$_,$_,$_,$_,$elems1)= explode(':',$def);
      $mref= trim($mref);
      $elems1= str_replace(' ','',$elems1);
      list($priority)= explode(',',trim($site));
      $priority= $priority ?: '0.5';
      // top menu
      if ( $typ_bloku=='tm' && $mref!='-' ) {
        $xml= sitemap_url($mref,$today,'daily',$priority).$xml;
      }
      // hlavní menu
      elseif ( $typ_bloku=='hm' && $mref!='-' ) {
                                                        display($elems1);
        list($elem)= explode(';',$elems1);
        list($typ,$ids)= explode('=',$elem.'=');
        if ( $typ=='menu' ) {
          foreach (explode(',',$ids) as $ref2) {
            // submenu
            list($typ2,$mid2,$site2,$mref2,$_,$_,$_,$_,$elems2)= explode(':',$def_block[$ref2]);
            list($priority2)= explode(',',trim($site2));
            $priority2= $priority2 ?: '0.5';
            if ( $mref2!='-' ) {
              $mref2= trim($mref2);
              if ( $mref2=="alberice/$letos" )  {
                for ($rok= $letos; $rok>$pocatek; $rok--) {
                  $change= $rok==$letos ? $today : "$rok-12-31";
                  $freq=   $rok==$letos ? 'weekly' : 'yearly';
                  $prior=  $rok==$letos ? '0.9' : '0.1';
                  $xml.= sitemap_url("alberice/$rok",$change,$freq,$prior);
                }
              }
              else {
                $xml.= sitemap_url($mref2,$today,'weekly',$priority2);
              }
            }
          }
        }
        elseif ( $mref=='akce' )  {
          $xml.= sitemap_url('akce/nove',$today,'weekly',1);
          for ($rok= $letos; $rok>=$pocatek; $rok--) {
            $change= $rok==$letos ? $today : "$rok-12-31";
            $freq=   $rok==$letos ? 'weekly' : 'yearly';
            $prior=  $rok==$letos ? '0.9' : '0.1';
            $xml.= sitemap_url("akce/$rok",$change,$freq,$prior);
          }
        }
        else {
          $xml.= sitemap_url($mref,$today,'weekly',$priority);
        }
      }
    }
    $xml= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
        . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
        . $xml
        . "\n</urlset>";
    $ok= file_put_contents("$ezer_path_root/sitemap.xml",$xml);
    if ( $ok ) {
      if ( $ezer_server ) {
        // upozorníme google, pokud jsme na ostrém serveru
        $ping= "http://www.google.com/webmasters/tools/ping?sitemap=https%3A%2F%2Fwww.setkani.org%2Fsitemap.xml";
        $google= file_get_contents($ping);
        $google= strip_tags($google,'<p><a>');
        $h= "<h3>Soubor sitemap.xml byl zapsán a bylo posláno upozornění pro Google</h3>";
        $h.= "<div style='border-left:3px solid black;font-size:8pt;padding:5px;margin:10px;'>$google</div>";
      }
      else {
        $h= "<h3>Soubor sitemap.xml byl zapsán jen lokálně</h3>";
      }
    }
    else {
      $h= "<b style='color:red'>Soubor sitemap.xml nebyl zapsán</b><br><br>";
    }
    $h.= nl2br(strtr(htmlentities($xml),array(
      ' '=>'&nbsp;','&lt;loc&gt;'=>'&lt;loc&gt;<b>','&lt;/loc&gt;'=>'</b>&lt;/loc&gt;')));
    $h= "<div class='vlakno'><div><div class='clanek'><div class='text'>$h</div></div></div></div>";
    break;

  case 'web_zmeny':   // ------------------------------------------------ změny
  case 'web_odkazy':  // ------------------------------------------------ odkazy
    $xx= array();
    $dnu= 21 * 60*60*24;
    $cond= $typ=='web_odkazy' ? " AND text REGEXP 'href=.*&case=' AND tags='A' " : (
           $typ=='web_zmeny'  ? " AND UNIX_TIMESTAMP()-p.tstamp < $dnu" : 0
    );
    $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
    global $userid;
    $spec_user= $userid ? "IF(ids_osoba,FIND_IN_SET($userid,ids_osoba),1)" : "!ids_osoba";
    $cr= pdo_qry("
      SELECT p.uid, c.uid, fe_groups, tags, p.title, text, p.deleted, p.hidden, FROM_UNIXTIME(p.tstamp)
      FROM setkani4.tx_gncase AS c
      JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
      -- JOIN setkani.pages AS g ON c.pid=g.uid
      WHERE !c.deleted AND !c.hidden $p_show $cond
        AND fe_groups IN ($usergroups)
        AND $spec_user
      ORDER BY p.tstamp DESC
    ");
    while ( $cr && (
      list($p_uid,$cid,$fe_group,$tags,$title,$text,$del,$hid,$chng)= pdo_fetch_row($cr)) ) {
      $tags.= $del ? 'd' : '';
      $tags.= $hid ? 'h' : '';
      $text= web_text($text);
      if ( $p_uid!=$uid ) {
        $text= x_shorting($text);
      }
      $xx[$cid]= (object)array(
        'ident'=>$p_uid,'nadpis'=>$title,'abstract'=>$text,'tags'=>$tags,'chng'=>$chng);
    }
    $found= count($xx)." článků";
  //                                                         debug($xx);
    $h= "<div id='list' class='x'>";
    $abstr= $mode[1] ? 'abstr' : 'abstr-line';
    foreach($xx as $cid=>$x) {
  //                                                       display("články {$x->ident} ? $uid");
      $flags= strpos($xx[$cid]->tags,'6')!==false
        ? " <i class='fa fa-key' style='color:red'></i> " : '';
      $ex= strpos($xx[$cid]->tags,'d')!==false ? ' abstrakt_deleted' : (
           strpos($xx[$cid]->tags,'h')!==false ? ' abstrakt_hidden' : '');
      $code= cid_pid_chng($cid,$x->ident,$x->chng);
      $jmp= $CMS ? "onclick=\"go(arguments[0],'$href0!$x->ident#vlakno');\""
                 : "href='$href0!$x->ident#vlakno'";
      $h.= $x->ident==$uid
          ? vlakno($cid,'clanek')
          : "<div class='$abstr x'>
               $code
               <a class='abstrakt $ex x$css' $jmp>
                 <b>$x->nadpis:</b> $flags $x->abstract
                 <hr style='clear:both;border:none'>
               </a>
             </div>";
    }
    $h.= "</div>";
    break;
  }
  return $h;
}
/** ===========================================================================================> GIT */
# ----------------------------------------------------------------------------------------- git make
# provede git par.cmd>.git.log a zobrazí jej
# fetch pro lokální tj. vývojový server nepovolujeme
function git_make($par) {
  global $abs_root;
  $bean= preg_match('/bean/',$_SERVER['SERVER_NAME'])?1:0;
                                                    display("bean=$bean");
  $cmd= $par->cmd;
  $folder= $par->folder;
  $lines= '';
  $msg= "";
  // proveď operaci
  switch ($par->op) {
  case 'cmd':
    if ( $cmd=='fetch' && $bean) {
      $msg= "na vývojových serverech (*.bean) příkaz fetch není povolen ";
      break;
    }
    $state= 0;
    // zruš starý obsah .git.log
    $f= @fopen("$abs_root/docs/.git.log", "r+");
    if ($f !== false) {
        ftruncate($f, 0);
        fclose($f);
    }
    if ( $folder=='ezer') chdir("../_ezer3.1");
    $exec= "git $cmd>$abs_root/docs/.git.log";
    exec($exec,$lines,$state);
                            display("$state::$exec");
    // po fetch ještě nastav shodu s github
    if ( $cmd=='fetch') {
      $msg.= "$state:$exec\n";
      $cmd= "reset --hard origin/master";
      $exec= "git $cmd>$abs_root/docs/.git.log";
      exec($exec,$lines,$state);
                            display("$state::$exec");
    }
    if ( $folder=='ezer') chdir($abs_root);
    $msg.= "$state:$exec\n";
  case 'show':
    $msg.= file_get_contents("$abs_root/docs/.git.log");
    break;
  }
  $msg= nl2br(htmlentities($msg));
  $msg= "<i>Synology: musí být spuštěný Git Server (po aktualizaci se vypíná)</i><hr>$msg";
  return $msg;
}
/** ==========================================================================================> EDIT */
# --------------------------------------------------------------------------------- edit next_footer
# přejde na další resp. předchozí položku zápatí, pro curr_id=0 najde první
function edit_next_footer($curr_id,$smer=1) {
  if ( $curr_id ) {
    if ( $smer ) {
      $rel= $smer==1 ? '<' : '>';
      $curr_id= select("id",'footer',"id $rel '$curr_id' LIMIT 1");
    }
  }
  else {
    $curr_id= select("id",'footer',"1 LIMIT 1");
  }
  return $curr_id;
}
# --------------------------------------------------------------------------------- edit test_online
# otestuje id_akce v online přihlášce proti aktuálnímu url (proměnné last v $.ezer)
function edit_test_online($id_akce,$last) {
  $msg= 1;
  // je to vůbec číslo akce?
  $ok= select('COUNT(*)','akce',"id_duakce='$id_akce'",'ezer_db2');
  if ( !$ok ) {
    $msg= "POZOR $id_akce není ID akce v Answeru"; goto end;
  }
  list($url,$verze)= select("web_url,IF(web_online='',1,2)",'akce',"id_duakce='$id_akce'",'ezer_db2');
  if ($verze==1) {
    $xlast= explode('!',$last);
    $ida_web= $xlast[count($xlast)-1];
    $xlast= explode('/',$url);
    $ida_ans= $xlast[count($xlast)-1];
    list($ida_ans)= explode('#',$ida_ans);
    if ( $ida_web!=$ida_ans ) {
      $msg= "ID akce nesouhlasí s údaji zapsanými v Answeru ($ida_web - $ida_ans)";
    }
  }
  elseif ($verze==2) {
    $msg= 2;
  }
end:  
  return $msg;
}
/** ===========================================================================================> IMG */
# --------------------------------------------------------------------------------------- img oprava
# opraví obrázky v part
#  a) odstraní embeded obrázky
function img_oprava($pid) {
  $msg= "";
  $count= 0;
  list($text,$title)= select("text,title","tx_gncase_part","uid=$pid");
  $text= preg_replace("/<img[^>]+src=.data:image[^>]+\>/i","(embeded image)",$text,-1,$count);
  if ( $count ) {
    $text= pdo_real_escape_string($text);
                                                       display($text);
    query("UPDATE tx_gncase_part SET text='$text' WHERE uid=$pid");
    $msg.= "$count embeded img odstraněno z $title";
  }
  else {
    $msg.= "žádný embeded img ve $title";
  }
  return $msg;
}
/** ===========================================================================================> CMS */
# --------------------------------------------------------------------------------------- cms report
# vrátí seznam 
# - změn obsahu
# - chybných pokusů o přihlášení do chlapi.online
function cms_report($par) { debug($par,'log_report');
  $html= "";
  switch ($par->cmd) {
  case 'obsah':    // -------------------------------------- obsah
    $dnu= $par->days;
    $html.= "<dl>";
    $cr= pdo_qry("
      SELECT fe_user,LEFT(MAX(datetime),16),
        GROUP_CONCAT(DISTINCT LEFT(action,1) ORDER BY action) AS _jak,
        uid_menu,uid_case,uid_part,CONCAT(firstname,' ',name),COUNT(*) AS _krat,message
      FROM gn_log AS g LEFT JOIN fe_users AS u ON u.uid=g.fe_user
      WHERE datetime > DATE_SUB(NOW(),INTERVAL $dnu DAY)
        AND action IN ('Insert','Update')
      GROUP BY uid_case,uid_part,fe_user,DATE(datetime)
      ORDER BY datetime DESC
    ");
    while ( $cr && (list($kdo,$kdy,$_jak,$mid,$cid,$pid,$username,$krat,$path)
        = pdo_fetch_row($cr)) ) {
      $jak= '';
      foreach (explode(',',$_jak) as $j) {
        $jak.= $j=='U' ? ' úprava' : (
            $j=='I' ? ' <b>vložení</b>' : (
            $j=='D' ? ' smazání' : (
            $j=='H' ? ' skrytí' : '?')));
      }
      $krat= $krat==1 ? "" : " ($krat x)";
      $path.= strchr($path,'#') ? '' : "#anchor$pid";
      $go= "<a onclick=\"go_anchor(0,'page=$path','')\" title='$path'>
          &nbsp;<i class='fa fa-arrow-right'></i>&nbsp;</a>";
      $html.= "$kdy <b>$username</b> - $jak $co $cid/$pid $krat $go<br>";
    }
    $html.= "</dl>";
    break;
  }
  return $html;
}
# ----------------------------------------------------------------------------------------- cms test
# vývoj
function cms_test($values) {
                                                        debug($values);
  return 1;
}
# ------------------------------------------------------------------------------------ cms copy_user
# vytvoří kopii BE uživatele v tabulce fe_users
function cms_copy_user($id_user,$level,$groups) { trace();  debug($groups);
  list($user,$pass,$fname,$sname)=
    select("username,password,forename,surname",'_user',"id_user=$id_user");
  $now= time();
  $groups= implode(',',(array)$groups);
  query("INSERT INTO fe_users (crdate,username,password,usergroup,ezer,firstname,name,userlevel)
         VALUES ($now,'$user','$pass','$groups',$id_user,'$fname','$sname',$level)");
  return 1;
}
# --------------------------------------------------------------------------------------- cms add_ip
# ASK
# zjistí, zda gn_log ukazuje na existujícího be-uživatele
# pokud je doit, přidá mu IP, jinak jen vrátí dotaz
function cms_add_ip($idl,$doit=0) {
  $msg= $err= '';
  list($name,$ip)= select("message,ip","gn_log","uid=$idl");
  list($idu,$ips,$user,$abbr)= select("id_user,ips,username,abbr","_user",
    "CONCAT(forename,' ',surname)='$name'");
  if ( $doit ) {
    $ips= $ips ? "$ip,$ips" : "$ip";
    query("UPDATE _user SET ips='$ips' WHERE id_user=$idu");
  }
  else {
    if ( $idu ) {
      if ( strstr(",$ips,",",$ip,")!==false )
        $err= "$user ($abbr) už $ip povolenou má";
      else
        $msg= "Opravdu přiřadit $ip jako povolenou adresu pro $user ($abbr)?";
    }
    else $err= "$name není jménem redaktora";
  }
  return (object)array('err'=>$err,'msg'=>$msg);
}
//# ------------------------------------------------------------------------------------------ session
//# getter a setter pro _SESSION
//function session($is,$value=null) {
//  $i= explode(',',$is);
//  if ( is_null($value) ) {
//    // getter
//    switch (count($i)) {
//    case 1: $value= $_SESSION[$i[0]]; break;
//    case 2: $value= $_SESSION[$i[0]][$i[1]]; break;
//    case 3: $value= $_SESSION[$i[0]][$i[1]][$i[2]]; break;
//    }
//  }
//  else {
//    // setter
//    switch (count($i)) {
//    case 1: $_SESSION[$i[0]]= $value; break;
//    case 2: $_SESSION[$i[0]][$i[1]]= $value; break;
//    case 3: $_SESSION[$i[0]][$i[1]][$i[2]]= $value; break;
//    }
//    $value= 1;
//  }
//  return $value;
//}
/** =========================================================================================> TABLE */
# funkce pro úpravu tabulky účastí
# ----------------------------------------------------------------------------------- cms table_load
# načtení tabulky pro editaci
function cms_table_load($cid) {
  $ret= (object)array('ok'=>1,'msg'=>'','rows'=>array());
  $err= '';
  // kontrola
  $den= select("FROM_UNIXTIME(fromday)","tx_gncase","uid=$cid");
  $pid= select("uid","tx_gncase_part","cid=$cid AND tags='T'");
  if ( !$pid ) { $err= "tabulka pro cid=$cid neexistuje"; goto end; }
  $stamp= select("timestamp","gnucast","datum='$den' AND skupina='maximum'");
  if ( !$stamp ) { $err= "tabulka pro den=$den v gnucast neexistuje"; goto end; }
  // přečtení tabulky jako gnucast.datum=den
  $tr= pdo_qry("
    SELECT COUNT(*),skupina,MAX(poradi) FROM gnucast WHERE datum='$den' GROUP BY skupina");
  while ( $tr && (list($pocet,$nazev,$maxim)= pdo_fetch_row($tr)) ) {
    if ( $nazev!='maximum' ) {
      $ret->rows[]= (object)array('nazev'=>$nazev,'maxim'=>$maxim,'pocet'=>$pocet-1);
    }
  }
end:
  if ( $err ) { $ret->ok= 0; $ret->msg= $err; }
                                                        debug($ret,"cms_table_load($cid)");
  return $ret;
}
# --------------------------------------------------------------------------------- cms table_change
# vytvoření tabulky
function cms_table_change($cid,$rows) {
                                                        debug($rows,"case=$cid");
  $ret= (object)array('ok'=>1,'msg'=>'');
  $max= array();
  $err= '';
  // kontrola
  $den= select("FROM_UNIXTIME(fromday)","tx_gncase","uid=$cid");
  $pid= select("uid","tx_gncase_part","cid=$cid AND tags='T'");
  if ( !$pid ) { $err= "tabulka pro cid=$cid neexistuje"; goto end; }
  $stamp= select("timestamp","gnucast","datum='$den' AND skupina='maximum'");
  if ( !$stamp ) { $err= "tabulka pro den=$den v gnucast neexistuje"; goto end; }
  // přečtení tabulky jako gnucast.datum=den
  $tr= pdo_qry("
    SELECT skupina,MAX(poradi) FROM gnucast WHERE timestamp=$stamp GROUP BY skupina");
  while ( $tr && (list($nazev,$maxim)= pdo_fetch_row($tr)) ) {
    $max[$nazev]= $maxim;
  }
  // úprava změněných maxim a názvů dat tabulky v gnucast, přidání či ubrání řádků
  foreach ($rows as $row) {
    if ( isset($max[$row->stary]) ) {                                   // skupina existuje
      if ( $row->maxim<$row->pocet ) {                                  // .. změna maxima = ko
        $err= "nelze snížit maximum pod počet již přihlášených";
        goto end;
      }
      if ( $row->maxim==0 && $row->pocet==0) {                          // .. zrušení
        query("DELETE FROM gnucast
               WHERE timestamp=$stamp AND skupina='{$row->stary}'");
      }
      if ( $row->maxim!=$max[$row->stary] ) {                           // .. změna maxima = ok
        query("UPDATE gnucast SET poradi='{$row->maxim}'
               WHERE timestamp=$stamp AND skupina='{$row->stary}' AND jmeno='max'");
      }
      if ( $row->nazev!=$row->stary ) {                                 // .. změna názvu
        query("UPDATE gnucast SET skupina='{$row->nazev}'
               WHERE datum='$den' AND skupina='{$row->stary}'");
      }
    }
    else {                                                              // nová skupina
      query("INSERT INTO gnucast(datum,skupina,jmeno,poradi,timestamp)
             VALUES ('$den','{$row->nazev}','max',{$row->maxim},$stamp)");
    }
  }
  $ret->msg= "tabulka byla změněna";
end:
  if ( $err ) { $ret->ok= 0; $ret->msg= $err; }
  return $ret;
}
# --------------------------------------------------------------------------------- cms table_create
# vytvoření tabulky
function cms_table_create($cid,$rows) {
                                                        debug($rows,"case=$cid");
  $ret= (object)array('ok'=>1,'msg'=>'');
  $err= '';
  // kontrola
  $den= select("FROM_UNIXTIME(fromday)","tx_gncase","uid=$cid");
  $pid= select("uid","tx_gncase_part","cid=$cid AND tags='T'");
  if ( $pid ) { $err= "tabulka pro cid=$cid již existuje"; goto end; }
  $stamp= select("timestamp","gnucast","datum='$den' AND skupina='maximum'");
  if ( $stamp ) { $err= "tabulka pro den=$den již v gnucast existuje"; goto end; }
  // vytvoření tabulky jako part.tags=T
  $autor= $_SESSION['web']['fe_username'];
  $stamp= time();
  query("INSERT INTO setkani4.tx_gncase_part (cid,tags,author,date,tstamp)
    VALUES ($cid,'T','$autor',UNIX_TIMESTAMP(),$stamp)");
  $pid= pdo_insert_id();
  // vytvoření tabulky jako gnucast.datum=den
  query("INSERT INTO gnucast(cid,datum,skupina,timestamp) VALUES ($cid,'$den','maximum',$stamp)");
  foreach ($rows as $row) {
    // normalizace názvu
    $nazev= str_replace(' ','_',$row->nazev);
    // vytvoření skupiny
    query("INSERT INTO gnucast(cid,datum,skupina,jmeno,poradi,timestamp)
           VALUES ($cid,'$den','$nazev','max',{$row->maxim},$stamp)");
  }
  $ret->msg= "tabulka byla vytvořena";
end:
  if ( $err ) { $ret->ok= 0; $ret->msg= $err; }
  return $ret;
}
?>
