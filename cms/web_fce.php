<?php

// ---------------------------------------------------------------------------------------------- //
// funkce aplikace Ezer/CMS společné pro FE (nepřihlášené) a BE (přihlášené)                      //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

/** ===========================================================================================> WEB */
# ---------------------------------------------------------------------------------------- menu save
function menu_save($wid,$tree) {
  function walk ($node,$delv='') {
    $value= "";
    if ( isset($node->prop->data->mid) ) {
      $del= "$delv\n(";
      foreach($node->prop->data as $field => $v) {
        $value.= "$del\"$v\"";
        $del= ",";
      }
      $value.= ")";
    }
    if ( isset($node->down) ) {
      foreach($node->down as $child) {
        if ( !$delv ) 
          $delv= $value ? ',' : '';
        $value.= walk($child,$delv);
      }
    }
    return $value;
  };
  $typ= gettype($tree);
  $len= strlen($tree);
  $m= json_decode($tree);
  $values= walk($m);
  $qry= "INSERT INTO tx_gnmenu "
    ."(wid,mid,mid_top,tstamp,crdate,ref,typ,site,mref,event,nazev,next,val,elem,title) VALUES $values";
                                                      display($qry);
  query("DELETE FROM tx_gnmenu WHERE wid=$wid",'setkani');
  query($qry,'setkani');
  return "AHOJ $typ $len";
}
# ---------------------------------------------------------------------------------------- menu tree
function menu_tree($wid) {
  //{prop:°{id:'ONE'},down:°[°{prop:°{id:'TWO'}},°{prop:°{id:'THREE'}}]}
  $menu= 
    (object)array(
      'prop' => (object)array('id'=>'menu'),
      'down' => array(
        (object)array(
          'prop' => (object)array('id'=>'top menu'),
          'down' => array()
        ),    
        (object)array(
          'prop' => (object)array('id'=>'main menu'),
          'down' => array()
        )
      )
    );    
  $mn= pdo_qry("SELECT * FROM tx_gnmenu WHERE wid=$wid ORDER BY typ,mid_top,mid",
      0,0,0,'setkani');
  while ( $mn && ($m= pdo_fetch_object($mn)) ) {
    $mid= $m->mid;
    $mid_top= $m->mid_top;
    $typ= $m->typ;
    $nazev= $m->ref;
    if ( $typ=='tm' ) {
      $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
      $menu->down[0]->down[]= $node;
    }
    elseif ( $typ=='hm' ) {
      $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
      $menu->down[1]->down[]= $node;
    }
    elseif ( $typ=='sm' ) {
      foreach ( $menu->down[1]->down as $i => $sm ) {
        if ( $sm->prop->data->mid===$mid_top ) {
          $node= (object)array('prop'=>(object)array('id'=>$nazev,'data'=>$m));
          $sm->down[]= $node;
          break;
        }
      } 
    }
  }
  return $menu;
}
# --------------------------------------------------------------------------------------- datum akce
function datum_akce($from,$until) {
  date_default_timezone_set('Europe/Prague');
  if ( $from == $until ) {  //zacatek a konec je stejny den
    $datum_dmy= date(date('Y',$from)==date('Y',time()) ? 'j.n.' : 'j.n.Y',$from);
  }
  elseif ( date('n.Y',$from)==date('n.Y',$until) ) { //zacatek a konec je stejny mesic
    $datum_dmy= date('j',$from).".- ".date('j',$until)
      .".".date( date('Y',$from)==date('Y',time()) ? 'n.' : 'n.Y',$until);
  }
  else { //ostatni pripady
    $datum_dmy= date('j.n.',$from)."- ".date('j.n',$until)
      .".".(date('Y',$from)==date('Y',$until) && date('Y',$from)==date('Y',time()) ? '' : date('Y',$until));
  }
  return $datum_dmy;
}

function datum_cesky($from,$until) {
  date_default_timezone_set('Europe/Prague');
  if ( $from == $until ) {  //zacatek a konec je stejny den
    $datum_dmy = date('j. ',$from);
    $datum_dmy .= czechMonthOf(date('n',$from));
    $datum_dmy .= date('Y',$from)==date('Y',time()) ? '' : ", " . date('Y', $from);
  }
  elseif ( date('n.Y',$from)==date('n.Y',$until) ) { //zacatek a konec je stejny mesic
    $datum_dmy = date('j',$from).". - ".date('j. ',$until);
    $datum_dmy .= czechMonthOf(date('n',$from));
    $datum_dmy .= date('Y',$from)==date('Y',time()) ? '' : ", " . date('Y', $from);
  }
  else { //ostatni pripady
    $fromYear = date('Y',$from);
    $toYear = date('Y',$until);
    $now = date('Y',time());

    $datum_dmy = date('j. ',$from);
    $datum_dmy .= czechMonthOf(date('n',$from));
    if ($fromYear!=$toYear && $fromYear!=$now) {
      $datum_dmy .= ", " . $fromYear;
    }
    $datum_dmy .=  " - ";
    $datum_dmy .= date('j. ',$until);
    $datum_dmy .= czechMonthOf(date('n',$until));
    if ($toYear!=$now) {
      $datum_dmy .= ", " . $toYear;
    }
  }
  return $datum_dmy;
}

function czechMonthOf($month) {
  return array('ledna', 'února', 'března', 'dubna', 'května', 'června',
      'července', 'srpna', 'září', 'října', 'listopadu', 'prosince')[$month - 1];
}

function czechMonth($month) {
  return array('leden', 'únor', 'březen', 'duben', 'květen', 'červen',
      'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec')[$month - 1];
}

function numOfDaysInMonth($month) {
  switch ($month) {
    case 1:
    case 3:
    case 5:
    case 7:
    case 8:
    case 10:
    case 12:
      return 31;
    case 2:
      return 28;
    default:
      return 30;
  }
}

//query of program that is stored in the database in the form: 1,2,3...
//todo verify whether the color can be obtained this way...
function barva_programu($program) {
  $min = 999;
  if (!$program || strlen($program) <= 0) return '#ffffff';
  for ($idx = 0; $idx < strlen($program); $idx +=2) {
    if($program[$idx] < $min) {
      $min = $program[$idx];
    }
  }
  return barva_programu_z_cisla($min);
}

function barva_programu_z_textu($pro_koho) {
  switch ($pro_koho) {
    case 'rodiny': return barva_programu_z_textu(1);
    case 'manzele': return barva_programu_z_textu(2);
    case 'chlapi': return barva_programu_z_textu(3);
    case 'zeny': return barva_programu_z_textu(4);
    case 'mladez': return barva_programu_z_textu(5);
    default: return '#ffffff';
  }
}

function barva_programu_z_cisla($pro_koho) {
  switch ($pro_koho) {
    case 1: return '#fe9801';
    case 2: return '#ff677d';
    case 3: return '#ccda46';
    case 4: return '#6f5a7e';
    case 5: return '#73A580';
    default: return '#ffffff';
  }
}
# ------------------------------------------------------------------------------------------ seradit
# seřadí články na stránce podle abecedy
function seradit($ids,$typ) {
  $sorting= 0;
  $rc= pdo_qry(
    $typ=='knihy' ? "
      SELECT c.uid
      FROM setkani4.tx_gncase AS c
        JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uidbarb
      WHERE !c.deleted AND !c.hidden AND c.pid IN ($ids) AND tags='C'
      ORDER BY p.author DESC,p.title DESC" : (
    $typ=='tance' ? "
      SELECT c.uid
      FROM setkani4.tx_gncase AS c
        JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
      WHERE !c.deleted AND !c.hidden AND c.pid IN ($ids)
      ORDER BY p.title DESC" : ''
  ));
  while ( $rc && (list($uid)= pdo_fetch_row($rc)) ) {
    $sorting++;
    query("UPDATE setkani4.tx_gncase SET sorting=$sorting WHERE uid=$uid");
  }
  return 1;
}
# --------------------------------------------------------------------------------------- access_get
# vrátí přístupová práva ve _SESSION[web][fe_usergroups]
function access_get($key=0) {
  $ret= '';
  if ( $key ) {
    $x= explode(',',$_SESSION['web']['fe_usergroups']);
    $i= array_search($key,$x);
    $ret= $i===false ? 0 : 1;
  }
  else {
    $ret= $_SESSION['web']['fe_usergroups'];
  }
  return $ret;
}
# --------------------------------------------------------------------------------------- access_set
# upraví přístupová práva ve _SESSION[web][fe_usergroups]
function access_set($keys,$on) {
  $x= explode(',',$_SESSION['web']['fe_usergroups']);
  foreach (explode(',',$keys) as $key) {
    $i= array_search($key,$x);
    if ( $i===false && $on )
      $x[]= $key;
    elseif ( $i!==false && !$on )
      unset($x[$i]);
  }
  $_SESSION['web']['fe_usergroups']= implode(',',$x);
  return 1;
}
# --------------------------------------------------------------------------------------- visibility
# vrátí resp. nastaví nastavenou hodnotu _SESSION[web][hidden|deleted]
function visibility($key,$value='-') {
  if ( $value=='-' ) { // getter
    $y= isset($_SESSION['web'][$key]) ? $_SESSION['web'][$key] : 0;
  }
  else {
    $_SESSION['web'][$key]= $value;
    $y= 1;
  }
  return $y;
}
# ------------------------------------------------------------------------------------------- ip get
# zjištění klientské IP
function ip_get() {
  return isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
}
# ----------------------------------------------------------------------------------------- ip watch
function ip_watch(&$my_ip,$log=0) {
  // ověříme známost počítače - zjištění klientské IP
  $my_ip= ip_get();
  // zjištění dosud povolených IP
  $ips= select("GROUP_CONCAT(ips)","_user","ips!=''");
  // kontrola
  $ips= str_replace(' ','',$ips);
  $ip_ok= strpos(",$ips,",",$my_ip,")!==false;
  if ( $log && !$ip_ok ) {
    // zapiš pokus o neautorizovaný přístup
    $day= date('Y-m-d'); $time= date('H:i:s');
    $browser= $_SERVER['HTTP_USER_AGENT'];
    $qry= "INSERT _touch (day,time,user,module,menu,msg)
           VALUES ('$day','$time','','error','ip?','|$my_ip||$browser')";
    $res= pdo_query($qry);
  }
  return $ip_ok;
}
# ----------------------------------------------------------------------------------------- pid2menu
function pid2menu($pid) { trace();
  list($cid,$mid,$ref,$mref,$type,$program,$rok)= select(
      "cid,mid,ref,mref,type,program,IF(LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10),'nove',YEAR(FROM_UNIXTIME(fromday)))",
      "tx_gncase_part AS p JOIN tx_gncase AS c ON c.uid=cid LEFT JOIN tx_gnmenu USING (mid)","p.uid=$pid");
  return query2menu($pid,$cid,$mid,$ref,$mref,$type,$program,$rok);
}
function query2menu($pid,$cid,$mid,$ref,$mref,$type,$program,$rok) { trace();
  $ret= (object)array('url'=>'','ref'=>'', 'page'=>'', 'direct_url'=>'');
  $mref= str_replace('Y',date('Y'),$mref);
  $url= $direct= '';
  if ( $type==1  ) {
    $url.= "$mref/$pid";
    $direct .= $url . "#anchor$pid";
    $page= "$ref!$pid";
  }
  elseif ( $type==2 && $program ) {
    $progs= array('','rodiny','manzele','chlapi','zeny','mladez');
    $prog= explode(',',$program);
    $komu= $del= '';
    foreach($prog as $i=>$p) if ( $p<6 ) {
      $komu.= $del.$progs[$p];
      $del= ',';
    }
    $url.= "$komu/$rok/$pid";
    $direct .= $url . "#anchor$pid";
    $page= "?page=akce!$komu,$rok!$pid#anchor$pid";
  }
  elseif ( $type==3 || $type==6 || $type==5 ) {
    $url.= "$mref/$cid,$pid";
    $direct .= $url . "#anchor$pid";
    $page= "$ref!$cid,$pid";
  }
  else {
    $url.= 'home';
    $direct .= $url;
    $page= "$ref!$pid";
  }
  $ret->url= $url;
  $ret->direct_url= $direct;
  $ret->page= $page;
  $ret->ref= "<a href='$url'>$url</a>";
  debug($ret,"($cid,$mid,$ref,$mref,$type,$program,$rok)");
  return $ret;
}
/** ==========================================================================================> TEXT */
# ----------------------------------------------------------------------------------------- web text
# transformace textu ze starého webu
#  - překlad odkazů dovnitř webu
function web_text($txt) { //trace();
  global $href0,$href1;
  $txt= str_replace("https://www.setkani.org/","",$txt);
//   $txt= str_replace("img","hr",$txt);
//   $txt= strtr($txt,array("<img"=>"<hr"));
//   $txt= strtr($txt,array("i"=>"x"));
//   $txt= "!!! $txt";
  goto end;
//  list($href1)= explode('page=',$href0);
////                                                         display("href0=$href1");
//  $txt= preg_replace_callback('/<a(.*?)href=(["\'])(.*?)\\2(.*?)>(.*?)<\/a>/i',
//    function($m) {
//      global $href1;
////                                                         display($m[0]);
////                                                         debug($m);
//      $url= $m[3];
//      $u= parse_url($url);
//      $p= explode('/',$u['path']);
//
//      if ( $u['host']=='www.setkani.org' && $p[1]=='index.php'
//        || $u['host']=='setkani.bean'    && $p[1]=='index.php'
//        || !isset($u['host'])            && $p[0]=='index.php' ) {
//        // odkaz článek na starém webu
//        parse_str(str_replace('&amp;','&',$u['query']),$q);
////                                                         debug($u,$url);
////                                                         debug($p,$u['path']);
////                                                         debug($q,$u['query']);
//        $id= $q['id'];
//        $case= $q['case'];
//        $path= "page=clanek!$case";
//        $go= "go(arguments[0],'{$href1}$path')";
//        $url= "<a title=\"$go\"
//          onclick=\"$go;\"><span>{$m[5]}</span></a>";
//      }
//      else {
//        $url= "<a{$m[1]}href='$url'{$m[4]} target='cizi'>{$m[5]}</a>";
//      }
//      return $url;
//    },
//    $txt);
end:
  return $txt;
}
# -------------------------------------------------------------------------------------- x first_img
# vrátí první obrázek s doplněnými atributy, nebo ''
function x_first_img ($html,$size=1) { //trace();
  global $ezer_path_root, $FREE;
  $h= '';
  $is1= preg_match('/<img[^>]+>/i',$html, $m);
  if ( !$is1 ) goto video;
//                                                 debug($m,htmlentities($m[0]));
  $is2= preg_match('/src=(["\'][^"\']*["\'])/i',$m[0], $src);
  if ( !$is2 ) goto video;
//                                                 debug($src,1);
  // našli jsme a zjístíme, zda existuje
  if ( substr($src[1],1,5)=='data:' ) {
    $h= "<div style='max-height:{$size}em;overflow:hidden;float:left;margin-right:4px'>
           <img src='cms/img/ymca_zakladni.png' style='width:{$size}em'>
         </div>";
    goto end; 
  }
  $url= trim(str_replace("'",'"',$src[1])," '\"");
  // překlad na globální odkazy pro ty lokální (pro servant.php)
  $http= $FREE && preg_match("/^fileadmin/",$url) ? "https://www.setkani.org/" : '';
  $h= "<div style='max-height:{$size}em;overflow:hidden;float:left;margin-right:4px'>
         <img src='$http$url' style='width:{$size}em'>
       </div>";
//   $path= substr($url,0,12)=='./fileadmin/' ? $ezer_path_root.substr($url,1) : (
//          substr($url,0,10)=='fileadmin/' ?  "$ezer_path_root/$url" : '');
//   if ( $path ) {
//     $je= file_exists($path);
//                                                 display("$path - $je");
//     if ( $je ) {
//       // překlad na globální odkazy pro ty lokální (pro servant.php)
//       $http= $FREE ? "https://www.setkani.org/" : '';
//       $h= "<div style='max-height:{$size}em;overflow:hidden;float:left'>
//              <img src='$http$url' style='width:{$size}em'>
//            </div>";
//     }
//   }
video:
  // pokusíme se najít youtube default obrázek
  if ( !$h ) {
    $is= preg_match("~data-oembed-url=\"(?:http://youtu.be/|https?://www.youtube.com/watch\?v=)(.*)\"~iU",$html, $m);
//                                                 debug($m,$is);
    if ( $is ) {
      $h= "<div style='max-height:{$size}em;overflow:hidden;float:left'>
             <img src='https://img.youtube.com/vi/$m[1]/hqdefault.jpg' style='width:{$size}em'>
           </div>";
    }
  }
//   if ( $FREE ) $h= "is1=$is1, is2=$is2, http=$http ".$h;
end:  
  return $h;
}
# --------------------------------------------------------------------------------------- x shorting
# EPRIN
# zkrátí text na $n znaků s ohledem na html-entity jako je &nbsp;
function x_shorting ($text,$n=200) { //trace();
  $img= '';
  $stext= xi_shorting ($text,$img,$n);
  if (!$stext) $stext = "Popis není k dispozici. Obsah naleznete pod odkazem.";
  if ( $img ) {
    $stext= $img ? "<div>$img$stext ...</div>" : "$stext ...";
  }
  return $stext;
}
function xi_shorting ($text,&$img,$n=200) { //trace();
  // náhrada <h.> za <i>
  $text= str_replace('<',' <', $text);
  $text= preg_replace("/\<(\/|)h3>/si",' <$1i> ', $text);
  // hrubé zkrácení textu
  $stext= mb_substr(strip_tags($text,''),0,$n);
  // odstranění poslední (případně přeříznuté) html-entity
  $in= mb_strlen($stext);
  $ia= mb_strrpos($stext,'&');
  if ( $ia!==false )
    $stext= mb_substr($stext,0,$in-$ia<10 ? $ia : $in);
  $im= mb_strrpos($stext,' ');
  if ( $im!==false )
    $stext= mb_substr($stext,0,$im);
  $stext= closetags($stext);
  $stext= preg_replace("/\s+/iu",' ', $stext);
  $img= x_first_img($text,8);
  $stext.= " &hellip;";
  return $stext;
}
function closetags($html) {
  preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
  $openedtags = $result[1];
  preg_match_all('#</([a-z]+)>#iU', $html, $result);
  $closedtags = $result[1];
  $len_opened = count($openedtags);
  if (count($closedtags) == $len_opened) {
    return $html;
  }
  $openedtags = array_reverse($openedtags);
  for ($i=0; $i < $len_opened; $i++) {
    if (!in_array($openedtags[$i], $closedtags)) {
      $html .= '</'.$openedtags[$i].'>';
    } else {
      unset($closedtags[array_search($openedtags[$i], $closedtags)]);
    }
  }
  return $html;
}
/** =========================================================================================> FOTKY */
# --------------------------------------------------------------- reload fotky
// 1) fotky a popisy se berou z adresáře a přemístí do textu
// POZDEJI: ma žádost provést kontrolu úplnosti fotek 
//     (po zobrazení udělat test na počet fotek v text a v adresáři
//     pokud není shoda, uložit chybějící do text na konec včetně případných popisů)
// 2) převést soubory na mini+thumbs
// 4) dát zprávu $gn->gn_msg("$n fotografií bylo přidáno do pásu $uid");
function reload_fotky($uid) { global $gn;
  global $ezer_path_root;
  $path= "$ezer_path_root/fileadmin/photo/$uid";
  $pocet= 0;
  if ( !file_exists($path) ) {
    goto end;    
  }
  $x= simple_glob("$path/..*.*");
  $th= 80;
  $handle= @opendir($path); #$gn->gn_echo("gn_reloadFoto: handle=$handle<br>");
  $max= 9999; // ochrana proti zacyklení
  $text= '';
  while ($handle && $max && false !== ($file= readdir($handle))) {
    $max--;
    $fnp= explode('.', $file);  #$gn->gn_echo($file);
    if ( $fnp[0] && in_array(strtolower($fnp[1]), array("jpg","gif","png")) ) {
      $pocet++;
      $files[]= $file;
      $filens[]= $fnp[0];
    }
  }
  if ( $handle ) {
    closedir($handle);
  }
  else {
    $foto.= "Složka '$path' s fotografiemi není dostupná";
  }
  #$gn->gn_debug($files); $gn->gn_debug($filens);
  // utřiď podle jména
  sort($files); sort($filens);
  // projdi fotky
  for ($i= 0; $i<$pocet; $i++) {
    // udělej miniaturu, pokud neexistuje
    $file= $files[$i];
    $filen= $filens[$i];
    $src= "$path/$file";
    $thumb= "$path/..$file";
    if ( ! file_exists($thumb) ) {
      #$cmd= "convert -geometry {$th}x$th +contrast -sharpen 10 $src $thumb";
      #       //$cmd = ereg_replace('/','\\',$cmd);  #echo "$cmd<br>";  snad má být ve Windows potřeba - není
      #system(IMAGE_TRANSFORM_LIB_PATH.$cmd);
      $width= $height= $th;
      x_resample($src,$thumb,$width,$height);
    }
    // udělej variantu pro web, pokud neexistuje
    $small= "$path/.$file";
    if ( ! file_exists($small) ) {
      #$cmd= "convert -geometry 512x512 $src $small";
      #system(IMAGE_TRANSFORM_LIB_PATH.$cmd);
      $width= $height= 512;
      x_resample($src,$small,$width,$height);
    }
    // připoj popisek
    $txt= "$path/$filen.txt";
    $popisek= '';
    if ( file_exists($txt) ) {
      $txt_desc= fopen($txt,'r');
      $popisek= fread($txt_desc,64000);
      fclose($txt_desc);
    }
    $popisek= strtr($popisek,'"','\"');
    list($width, $height, $type, $attr) = getimagesize("$path/$file");
    $text.= $file . ',' . str_replace( ',','##44;',$popisek) . ',';
  }
  query ("UPDATE tx_gncase_part SET text=\"$text\" WHERE uid=$uid");
  #$gn->gn_echo("<hr>$text<hr>");
//  if ( $pocet ) {
//    $this->data['text']= $text;
//  }
end:  
  return $pocet;
}
# ----------------------------------------------------------------------------------==> . corr fotky
# uid určuje složku
function corr_fotky($uid) { trace();
  global $ezer_path_root;
  $tr= pdo_qry("SELECT uid,text FROM tx_gncase_part WHERE cid=$uid AND tags='F'");
  while ( $tr && (list($uid,$text)= pdo_fetch_row($tr)) ) {
    $path= "$ezer_path_root/fileadmin/photo/$uid";
    $msg= "<br>$uid: $path ";
    if ( file_exists($path) ) {
      $msg.= " exists ".(is_link($path) ? 'L' : '<s>L</s>' ).(is_dir($path) ? 'D' : '<s>D</s>' )
          .(is_readable($path) ? 'R' : '<s>R</s>' ).(is_writeable($path) ? 'W' : '<s>W</s>' );
      $x= simple_glob("$path/*.*");
      $msg.= " ".count($x)." files";
                                                  display("part.text=$text");
                                                  debug($x,"$uid");
    }
    else
      $msg.= " not exists ";
  }
  return $msg;
}
# --------------------------------------------------------------------------------==> . show fotky 2
# uid určuje složku
function show_fotky2($uid,$lst,$back_href='') { trace();
  global $CMS, $href0, $clear;
//                                                         display($lstx);
  if ( $CMS ) return show_fotky($uid,$lst,$back_href);
  $lstx= $lst;
  $h= '';
  $fs= explode(',',$lstx);
  $last= count($fs)-1;
  $ih= "<div class='fotorama'
    data-allowfullscreen='native'
    data-caption='true'
    data-width='800px'
    data-maxheight='600px'
    xdata-ratio='800/400'
    data-nav='thumbs'
    data-x-autoplay='true'
  >";
  for ($i= 0; $i<$last; $i+=2) {
    $mini= "fileadmin/photo/$uid/..$fs[$i]";
    $open= "fileadmin/photo/$uid/.$fs[$i]";
    $orig= "fileadmin/photo/$uid/$fs[$i]";
    if ( file_exists($mini) ) {
      $mini= str_replace(' ','%20',$mini);
      $title= '';
//       $title= " data-caption='něco fakt vtipnýho'";
      if ( $fs[$i+1] ) {
        $title= $fs[$i+1];
        $title= strtr($title,array('##44;'=>',',"'"=>'"','~'=>'-'));
        $title= " data-caption='$title'";
      }
      $i2= $i/2;
      $ih.= "<img src='$open' $title>";
//       $ih.= "<a href='$open'><img src='$mini' $title></a>";
    }
  }
  $ih.= "</div>";
  return $ih;
}
# ----------------------------------------------------------------------------------==> . show fotky
# uid určuje složku
function show_fotky($uid,$lst,$back_href) { trace();
  global $CMS, $href0, $clear;
  $lstx= $lst;
  popup("Prohlížení fotografií","$uid~$lstx",$back_href,'foto');
  $h= '';
  $fs= explode(',',$lstx);
                                                        debug($fs);
  $last= count($fs)-1;
  for ($i= 0; $i<$last; $i+=2) {
    $mini= "fileadmin/photo/$uid/..$fs[$i]";
    $open= "fileadmin/photo/$uid/.$fs[$i]";
    $orig= "fileadmin/photo/$uid/$fs[$i]";
                                                display($mini);
    if ( file_exists($mini) ) {
      $mini= str_replace(' ','%20',$mini);
      $title= $fs[$i];
      if ( $fs[$i+1] ) {
        $title= $fs[$i+1];
        $title= strtr($title,array('##44;'=>',',"'"=>'"','~'=>'-'));
      }
      $i2= $i/2;
      $onclick= $CMS ? '' : " onclick=\"foto_show(arguments[0],$i2);return false;\"";
      $h.= " <span data-foto-n='$i2' title='$title' $onclick
               class='foto foto_cms' style='background-image:url($mini)'></span>";
    }
  }
  return $h;
}
# ----------------------------------------------------------------------------------==> . load fotky
function load_fotky($uid) { trace();
  global $CMS, $href0, $clear;
  $x= (object)array();
  list($x->autor,$x->nadpis,$lst,$psano)=
    select('author,title,text,FROM_UNIXTIME(date)','tx_gncase_part',"uid=$uid");
  $x->fotky= "<span class='foto drop' data-foto-n='-1'></span><ul class='foto' id='foto'>";
  $x->psano= sql_date1($psano);
  $fs= explode(',',$lst);
  $last= count($fs)-1;
  for ($i= 0; $i<$last; $i+=2) {
    $mini= "fileadmin/photo/$uid/..$fs[$i]";
    if ( file_exists($mini) ) {
      $title= $fs[$i] ? "title='{$fs[$i]}'" : '';
      $tit= $fs[$i+1] ? "<div>{$fs[$i+1]}</div>" : '';
      $chk= "<input type='checkbox' onchange=\"this.parentNode.dataset.checked=this.checked;\" />";
      $menu= "oncontextmenu=\"Ezer.fce.contextmenu([
          ['smazat fotku',foto_delete],
          ['upravit popis',foto_note],
          ['vybrat jako titulní',foto_main]
        ],arguments[0]);return false;\"";
      $n= $i/2;
      $x->fotky.=
        " <li class='foto' data-foto-n='$n' $title $menu style='background-image:url($mini)'>"
        . "$chk$tit</li>";
    }
  }
  $x->fotky.= "</ul>";
  return $x;
}
# ------------------------------------------------------------------------------------- delete fotky
function delete_fotky($uid,$foto) {
  global $ezer_path_root, $ezer_root;
  // zrušení odkazu na fotku
  $text= select('text','tx_gncase_part',"uid=$uid");
  $fotky= explode(',',$text);
  while (1) {
    $n= array_search($foto,$fotky);
    if ( $n===false ) break;
    unset($fotky[$n]); unset($fotky[$n+1]);
  }
  $text= implode(',', $fotky);
  query("UPDATE tx_gncase_part SET text='$text' WHERE uid=$uid");
  // smazání fotky
  $path= "$ezer_path_root/fileadmin/photo/$uid";
  unlink("$path/$foto"); unlink("$path/.$foto"); unlink("$path/..$foto");
  return 1;
}
# --------------------------------------------------------------------------------------- main fotky
# fotku redaktor vybral jako titulní ve fotogalerii
function main_fotky($uid,$foto) { trace();
  //reuse 'abstract' column as photos do not use it...
  query("UPDATE setkani4.tx_gncase_part SET abstract='$foto' WHERE uid='$uid'");
  return 1;
}
# --------------------------------------------------------------------------------------- note fotky
function note_fotky($uid,$foto0,$note) {
  // načtení
  $text= select('text',"setkani4.tx_gncase_part","uid='$uid'");
  $f= array();
  $t= explode(',',$text);
  for ($i= 0; $i<count($t)-1; $i+=2) {
    $foto= $t[$i]; $desc= $t[$i+1];
    $f[$foto]= $desc;
  }
  // změna
  $f[$foto0]= $note;
  // zápis
  $text= '';
  foreach($f as $foto=>$desc) {
    $text.= "$foto,$desc,";
  }
  query("UPDATE setkani4.tx_gncase_part SET text='$text' WHERE uid='$uid'");
  return 1;
}
# --------------------------------------------------------------------------------==> . create fotky
# přidání fotek - pokud je definováno x.kapitola pak pod příslušné part - jinak na konec
function create_fotky($x) {
  $cid= $x->cid;
  $autor= pdo_real_escape_string($x->autor);
  $nadpis= pdo_real_escape_string($x->nadpis);
  $kapitola= $x->kapitola;
  query("INSERT INTO setkani4.tx_gncase_part (cid,kapitola,tags,author,title,text,date,tstamp)
         VALUES ($cid,'$kapitola','F','$autor','$nadpis','',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
  $uid= pdo_insert_id();
  return $uid;
}
# ----------------------------------------------------------------------------------==> . save fotky
function save_fotky($x,$perm) {
  $uid= $x->uid;
  $autor= pdo_real_escape_string($x->autor);
  $nadpis= pdo_real_escape_string($x->nadpis);
  $psano= sql_date1($x->psano,1);
  $text= select('text',"setkani4.tx_gncase_part","uid='$uid'");
  // přeskládání textu podle order
  $nt= array();
  $t= explode(',',$text);
//                                                         debug($t,$perm);
  $p= explode(',',$perm);
  for ($i= 0; $i<count($p); $i++) {
    $nt[$i*2]=   $t[$p[$i]*2];
    $nt[$i*2+1]= $t[$p[$i]*2+1];
  }
  $text2= implode(',',$nt);
//                                                         display($text);
//                                                         display($text2);
  $set_text= $text==$text2 ? '' : ",text='$text2'";
  // zápis
  query("UPDATE setkani4.tx_gncase_part
         SET author='$autor',title='$nadpis',date=UNIX_TIMESTAMP('$psano'),tstamp=UNIX_TIMESTAMP()
         $set_text WHERE uid='$uid'");
  return 1;
}
# ----------------------------------------------------------------------------------==> . sort fotky
# seřadí fotky podle jména souboru
function sort_fotky($uid) { trace();
  $text= select('text',"setkani4.tx_gncase_part","uid='$uid'");
  $f= array();
  $t= explode(',',$text);
                                                        debug($t);
  for ($i= 0; $i<count($t)-1; $i+=2) {
    $foto= $t[$i]; $desc= $t[$i+1];
                                                        display("$i,$foto,$desc");
    $f[$foto]= $desc;
  }
                                                        debug($f);
  ksort($f);
                                                        debug($f);
  $text= '';
  foreach($f as $foto=>$desc) {
    $text.= "$foto,$desc,";
  }
                                                        display($text);
  // zápis
  query("UPDATE setkani4.tx_gncase_part SET text='$text' WHERE uid='$uid'");
  return 1;
}
# ----------------------------------------------------------------------------------==> . move fotky
# přesune fotky s pořadími uvedenými v lst z part.uid=from do part.uid=to
function move_fotky($from,$to,$checked) { trace();
  global $ezer_path_root, $ezer_root;
  $path_from= "$ezer_path_root/fileadmin/photo/$from";
  $text_from= select('text',"setkani4.tx_gncase_part","uid='$from'");
  $path_to= "$ezer_path_root/fileadmin/photo/$to";
  $text_to= select('text',"setkani4.tx_gncase_part","uid='$to'");
  // zajisti cílovou složku
  if ( !is_dir($path_to) ) {
    $ok= mkdir($path_to,0777);
    if (!$ok) { fce_warning("POZOR nepodařilo se vytvořit složku pro fotografie ($path_to)"); goto end;}
  }
  // úprava seznamů a fotek
  $add= $sub= '';
  $t= explode(',',$text_from);
  $p= explode(',',$checked);
  for ($i= 0; $i<count($p); $i++) {
    if ( $p[$i]>0 ) {
      $pi= $p[$i]-1;
      $foto= $t[$pi*2]; $desc= $t[$pi*2+1];
      $add.= "$foto,$desc,";
      // přesun fotek mezi složkami
                                                        display("copy($path_from/$foto,$path_to/$foto)");
      foreach (array($foto,".$foto","..$foto") as $f) {
        if ( file_exists("$path_from/$f") ) {
          copy("$path_from/$f","$path_to/$f");
          unlink("$path_from/$f");
        }
      }
    }
    else {
      $pi= -$p[$i]-1;
      $foto= $t[$pi*2]; $desc= $t[$pi*2+1];
      $sub.= "$foto,$desc,";
    }
  }
  $text_from= $sub;
  $text_to= $text_to.$add;
                                                        display("from=$text_from");
                                                        display("to=$text_to");
  // zápis
  query("UPDATE setkani4.tx_gncase_part SET text='$text_from' WHERE uid='$from'");
  query("UPDATE setkani4.tx_gncase_part SET text='$text_to' WHERE uid='$to'");
end:
  return 1;
}
# ----------------------------------------------------------------------------------==> . upload url
# zapíše soubor zadaný urldo fileadmin/img/cid
function upload_url($url,$cid) { trace();
  global $ezer_path_root, $ezer_root;
  $ret= (object)array('err'=>'');
  // zajisti složku
  $path= "$ezer_path_root/fileadmin/img/$cid";
  if ( !is_dir($path) ) {
    $ok= mkdir($path,0777);
    if (!$ok) { $ret->err= "POZOR nepodařilo se vytvořit složku pro soubor ($path)"; goto end;}
  }
  // zjisti velikost a zda je dost místa
  $free= floor(disk_free_space("/")/(1024*1024));
  $headers= get_headers($url, 1);
                                                        debug($headers,$url);
  $size= $headers["Content-Length"];
  if ( is_array($size) ) $size= $size[count($size)-1];
  $size= ceil($size/(1024*1024));
                                                        display("volných $free MB, soubor má $size MB");
  if ( 5*$size > $free ) {
    $ret->err= "Na serveru je $free volných MB - to je dost málo (soubor má $size MB)"; goto end; }

  // zjisti a uprav jméno
  $disp= $headers["Content-Disposition"];
  $ok= preg_match("/attachment; filename=\"([^\"]+)\"/",$disp,$m);
                                                        debug($m,$ok);
  if (!$ok) { $ret->err= "POZOR soubor má nečekaný popis ($disp)"; goto end;}
  $file= utf2ascii($m[1],'.');
  $pathfile= "$path/$file";
                                                        display("file=$file");
  // soubor přepíšeme pokud existuje
  if ( file_exists($pathfile) ) unlink($pathfile);
  // zkopíruj do souboru
  if (!copy($url,$pathfile)) { $ret->err= "POZOR soubor $file se nepodařilo přečíst"; goto end; }
end:
  return $ret;
}
# ----------------------------------------------------------------------------------==> . upload zip
function upload_zip($url,$uid,$cid) { trace();
  global $ezer_path_root;
  $ret= (object)array('err'=>'');
  $free= floor(disk_free_space("/")/(1024*1024));
  $headers= get_headers($url, 1);
  $size= $headers["Content-Length"];
  if ( is_array($size) ) $size= $size[count($size)-1];
  $size= ceil($size/(1024*1024));
                                                        display("volných $free MB, zip má $size MB");
  if ( 5*$size > $free ) {
    $ret->err= "Na serveru je $free volných MB - to je dost málo (soubor má $size MB)"; goto end; }
  $path= "$ezer_path_root/fileadmin/photo/$uid";

  // zkopíruj do dočasného souboru
  if ( file_exists("tmp_file.zip") ) unlink("tmp_file.zip");
  $tmp= "tmp_file.zip";
  if (!@copy($url,$tmp)) { $ret->err= "POZOR archiv se nepodařilo přečíst"; goto end; }
  // zajisti složku
  if ( !is_dir($path) ) {
    $ok= mkdir($path,0777);
    if (!$ok) { $ret->err= "POZOR nepodařilo se vytvořit složku pro fotografie ($path)"; goto end;}
  }
  // otevři archiv
  $z= new ZipArchive;
  $ok= $z->open($tmp);
  if ( $ok===true ) {
                                                        display("files={$z->numFiles}");
    for ($i=0; $i<$z->numFiles;$i++) {
      $f= $z->statIndex($i);
      $file0= $f['name'];
      $file= utf2ascii($file0,'.');
      if ( $file0!=$file ) {
        $z->renameName($file0,$file);
      }
      $z->extractTo($path,array($file));
      list($width, $height, $type, $attr)= getimagesize("$path/$file");
      // file na HD 1080
      if ( $width>1920 || $height>1080 ) {
        $w= 1920; $h= 1080;
        $ok= x_resample("$path/$file","$path/$file",$w,$h) ? 'ok' : 'ko';
      }
      // .file na HD 720
      if ( $width>1280 || $height>720 ) {
        $w= 1280; $h= 720;
        $ok= x_resample("$path/$file","$path/.$file",$w,$h) ? 'ok' : 'ko';
      }
      else
        copy("$path/$file","$path/.$file");
      // doplnění thumbs ..$file
      $w= $h= 80;
      $ok= x_resample("$path/.$file","$path/..$file",$w,$h) ? 'ok' : 'ko';
      // přidání názvu fotky do záznamu v tabulce
      query("UPDATE tx_gncase_part SET text=CONCAT('$file,,',text) WHERE uid=$uid");
    }
    $z->close();
  }
  else { $ret->err= "POZOR archiv se nepodařilo otevřít (chyba:$ok)"; goto end;}
end:
  // uvolni prostor
  if ( file_exists("tmp_file.zip") ) unlink("tmp_file.zip");
  return $ret;
}
# --------------------------------------------------------------------------------==> . upload fotky
# přidá fotografii do seznamu (rodina|osoba) podle ID na konec a vrátí nové názvy
function upload_fotky($fileinfo,$uid,$cid) { trace();
  global $ezer_path_root, $ezer_root;
  $name= '';          // tiché oznámení chyby
  $path= "$ezer_path_root/fileadmin/photo/$uid";
  $parts= pathinfo($fileinfo->name);
  // zajisti složku
  if ( !is_dir($path) ) {
    $ok= mkdir($path,0777);
    if (!$ok) {fce_error("POZOR nepodařilo se vytvořit složku pro fotografie ($path)!"); goto end;}
  }
  $file= utf2ascii($parts['filename']).'.'.$parts['extension'];
//                                                 debug($fileinfo,$path);
  $data= $fileinfo->text;
  // test korektnosti fotky
  if ( $type=="application/x-zip-compressed" ) {
    // ZIP archiv
    $prefix= "application/x-zip-compressed;base64,";
    $data= base64_decode(substr("$data==",strlen($prefix)));
    fce_error("vkládání fotek přes ZIP ještě není hotové");
  }
  elseif ( substr($data,0,23)=="data:image/jpeg;base64," ) {
    // uložení fotky na disk
    $data= base64_decode(substr("$data==",23));
    $bytes= file_put_contents("$path/$file",$data);
    // přidání názvu fotky do záznamu v tabulce
    query("UPDATE tx_gncase_part SET text=CONCAT('$file,,',text) WHERE uid=$uid");
    // doplnění thumbs ..$file
    $w= $h= 80;
    $ok= x_resample("$path/$file","$path/..$file",$w,$h) ? 'ok' : 'ko';
    // ZMENŠENINA .file
    if ( !is_file("$path/.$file") ) // pokud zmenšenina neexistuje, vynuť její vytvoření
      $width0= $height0= -1;
    else // pokud existuje zmenšenina, podívej se na její rozměry
      list($width0, $height0, $type0, $attr0)= getimagesize("$path/.$file");
    if ( $width!=$width0 || $height!=$height0 ) {
      // je požadována změna rozměrů, transformuj obrázek
      $w= $width; $h= $height;
      $ok= x_resample("$path/$file","$path/.$file",$w,$h) ? 'ok' : 'ko';
    }
  }
end:
  return $name;
}
# -------------------------------------- x_resample
// změna velikosti obrázku typu gif, jpg nebo png (na jiných zde není realizována)
//   $source, $dest -- cesta k souboru, ktery chcete zmensit  a cesta, kam zmenseny soubor ulozit
//   $maxWidth, $maxHeight  -- maximalni sirka a vyska změněného obrazku
//     hodnota 0 znamena, ze sirka resp. vyska vysledku muze byt libovolna
//     hodnoty 0,0 vedou na kopii obrázku
//     $copy_bigger==1 vede na kopii (např.miniatury) místo na zvětšení
//   výsledek 0 - operace selhala
function x_resample($source, $dest, &$width, &$height,$copy_bigger=0) {
  global $gn;
  $maxWidth= $width;
  $maxHeight= $height;
  $ok= 1;
//                               display("... RESAMPLE($source, $dest, &$width, &$height,$copy_bigger)<br>");
  // zjistime puvodni velikost obrazku a jeho typ: 1 = GIF, 2 = JPG, 3 = PNG
  list($origWidth, $origHeight, $type)=@ getimagesize($source);
  if ( !$type ) $ok= 0;
  if ( $ok ) {
    if ( !$maxWidth ) $maxWidth= $origWidth;
    if ( !$maxHeight ) $maxHeight= $origHeight;
    // nyni vypocitam pomer změny
    $pw= $maxWidth / $origWidth;
    $ph= $maxHeight / $origHeight;
    $p= max($pw, $ph);
    // vypocitame vysku a sirku změněného obrazku - vrátíme ji do výstupních parametrů
    $newWidth = (int)($origWidth * $p);
    $newHeight = (int)($origHeight * $p);
    $width= $newWidth;
    $height= $newHeight;
    if ( ($pw == 1 and $ph == 1) or ($copy_bigger and $p>1) ) {
      // jenom zkopírujeme
      copy($source,$dest);
    }
    else {
      // zjistíme velikost cíle - abychom nedělali zbytečnou práci
      $destWidth= $destHeight= -1; $ok= 2; // ok=2 -- nic se nedělalo
      if ( file_exists($dest) ) list($destWidth, $destHeight)= getimagesize($dest);
      if ( $destWidth!=$newWidth || $destHeight!=$newHeight ) {
        // vytvorime novy obrazek pozadovane vysky a sirky
        #if ( $CONST['GraphicTool']['name']=='GD' ) { // GD Library
          // karel: nezapomeň ještě taky, že když zmenšuješ GIF s průhlednou barvou, musíš touto barvou nejprve vyplnit cílový obrázek a nastavit ji jako průhlednou
          $image_p= ImageCreateTrueColor($newWidth, $newHeight);
          // otevreme puvodni obrazek se souboru
          switch ($type) {
          case 1: $image= ImageCreateFromGif($source); break;
          case 2: $image= ImageCreateFromJpeg($source); break;
          case 3: $image= ImageCreateFromPng($source); break;
          }
          // okopirujeme zmenseny puvodni obrazek do noveho
          if ( $maxWidth || $maxHeight )
            ImageCopyResampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
          else
            $image_p= $image;
          // ulozime
          $ok= 0;
          switch ($type) {
          case 1: /*ImageColorTransparent($image_p);*/ $ok= ImageGif($image_p, $dest);  break;
          case 2: $ok= ImageJpeg($image_p, $dest);  break;
          case 3: $ok= ImagePng($image_p, $dest);  break;
          }
        #}
        #elseif ( $CONST['GraphicTool']['name']=='ImageMagic' ) { // ImageMagic
        #  // proveď externí program
        #  $mode= $destWidth<100 ? " +contrast -sharpen 10 " : ''; // malým obrázkům přidej kontrast a zaostři je
        #  $cmd= "convert -geometry {$newWidth}x$newHeight $mode $source $dest";
        #  $ok= system("{$CONST['GraphicTool']['binary']}$cmd") ? 1 : 0;
        #}
      }
    }
  }
  return $ok;
}
/** =========================================================================================> TABLE */
# obsluha přihlašovací tabulky bez BE
# -------------------------------------------------------------------------------------==> . tabulka
# uid určuje složku
function tabulka($cid,$day) { trace();
  global $CMS, $href0, $clear, $fe_user, $fe_host;
  $skup= $tab= array();  // tab: [skup][poradi] poradi=0 => max, poradi>0 => jméno
  $maximum= 0;
  $tr= pdo_qry("SELECT skupina,jmeno,poradi FROM setkani4.gnucast
    WHERE datum='$day' ORDER BY skupina,poradi,gnucast");
  while ( $tr && (list($skupina,$jmeno,$poradi)= pdo_fetch_row($tr)) ) {
    if ( $skupina=='maximum' )    { $maximum= max($maximum,$poradi); continue; }
    if ( !isset($tab[$skupina]) ) { $skup[]= $skupina; $tab[$skupina]= array(0); }
    if ( $jmeno=='max' )          { $tab[$skupina][0]= $poradi; $maximum= max($maximum,$poradi); continue; }
    $tab[$skupina][]= "$jmeno";
  }
//                                                        debug($skup,"maximum=$maximum");
//                                                         debug($tab);
  $dnes= date('Y-m-d');
  $h= count($tab)==1
    ? "<h3>Přihlašovací tabulka</h3>
       Pokud se chceš zúčastnit tohoto setkání, klikni na <big><b>+</b></big> za názvem místa 
       (případně vyplň krátký test) a potom přidej svoje jméno a příjmení ukončené Enter. 
       Pokud bys s tím měl problémy, pošli mi SMS na 603150565 se svým jménem
       (ale napřed to zkus tady a teď). Pokud potřebuješ svoji účast zrušit, 
       napiš znovu svoje jméno jako poprvé, bude vyjmuto."
    : "<h3>Přihlašovací tabulka</h3>
       Na tomto setkání se sejdeme v jednom čase na níže uvedených místech. Pokud se chceš zúčastnit,
       klikni na <big><b>+</b></big> za názvem skupiny (případně vyplň krátký test) a potom přidej svoje jméno a příjmení
       ukončené Enter. Pokud bys s tím měl problémy, pošli SMS na 603150565 se svým jménem a názvem skupiny
       (ale napřed to zkus tady a teď). Pokud se chceš přeřadit do jiné skupiny, napiš svoje jméno do ní (z té původní se
       vyjme samo).<br>";
  $h.= "<br><div class='skupiny_container'><table id='skupiny' cellspacing='0' cellpadding='0'><tr>";
  $add= $event= '';
  foreach ($skup as $s) {
    if ( $day>=$dnes )
      $event= $fe_host || $fe_user
        ? "onclick=\"table_add1(arguments[0],'$day','$s','$cid');\""
        : "onclick=\"table_test(arguments[0]);return false;\"";
    $style= "style='box-shadow:3px 2px 6px gray;float:right'";
    $class= "class='jump'";
    if ( $day>=$dnes )
      $add= "<a $event $class $style>+</a>";
    $h.= "<th>$s$add</th>";
  }
  $h.= "</tr><tr>";
  foreach ($skup as $s) {
    if ( $day>=$dnes ) {
      $event= "onsubmit=\"table_add2(arguments[0],'$day','$s','$cid');return false;\"";
      $h.= "<td><form $event>
              <input type='text' size='1' maxlength='100' id='table-$s' style='display:none'>
            </form></td>";
    }
  }
  $h.= "</tr>";
  for ($i= 1; $i<=$maximum; $i++) {
    $h.= "<tr>";
    foreach ($skup as $s) {
      if ( !$tab[$s][0] ) $tab[$s][0]= $maximum;
      $jm= isset($tab[$s][$i]) ? $tab[$s][$i] : '';
      if ( !$fe_user && !$fe_host ) {
        list($jm)= explode(' ',trim($jm));
      }
      $clr= $i<=$tab[$s][0] ? '#e6a98f' : 'transparent';
      $h.= "<td style='background-color:$clr'>$jm</td>";
    }
    $h.= "</tr>";
  }
  $h.= "</table></div><br>";
  return $h;
}
/** ======================================================================================> CKEDITOR */
# funkce na serveru přes AJAX pro spolupráci s CKEDITORem
function cke_server($x) {
  global $y;
  global $CMS;
  $CMS= count($_SESSION['cms']);
  global $trace, $totrace;
  $totrace= $x->totrace;
  $err= '';
  $cke= $x->cke;
  $y= $x;
  $y->ok= 0;
  switch ($cke) {
  case 'img_rotate':
                                                display("cke_server({cke:$cke,src:$x->src,deg:$x->deg})");
    $deg= $x->deg;
    $src= $x->src;
    $part= pathinfo($src);
    $ext= strtolower($part['extension']);
    if ( substr($part['dirname'],0,4)=="http" ) { $err= "nelze otočit obrázky z jiného webu"; goto end; }
    $y->src= "{$part['dirname']}/{$part['filename']}_$deg.$ext";
                                                        debug($part,"$ext $src ==> $y->src");
    switch ($ext) {
    case 'jpg':
      $img= @imagecreatefromjpeg($src);
      if ( !$img ) { $err= "CKEDIT/EZER $x->src nema format JPEG"; goto end; }
      $img= imagerotate($img,$deg,0);
      $xxx= imagejpeg($img,$y->src); display($xxx);
      if ( !imagejpeg($img,$y->src) ) { $err= "CKEDIT/EZER $y->src nelze ulozit"; goto end; }
      $y->ok= 1;
      break;
    case 'png':
      $img= @imagecreatefrompng($src);
      if ( !$img ) { $err= "CKEDIT/EZER $x->src nema format PNG"; goto end; }
      $img= imagerotate($img,$deg,0);
      if ( !imagepng($img,$y->src) ) { $err= "CKEDIT/EZER $y->src nelze ulozit"; goto end; }
      $y->ok= 1;
      break;
    case 'gif':
      $img= @imagecreatefromgif($src);
      if ( !$img ) { $err= "CKEDIT/EZER $x->src nema format GIF"; goto end; }
      $img= imagerotate($img,$deg,0);
      if ( !imagegif($img,$y->src) ) { $err= "CKEDIT/EZER $y->src nelze ulozit"; goto end; }
      $y->ok= 1;
      break;
    default:
      $err= "CKEDIT/EZER: neznamy typ souboru '$src'";
    }
    break;
  default:
    $err= "CKEDIT/EZER: neznama operace '$cke'"; goto end;
    break;
  }
end:
  if ( $err ) {
    $y->warning= $err; $y->ok= 0;
                                                        display("cke_server err = $err");
  }
                                                        display("cke_server = $y->ok");
  $y->trace= $trace;
}
/** ==========================================================================================> MAPA */

# ------------------------------------------------------------------------------------ mapa2 skupiny
# přečtení seznamu skupin
function mapa2_skupiny() {  trace();
  global $totrace, $trace;
//   $totrace='M';
  $goo= "https://docs.google.com/spreadsheets/d";
  $key= "1mp-xXrF1I0PAAXexDH5FA-n5L71r5y0Qsg75cU82X-4";         // Seznam skupin - kontakty
  $prefix= "google.visualization.Query.setResponse(";           // přefix json objektu
  $sheet= "List 1";
  $x= file_get_contents("$goo/$key/gviz/tq?tqx=out:json"); //&sheet=$sheet");
//                                         display($x);
  $xi= strpos($x,$prefix);
  $xl= strlen($prefix);
//                                         display("xi=$xi,$xl");
  $x= substr(substr($x,$xi+$xl),0,-2);
//                                         display($x);
  $tab= json_decode($x)->table;
//                                         debug($tab,$sheet);
  // projdeme získaná data
  $psc= $note= $clmns= $emails= array();
  $n= 0;
  $msg= '';
  if ( $tab ) {
    foreach ($tab->rows as $irow=>$crow) {
      $row= $crow->c;
      if ( $row[0]->v=="ZVLÁŠTNÍ SKUPINY:" ) break;     // konec seznamu
      $skupina= $row[0]->v;
      $p= $row[1]->v;
      $p= strtr($p,array(' '=>'','?'=>'',"\n"=>''));
      $aktual= $row[2]->v;
      if ( preg_match("/(\d+),(\d+),(\d+)/",$x,$m) )
        $aktual= "$m[3].$m[2].$m[1]";
      $kontakt= $row[3]->v;
      $email= $row[4]->v;
      $pozn= $row[5]->v;
//                                                 if ( $irow==2 ) { goto end; }
      if ( strlen($p)==5 ) {
        $psc[$p]= $pozn;
        $note[$p]= $skupina;
        $n++;
        // emailové adresy do pole $emails
        $email= trim($email);
        $email= strtr($email,array(' '=>',',';'=>','));
        $email= strtr($email,array(',,'=>','));
        $emails[$p]= $email;
        // podrobnosti do pole $clmns
        $clmns[$p]=
          "<h3>$skupina</h3><p>Kontakt:$kontakt, <b>$email</b></p>"
        . "<p>$pozn</p><p style='text-align:right'><i>aktualizováno: $aktual</i></p>";
      }
      else {
//                                         debug($crow,"problém");
        $msg.= " $p";
      }
    }
  }
  // konec
end:
  $ret= mapa2_psc($psc,$note,1);
  $msg= $msg ? "<br><br>Problém nastal pro PSČ: $msg" : '';
  $msg.= $ret->err ? "<br><br>$ret->err" : '';
  $ret->err= '';
  $ret->clmns= $clmns;
  $ret->emails= $emails;
  $ret->msg= "Je zobrazeno $n skupin z tabulky <b>Seznam skupin - kontakty</b>$msg|{$ret->msg}";
  return $ret;
}
# -----------------------------------------------------------------------------==> .. mapa2 psc_list
# vrátí strukturu pro gmap
function mapa2_psc_list($psc_lst) {
  $psc= $obec= array();
  foreach (explode(',',$psc_lst) as $p) {
    $psc[$p]= $p;
  }
  return mapa2_psc($psc,$obec);
}
# ----------------------------------------------------------------------------------==> .. mapa2 psc
# vrátí strukturu pro gmap
function mapa2_psc($psc,$obec,$psc_as_id=0) {
  global $totrace, $trace;
//                                                 debug($psc,"mapa2_psc");
  // k PSČ zjistíme LAN,LNG
  $ret= (object)array('mark'=>'','n'=>0);
  $marks= $err= '';
  $mis_psc= array();
  $err_psc= array();
  $n= 0; $del= '';
  foreach ($psc as $p=>$tit) {
    $p= trim($p);
    if ( preg_match('/\d\d\d\d\d/',$p) ) {
      $qs= "SELECT psc,lat,lng FROM psc_axy WHERE psc='$p'";
      $rs= pdo_qry($qs);
      if ( $rs && ($s= pdo_fetch_object($rs)) ) {
        $n++;
        $o= isset($obec[$p]) ? $obec[$p] : $p;
        $title= str_replace(',','',"$o:$tit");
        $id= $psc_as_id ? $p : $n;
        $marks.= "{$del}$id,{$s->lat},{$s->lng},$title"; $del= ';';
      }
      else {
        $err_psc[$p].= " $p";
      }
    }
    else {
      $mis_psc[$p].= " $p";
    }
  }
  // zjištění chyb
  if ( count($err_psc) || count($mis_psc) ) {
    if ( ($ne= count($mis_psc)) ) {
      $err= "$ne PSČ chybí nebo má špatný formát. Týká se to: ".implode(' a ',$mis_psc);
    }
    if ( ($ne= count($err_psc)) ) {
      $err.= "<br>$ne PSČ se nepovedlo lokalizovat. Týká se to: ".implode(' a ',$err_psc);
    }
  }
  $msg= "($trace)";
  $ret= (object)array('mark'=>$marks,'n'=>$n,'err'=>$err,'msg'=>$msg);
//                                         debug(explode(';',$ret->mark),"mapa_akce");
  return $ret;
}

# ------------------------------------------------------------------------------------ mapa2 skupiny
# přečtení seznamu skupin z tabulky
# https://docs.google.com/spreadsheets/d/1mp-xXrF1I0PAAXexDH5FA-n5L71r5y0Qsg75cU82X-4/edit#gid=0
# https://docs.google.com/spreadsheets/d/1mp-xXrF1I0PAAXexDH5FA-n5L71r5y0Qsg75cU82X-4/gviz/tq?tqx=out:json
#   0 - skupina
#   1 - psč[,město,ulice]
#   2 - aktualizace
#   3 - kontakt
#   4 - email
#   5 - poznámka
#   6 - uzavřená skupina
function mapa2_skupiny2() {  trace();
  global $totrace, $trace;
//   $totrace='M';
  $goo= "https://docs.google.com/spreadsheets/d";
  $key= "1mp-xXrF1I0PAAXexDH5FA-n5L71r5y0Qsg75cU82X-4";         // Seznam skupin - kontakty
  $prefix= "google.visualization.Query.setResponse(";           // přefix json objektu
  $sheet= "List 1";
  $x= file_get_contents("$goo/$key/gviz/tq?tqx=out:json"); //&sheet=$sheet");
//                                         display($x);
  $xi= strpos($x,$prefix);
  $xl= strlen($prefix);
//                                         display("xi=$xi,$xl");
  $x= substr(substr($x,$xi+$xl),0,-2);
//                                         display($x);
  $tab= json_decode($x)->table;
//                                         debug($tab,$sheet);
  // projdeme získaná data
  $adrs= $geos= $notes= $clmns= $emails= array();
  $n= 0;
  $msg= '';
  if ( $tab ) {
    foreach ($tab->rows as $irow=>$crow) {
      $row= $crow->c;
      if ( $row[0]->v=="ZVLÁŠTNÍ SKUPINY:" ) break;     // konec seznamu
      $group= $row[0]->v;
      $adr= $row[1]->v;
      $adr= strtr($adr,array(';'=>',','?'=>'',"\n"=>''));
      $aktual= $row[2]->v;
      if ( preg_match("/(\d+),(\d+),(\d+)/",$x,$m) )
        $aktual= "$m[3].$m[2].$m[1]";
      $kontakt= $row[3]->v;
      // emailové adresy do pole $emails
      $email= trim($row[4]->v);
      $email= strtr($email,array(' '=>',',';'=>','));
      $email= strtr($email,array(',,'=>','));
      $note= $row[5]->v;
        // podrobnosti do pole $clmns
      $clmn= "<h3>$group</h3><p>Kontakt:$kontakt, <b>$email</b></p>"
           . "<p>$note</p><p style='text-align:right'><i>aktualizováno: $aktual</i></p>";
//                                                 if ( $irow==0 ) { $adr="110 00,Praha,Voršilská 2085/3"; }
//                                                 if ( $irow==1 ) { goto end; }
      $adrs[$irow]= "CZ,$adr";               // CZ,psč[,město,ulice]
      $notes[$irow]= $note;
      $groups[$irow]= $group;
      $emails[$irow]= $email;
      $clmns[$irow]= $clmn;
      $n++;
    }
  }
  // konec
end:
  $ret= mapa2_adr($adrs,$groups,$notes);
  $msg= $msg ? "<br><br>Problem nastal pro PSC: $msg" : '';
  $msg.= $ret->err ? "<br><br>$ret->err" : '';
  $ret->err= '';
  $ret->clmns= $clmns;
  $ret->emails= $emails;
  $ret->msg= "Je zobrazeno $n skupin z tabulky <b>Seznam skupin - kontakty</b>$msg|{$ret->msg}";
  return $ret;
}
# ----------------------------------------------------------------------------------==> .. mapa2 adr
# vrátí strukturu pro gmap
function mapa2_adr($adrs,$groups,$notes) {
  global $totrace, $trace;
  // k PSČ zjistíme LAN,LNG
  $ret= (object)array('mark'=>'','n'=>0);
  $marks= $err= '';
  $err_psc= array();
  $n= $ndb1= $ndb2= $ngeo= 0; $del= '';
  foreach ($adrs as $irow=>$adr) {
    $lat= $lng= 0;
    $psc= substr(strtr(trim(substr($adr,0,10)),array(' '=>'')),0,5);
    // nejprve zjistíme, zda jsme již zjistili polohu
    list($lat,$lng)= select("lat,lng","ezer_db2._geocode","adr='$adr'","ezer_db2");
    if ( !$lat ) {
      // zkusíme online geolokaci
      $g= geocode_google($adr);
      if ( $g->err ) {
        $err.= "geolokace:{$g->err}";
        // nouzově zjistíme aspoň polohu PSČ, je-li v tabulce
        list($lat,$lng)= select("lat,lng","psc_axy","psc='$psc'","ezer_db2");
        $ndb2++;
      }
      else {
        // zapíšeme do tabulky
        $lat= $g->lat;
        $lng= $g->lng;
        query("INSERT INTO ezer_db2._geocode (adr,lat,lng) VALUES ('$adr','$lat','$lng')","ezer_db2");
        $ngeo++;
      }
    }
    else {
      $ndb1++;
    }
    if ( $lat ) {
      $n++;
//       $title= "<b>{$groups[$irow]}</b><br>$notes[$irow]"; ---- nejde, je pak ošklivý title
      $title= "{$groups[$irow]}: $notes[$irow]";
      $title= str_replace(',','‚',$title); // &sbquo;
      // title se objeví při mouseover a jako nápis po kliknutí na značku
      $marks.= "{$del}$irow,{$lat},{$lng},$title"; $del= ';';
    }
    else {
      $err_psc[$p].= " $p";
    }
  }
  // zjištění chyb
  if (( $ne= count($err_psc) )) {
    $err.= "<br>$ne PSC se nepovedlo lokalizovat. Tyka se to: ".implode(' a ',$err_psc);
  }
  $msg= "(db1=$ndb1,geo=$ngeo,db2=$ndb2,$trace)";
  $ret= (object)array('mark'=>$marks,'n'=>$n,'err'=>$err,'msg'=>$msg);
  return $ret;
}
# -----------------------------------------------------------------------------==> .. geocode google
# vrátí strukturu pro gmap
function geocode_google($adr) {
  global $api_key_gmaps;
  $lat= $lng= $err= '';
  $adr= urlencode($adr);
  $url= "https://maps.googleapis.com/maps/api/geocode/json?key=$api_key_gmaps&address=$adr";
//   $err= "[$url]";
  $json= @file_get_contents($url);
  if ( $json ) { //&& substr($json,0,1)=='{' ) {
    $y= json_decode($json);
    if ( $y->status=="OK" ) {
      $lat= $y->results[0]->geometry->location->lat;
      $lng= $y->results[0]->geometry->location->lng;
    }
    else {
      $err.= $y->status;
    }
  }
  else {
    $err.= "adresa nemá správný tvar";
  }
  $ret= (object)array('lat'=>$lat,'lng'=>$lng,'err'=>$err);
  return $ret;
}

/** ========================================================================================> SERVER */
# funkce na serveru přes AJAX
function server($x) {  trace();
  global $y, $trace, $index;
//   $y= (object)array();
  switch ( $x->cmd ) {
    
  // ======================================================================= servant
    
  case 'knihy': // ---------------------------------------------- servant:knihy
    global $usergroups, $totrace, $href0, $page_mref, $ezer_server;
    $y->trace= '';
    $href0= "$index?page=foto&";
    require_once("template.php");
    ezer_connect('setkani4');
    error_reporting(E_ALL ^ E_NOTICE);
//    $fileadmin= array(
//        "http://setkani.bean:8080/fileadmin",
//        "https://www.setkani.org/fileadmin",
//        "http://setkani4.doma/fileadmin",
//        "http://setkani4.bean:8080/fileadmin"
//      )[$ezer_server];
    $usergroups= $x->groups;
    $page_mref= $x->page;
    list($chlapi,$uid)= explode('!',$x->chlapi);
    $clanky= clanky(0,$uid,0,$chlapi,$x->back);
    $y->obsah= $clanky;
    $y->ip= ip_get();
    $y->id= $x->id;
    break;
  
  case 'kapitoly': // ---------------------------------------------- servant:kapitoly
    // vrátí seznam kapitol knihy dané pid jedné kapitoly
    $y->trace= '';
    ezer_connect('setkani4');
    $cid= select("cid","tx_gncase_part","uid=$x->pid");
    $pids= select1("GROUP_CONCAT(CONCAT(tags,uid) ORDER BY kapitola,IF(tags='F',3,0),uid)",
        "tx_gncase_part",
        "cid=$cid AND deleted=0 AND hidden=0 AND tags IN ('A','B','C','D','E','F') GROUP BY cid");
    $y->pids= $pids;
    break;
  
  case 'akce':   // ---------------------------------------------- servant:akce
    global $usergroups, $totrace, $href0, $page_mref;
    $y->trace= '';
    require_once("template.php");
    ezer_connect('setkani4');
    error_reporting(E_ALL ^ E_NOTICE);
    $usergroups= $x->groups;
    $back= $x->back;
    list($chlapi,$uid)= explode('!',$x->chlapi);
    $akce= chlapi_prehled($chlapi,$uid,$back);
    // úprava pro chlapi.online
    $y->obsah= $akce;
    $y->ip= ip_get();
    $y->id= $x->id;
    break;
  
  case 'clanky': // ---------------------------------------------- servant:clanky
    global $usergroups, $totrace, $href0, $page_mref;
    $y->trace= '';
    $href0= "$index?page=foto&";
    require_once("template.php");
    ezer_connect('setkani4');
    error_reporting(E_ALL ^ E_NOTICE);
    $usergroups= $x->groups;
    $page_mref= $x->page;
    list($chlapi,$uid)= explode('!',$x->chlapi);
    $clanky= clanky(0,$uid,0,$chlapi,$x->back);
    $y->obsah= $clanky;
    $y->ip= ip_get();
    $y->id= $x->id;
    break;
  
  case 'kniha': // ---------------------------------------------- kniha / servant
    global $usergroups, $totrace, $href0, $page_mref;
    $y->trace= '';
    $href0= "$index?page=foto&";
    require_once("template.php");
    ezer_connect('setkani4');
    error_reporting(E_ALL ^ E_NOTICE);
    $usergroups= $x->groups;
    $page_mref= $x->page;
    $kniha= knihy('chlapi.online',"$x->cid",$x->kapitola);
    // úprava pro chlapi.online
    $kniha= str_replace('class="galerie"','',$kniha);
    $y->obsah= preg_replace("/(src|href)=(['\"])fileadmin/","$1=$2$fileadmin",$kniha);
    $y->ip= ip_get();
    $y->id= $x->id;
    break;
  
  case 'abstrakt': // ---------------------------------------------- abstrakt / servant
    global $usergroups, $totrace, $trace, $href0;
    $totrace= 'M';
    ezer_connect('setkani4');
    $y->abstrakt= array();
    $cr= pdo_qry("
      SELECT p.title, text
      FROM tx_gncase AS c
      JOIN tx_gncase_part AS p ON p.cid=c.uid
      WHERE !c.deleted AND !c.hidden AND c.uid IN ({$x->ids})
      ORDER BY c.sorting DESC,c.uid DESC");
    while ( $cr && (list($title,$text)= pdo_fetch_row($cr)) ) {
      $text= x_shorting($text);
      // překlad na globální odkazy
      $text= preg_replace("/(src|href)=(['\"])fileadmin/","$1=$2$fileadmin",$text);
      $y->abstrakt[]= (object)array('nadpis'=>$title,'abstrakt'=>$text);
    }
    $y->trace= $trace;
    break;
  
  case 'clanek': // ---------------------------------------------- clanek / servant
    global $usergroups, $totrace, $href0, $FREE;
    $totrace= '';
    ezer_connect('setkani4');
    list($y->autor,$y->nadpis,$y->obsah,$psano,$y->crdate,$y->tstamp,$y->od,$y->do,$y->fe_groups)= 
        select(
          "p.author,p.title,p.text,FROM_UNIXTIME(p.date),p.date,p.tstamp,
              DATE(FROM_UNIXTIME(c.fromday)),DATE(FROM_UNIXTIME(c.untilday)),c.fe_groups",
          "setkani4.tx_gncase_part AS p LEFT JOIN setkani4.tx_gncase AS c ON c.uid=p.cid",
          "p.uid='{$x->pid}'");
    $y->psano= sql_date1($psano);
    $y->trace= $trace;
    // překlad na globální odkazy
    if ( $FREE )
      $y->obsah= preg_replace("/(src|href)=(['\"])fileadmin/",
          "$1=$2$fileadmin",$y->obsah);
    break;
  
  case 'galerie': // ---------------------------------------------- galerie / servant
    global $usergroups, $totrace, $href0;
    $totrace= '';
    list($x->autor,$x->nadpis,$obsah,$psano,$crdate,$tstamp)= select(
      "author,title,text,FROM_UNIXTIME(date),date,tstamp",
      "setkani4.tx_gncase_part",
      "uid='{$x->pid}'",'setkani');
    $y->psano= sql_date1($psano);
    $y->trace= $trace;
    // překlad na globální odkazy
    $lst= show_fotky2($x->pid,$obsah);
    $y->obsah= preg_replace("/(src|href)=(['\"])fileadmin/","$1=$2$fileadmin",$lst);
    break;
  
  case 'kalendar': // ---------------------------------------------- roky / kalendar
    # vrátí seznam naplánovaných akcí
    # tzn. akce.web_kalendar=1 příp. web_anotace
    global $usergroups, $totrace, $trace, $href0;
    $totrace= 'uM';
    error_reporting(E_ALL ^ E_NOTICE);
    $y->akce= array();
                                                  display("servant:kalendar");
    ezer_connect('ezer_db2');
    $tr= pdo_qry("
      SELECT access,nazev,misto,datum_od,datum_do,web_anotace,web_url,web_obsazeno
      FROM ezer_db2.akce
      WHERE web_kalendar=1 AND datum_od>NOW()
      ORDER BY datum_od
    ");
    while ($tr && list($org,$nazev,$misto,$od,$do,$anotace,$url,$obsazeno)= pdo_fetch_row($tr)) {
      $oddo= datum_oddo($od,$do);
      $akce= array('od'=>$od, 'org'=>$org, 'nazev'=>$nazev, 'misto'=>$misto, 'url'=>$url, 
          'oddo'=>$oddo, 'anotace'=>$anotace, 'obsazeno'=>$obsazeno);
      $y->akce[]= $akce;
    }
    $y->trace= $trace;
    break;
  
  case 'roky':   // ---------------------------------------------- roky / servant
    # vrátí seznam roků, vce kterých proběhla iniciace
    # tzn. case.fe_groups=6 AND case.untilday=rok
    global $usergroups, $totrace, $href0;
    $totrace= 'uM';
    error_reporting(E_ALL ^ E_NOTICE);
    $roky= '';
    ezer_connect('setkani4');
    $tr= pdo_qry("
      SELECT GROUP_CONCAT(DISTINCT YEAR(FROM_UNIXTIME(c.untilday)) ORDER BY c.untilday)
      FROM setkani4.tx_gncase AS c
      JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
      -- JOIN setkani.pages AS g ON c.pid=g.uid
      WHERE !c.deleted AND !c.hidden AND fe_groups=6
    ");
    list($roky)= pdo_fetch_row($tr);
    $y->ip= ip_get();
    $y->roky= $roky;
    $y->trace= $trace;
    break;
  
  case 'foto':   // ---------------------------------------------- foto / servant
    global $usergroups, $totrace, $href0;
    $totrace= 'uM';
    $href0= "$index?page=foto&";
    error_reporting(E_ALL ^ E_NOTICE);
    ezer_connect('ezer_db2');
    require_once("template.php");
    $usergroups= $x->groups;
    $y->ip= ip_get();
    $y->id= $x->id;
    $y->msg= akce('chlapi.online',$x->rok,$x->id);
    $y->trace= $trace;
    break;
  
  case 'free':   // ---------------------------------------------- free / servant
    global $usergroups, $totrace, $href0;
    // výběr ukázkových akcí
    $vyber= array(822,3273);
    $akce= $vyber[rand(0,count($vyber)-1)];
    $totrace= 'uM';
    $href0= "$index?page=foto&";
    error_reporting(E_ALL ^ E_NOTICE);
    ezer_connect('ezer_db2');
    require_once("template.php");
    $usergroups= '0';
    $y->msg= akce('chlapi.online-free',0,$akce);
    $y->trace= $trace;
    break;
  
  case 'mapa':   // ---------------------------------------------- mapa / servant
    ezer_connect('ezer_db2');
    $y->mapa= mapa2_skupiny2();
    $y->ip= ip_get();
    break;

  case 'mapa2':   // ---------------------------------------------- mapa / servant
    ezer_connect('ezer_db2');
    $y->mapa= mapa2_skupiny();
    $y->ip= ip_get();
    break;

  case 're_login': // ---------------------------------------------- re login
    global $PIN_alive;
    ezer_connect('ezer_db2');
    $y->state= 'ko'; 
    $ido= $x->ido;
    // ověření adresy a stavu PIN z Answeru
    list($diff)= select("TIMESTAMPDIFF(HOUR,pin_vydan_ch,NOW())", 
        "osoba", "id_osoba='$ido'", 'ezer_db2');
    if ( $diff<=$PIN_alive ) {
      // PIN správný a čerstvý - lze prodloužit
      query("UPDATE osoba SET pin_vydan_ch=NOW() WHERE id_osoba=$ido", 'ezer_db2');
      $y->state= 'ok'; 
    }
    break;

  // ======================================================================= lokální

  case 'cke':
    cke_server($x);
    break;
  case 'test':
    $y->test= 'ok';
    break;
  case 'dum':
    // zpracování objednávek Domu setkání
    require_once("pokoje.php");
    dum_server($x);
    break;
  case 'fe_host':
    // ověření údajů pro anonymního fe uživatele - jednoduchou odpovědí na dotaz
    break;
  case 'me_login': // ---------------------------------------------- me login
    // v x.local_ip je lokální IP adresa, ip_get() dá externí
    login_by_mail($x,$y); // přesunuto do mini.php aby bylo společné se servant.php
    if ( $y->state=='ok') {
      // přihlas uživatele jako FE
      $_SESSION['web']['fe_usergroups']= '4,6';
      $_SESSION['web']['fe_userlevel']= 0;
      $_SESSION['web']['fe_user']= $y->user;
      $_SESSION['web']['fe_level']= $y->level;
      $_SESSION['web']['mrop']= $y->mrop;
      $_SESSION['web']['firm']= $y->firm;
      $_SESSION['web']['fe_username']= $y->name;
      $y->fe_user= $y->user;
      $y->be_user= 0;
    } 
  case 'fe_key_ok': // --------------------------------------------- fe_key_ok
    $y->key_ok= $y->akey=="WEBKEYNHCHEIYHHPKBYGAFVUOVKEYWEB" ? 1 : 0;
    break;
  case 'fe_login': // ---------------------------------------------- fe login
    // ověření údajů pro fe-user
    $x->pass= str_replace("'",'"',$x->pass);
    list($uid,$usergroup,$userlevel,$ezer,$user)=
      select("uid,usergroup,userlevel,ezer,CONCAT(firstname,' ',name)",
        "setkani4.fe_users","username='{$x->name}' AND password='{$x->pass}'");
    $_SESSION['web']['fe_usergroups']= $usergroup;
    $_SESSION['web']['fe_userlevel']= $userlevel;
    $_SESSION['web']['fe_user']= $uid;
    $_SESSION['web']['fe_username']= $user;
    $y->fe_user= $uid;
    $y->be_user= 0;
    $ip= '';
    if ( $x->type=='be' ) {
      $y->user= $user;
      if ( $ezer ) {
        // pro ezer=1 zkontroluje přihlášení podle tabulky _user
        $x->pass= str_replace("'",'"',$x->pass);
        list($idu,$ips)=
          select("id_user,ips","setkani4._user","username='{$x->name}' AND password='{$x->pass}'");
        if ( $idu==$ezer ) {
          // a zkontroluje IP (podle tabulky _user)
          $key_ok= $y->akey=="WEBKEYNHCHEIYHHPKBYGAFVUOVKEYWEB" ? 1 : 0;
          if ( $key_ok || ip_watch($ip,1) ) {
            $y->be_user= $idu;
            $_SESSION['cms']['user_id']= $idu;
            $_SESSION['cms']['user_key']= $key_ok;
            $y->msg= "logged user $uid, be_user changed to user={$_SESSION['cms']['user_id']}";
          }
        }
      }
    }
    // zápis o (ne)přihlášení
    $date= date('YmdHis',time());
    if ( !$ip ) ip_watch($ip,0);
    $login= $uid
          ? ('Login'.($y->be_user?($key_ok?'#':'+'):($x->type=='be'?($ezer?'-':'?'):'')))
          : 'Logfail';
    query("INSERT INTO gn_log (datetime,fe_user,action,message,ip) VALUES
      ('$date','$uid','$login','$user','$ip')");
    $_SESSION['web']['be_user']= $y->be_user;
    $y->web= $_SESSION['web'];
    break;
  case 'be_logout':
    $date= date('YmdHis',time());
    query("INSERT INTO gn_log (datetime,fe_user,action,message) VALUES
      ($date,'{$_SESSION['web']['fe_user']}','logout','{$_SESSION['web']['fe_username']}')");
    $_SESSION['web']= array();
    $_SESSION['web']['fe_user']= 0;
    $_SESSION['web']['be_user']= 0;
    unset($_SESSION['ans']);
    unset($_SESSION['cms']);
    session_write_close();
    $y->fe_user= 0;
    $y->be_user= 0;
    $y->page= $x->page;
    break;
  case 'clanek':
    $y->row= clanek($x->id);
    break;
  /** TABULKA */
  # ------------------------------------------ vyhodnocení testovací otázky pro Ezer.web.fe_host
  case 'table_tst':
    $y->ok= $_SESSION['web']['fe_host']= trim(strtolower($y->test))=='rohr' ? 1 : 0;
    break;
  # ------------------------------------------ přidání nového účastníka do tabulky
  case 'table_add2':
    $jmeno= trim($y->jmeno);
    $skupina= $y->skupina;
    $cid= $y->cid;
    $old_skupina= select('skupina','gnucast',"datum='{$y->datum}' AND TRIM(jmeno)='$jmeno'");
    $old_pocet= select('COUNT(*)','gnucast',"datum='{$y->datum}' AND skupina='$old_skupina' AND jmeno!='max'");
    $pocet= select('COUNT(*)','gnucast',"datum='{$y->datum}' AND skupina='$skupina' AND jmeno!='max'");
    $maximum= select('poradi','gnucast',"datum='{$y->datum}' AND skupina='$skupina' AND jmeno='max'");
    if ( $pocet>=$maximum && $skupina!=$old_skupina) {
      $y->msg= "skupina '$skupina' je už plná";
      $y->choose= 0;
    }
    elseif ( $old_skupina==$skupina && $old_pocet>1 ) {
      $y->msg= "$jmeno už je ve skupině $old_skupina";
      $y->choose= 1;
    }
    elseif ( $old_skupina==$skupina && $old_pocet==1 ) {
      $y->msg= "$jmeno už je ve skupině $old_skupina jako poslední, odstraním i skupinu";
      $y->choose= 2;
    }
    else {
//       $y->msg= "$jmeno, $skupina, $old_skupina, $old_pocet, $maximum";
      query("INSERT INTO gnucast(cid,datum,skupina,jmeno,timestamp)
             VALUES ($cid,'{$y->datum}','{$y->skupina}','$jmeno',UNIX_TIMESTAMP())");
    }
    break;
  # ------------------------------------------ odebrání účastníka z tabulky
  case 'table_del':
    $jmeno= trim($y->jmeno);
    $skupina= $y->skupina;
    $cid= $y->cid;
    if ( $cid ) { // cid musí být od května 2017 udáno
      query("DELETE FROM gnucast WHERE cid=$cid AND TRIM(jmeno)='$jmeno'");
      // pokud odstraňujeme posledního (asi hostitele), odstraníme celou skupinu
      $zbyva= select('COUNT(*)','gnucast',"cid=$cid AND skupina='$skupina' AND jmeno!='max'");
      if ( $zbyva==0 ) {
        query("DELETE FROM gnucast WHERE cid=$cid AND skupina='$skupina'");
        // pokud to byla jediná skupina, odstraníme i tu
        $zbyva= select('COUNT(*)','gnucast',"cid=$cid");
        if ( $zbyva==1 ) {
          query("DELETE FROM gnucast WHERE cid=$cid");
          // a také T-záznam v parts
          query("DELETE FROM tx_gncase_part WHERE tags='T' AND cid=$cid ");
        }
      }
    }
    else {
      $y->msg= "nastala chyba při pokusu o odstranění $jmeno z $skupina ($cid)";
    }
    break;
  # ------------------------------------------ přesun účastníka v tabulkce do jiné skupiny
  case 'table_mov':
    $jmeno= trim($y->jmeno);
    query("UPDATE gnucast SET skupina='{$y->skupina}'
           WHERE datum='{$y->datum}' AND TRIM(jmeno)='$jmeno'");
    break;

  }
  return 1;
}
# --------------------------------------------------------------------------------------- datum oddo
function datum_oddo($x1,$x2) {
  $d1= 0+substr($x1,8,2);
  $d2= 0+substr($x2,8,2);
  $m1= 0+substr($x1,5,2);
  $m2= 0+substr($x2,5,2);
  $r1= 0+substr($x1,0,4); 
  $r2= 0+substr($x2,0,4);
  $r= date('Y');
  if ( $x1==$x2 ) {  // zacatek a konec je stejny den
    $datum= "$d1. $m1" . ($r1!=$r ? ". $r1" : '');
  }
  elseif ( $r1==$r2 ) {
    if ( $m1==$m2 ) { // zacatek a konec je stejny mesic
      $datum= "$d1 - $d2. $m1. ".($r1==$r ? '' : $r1);
    }
    elseif ( $r1==$r ) { // letošní měsíce
      $datum= "$d1. $m1 - $d2. $m2.";
    }
    else { //ostatni pripady
      $datum= "$d1. $m1 - $d2. $m2. $r1";
    }
  }
  else { //ostatni pripady
    $datum= "$d1. $m1. $r1 - $d2. $m2. $r2";
  }
  return $datum;
}
# --------------------------------------------------------------------------------- url get_contents
function url_get_contents($url, $useragent='cURL', $headers=false, $follow_redirects=true, $debug=false) {
  // initialise the CURL library
  $ch = curl_init();
  // specify the URL to be retrieved
  curl_setopt($ch, CURLOPT_URL,$url);
  // we want to get the contents of the URL and store it in a variable
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  // specify the useragent: this is a required courtesy to site owners
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  // ignore SSL errors
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  // return headers as requested
  if ($headers==true){
    curl_setopt($ch, CURLOPT_HEADER,1);
  }
  // only return headers
  if ($headers=='headers only') {
    curl_setopt($ch, CURLOPT_NOBODY ,1);
  }
  // follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
  if ($follow_redirects==true) {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
  }
  // if debugging, return an array with CURL's debug info and the URL contents
  if ($debug==true) {
    $result['contents']=curl_exec($ch);
    $result['info']=curl_getinfo($ch);
  }
  // otherwise just return the contents as a variable
  else $result=curl_exec($ch);
  // free resources
  curl_close($ch);
  // send back the data
  return $result;
}



# ------------------------------------------------------------------------------------ cms post_request
/**
 * Sends POST request to URL
 * @param $url
 * @param array $params
 * @return string response message
 */
function cms_post_request($url, array $params) {
  $query_content = http_build_query($params);

  try {
    //if (!file_exists($url)) return "Server neodpovídá. Zkuste to prosím později.";
    $fp = fopen($url, 'r', FALSE, // do not use_include_path
        stream_context_create([
            'http' => [
                'header'  => [ // header array does not need '\r\n'
                    'Content-type: application/x-www-form-urlencoded',
                    'Content-Length: ' . strlen($query_content)
                ],
                'method'  => 'POST',
                'content' => $query_content
            ]
        ]));
    if ($fp === FALSE) {
      return "Něco se nepodařilo - zkuste to za chvíli.";
    }
    //possibly read response...
    $result = stream_get_contents($fp);
    fclose($fp);
    return $result;
  } catch (Exception $e) {
    return "Chyba: prosíme, napište administrátorovi. Přiložte následující popis chyby: <code>$e</code>";
  }
}

?>
