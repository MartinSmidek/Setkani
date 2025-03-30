<?php
include "cms_onclick.php";
define('COOKIE_JS_PROPERTIES', $ezer_server == 1 ? "SameSite=None;Secure;" : "");


# --------------------------------------------------------------------------------------==> def menu
function def_menu($from_table=false) { trace();
  global $def_block;
  $letos= date('Y');
  if ( $from_table ) {
    $def_block= array();
    $mn= pdo_qry("
      SELECT mid,ref,typ,site,mref,event,nazev,next,val,elem,title 
      FROM tx_gnmenu WHERE wid=1 ORDER BY mid
    ");
    while ($mn && (
        list($mid,$ref,$typ,$site,$mref,$event,$nazev,$next,$val,$elems,$title)
            = pdo_fetch_array($mn))) {
      list($ref1,$ref2)= explode('!',$ref);
      $ref= $ref2 ? $ref2 : $ref1;
      $def_block[$ref]= "$typ:$mid:$site:$mref:$event:$nazev:$next:$val:$elems:$title";
    }
//                                                        debug($def_block);
  }
  else {
    $def_vse= 'rodiny,manzele,chlapi,zeny,mladez';
    $def_block= array( // je používáno také fcí admin_web
      # hlavní menu    typ  i site mref             on    název             next/default       elem ...                          title
        'ms'          => "hm:8:    :odkaz            :: Manželská setkání:::                             menu=informace_ms,galerie_ms;odkaz=".urlencode("http://manzelska.setkani.org").": Galerie Manželských setkání",
        'dum'         => "hm:11:   :odkaz            :: Dům setkání:::                           menu=informace_ds,galerie_ds,chystame,objednavky,ceny,archiv;odkaz=".urlencode("http://dum.setkani.org").": Galerie Domu setkání",
        'akce'        => "hm:10:   :akce             :akce,102: Naše akce::   $def_vse:          proc=plan_rok; akce=prehled:      Akce pro rodiny, muže i ženy pořádané YMCA Setkání",
        'libr'        => 'hm:13:   :manzelak         ::     Knihov&shy;nička:bulletin::          menu=bulletin,tance,modlitby,knihy,audio,obrazy,odkazy',
        'my'          => 'hm:14:0.9:ymca-setkani     :clanek,21:O nás:::                         mclanky;-clanky=21,15,13,284,287,263:      Informace o YMCA Setkání', //o_nas',
      # Manželská setkání
        'galerie_ms'  => "sm:51:0.9:odkaz            :: Galerie:::           odkaz=".urlencode("http://manzelska.setkani.org/galerie/").": Fotogalerie Manželských setkání",
        'informace_ms'=> "sm:52:0.9:odkaz            :: Informace:::         odkaz=".urlencode("http://manzelska.setkani.org/informace/").": Fotogalerie Domu setkání",
      # Dům setkání
//      'alberice'    => 'sm:20:0.9:alberice/dum     :clanek,37: Dům Setkání:::                  mclanky;:                         Dům setkání',
        'informace_ds'=> "sm:47:0.9:odkaz            :: Informace:::                             odkaz=".urlencode("http://dum.setkani.org/informace/").": Informace k Domu Setkání",
        'galerie_ds'  => "sm:21:0.9:odkaz            :: Galerie:::                               odkaz=".urlencode("http://dum.setkani.org/galerie/").": Fotogalerie Domu setkání",
        'chystame'    => "sm:21:0.9:alberice/akce    :akce,222: Akce v Domě::  $def_vse:         proc=aplan; akce=bude:            Akce v Domě setkání",
        'objednavky'  => 'sm:23:0.8:alberice/objednavky::        Objed&shy;návky:::              dum:                              Objednávky pobytů v Domě setkání',
        'ceny'        => 'sm:24:0.8:alberice/ceny    ::          Ceny:::                         vlakno=51:                        Ceny služeb Domu setkání',
        'archiv'      => "sm:22:   :alberice/$letos  ::          Prožili jsme:::                 akce=aprehled:                    Archiv akcí v Domě setkání",
      # knihovnička
        'bulletin'    => 'sm:25:0.9:manzelak         ::          Manželák:::                     vlakno=80:                        Manželák - časopis YMCA Setkání',
        'tance'       => 'sm:26:0.9:tance            :clanek,322:Tance:::                        mclanky;-clanky=322:              Židovské biblické tance',    // 322=tance
        'modlitby'    => 'sm:27:0.1:modlitby         :clanek,254:Modlitby:::                     mclanky;-clanky=254:              Modlitby',    // 254=modlitby
        'knihy'       => 'sm:28:0.0:cetba            :kniha,228: Četba:::                        mknihy;-knihy=228,250,241:        Vybrané texty', // 241=články
        'audio'       => 'sm:29:0.2:audio            :kniha,242: Audio:::                        mknihy;-knihy=242:                Vybraná audia',
        'obrazy'      => 'sm:30:0.4:video            :clanek,320:Video:::                        mclanky;-clanky=320:              Videa a obrazy',
        'odkazy'      => 'sm:31:0.8:doporuceni       :clanek,320:Doporu&shy;čujeme:::            mclanky;-clanky=223,9:            Doporučujeme s podobnou tématikou',
      # speciální stránky
        'home'        => "tm:32:0.9:home             ::<i class='fa fa-home'></i> Domů:::        home:                             Akce pro rodiny, muže i ženy pořádané YMCA Setkání",
        'hledej'      => "tm:34:   :hledej           ::Hledat:::                                 search:                           Hledej",
        'clanek'      => ':99:   :-                ::-:::                                        single:                           ',
    );
  }
}
# --------------------------------------------------------------------------------------==> page
function page($from_table,$app_root,$page) {  trace();
  global $CMS, $fe_user_display, $mode, $microtime_start, $page_kb, $page_ms;
  global $show_deleted, $show_hidden;
  global $userid; // skupiny
  $ret= (object)array();
  def_menu($from_table);
//  $FREE= 0; // ponechává lokální odkazy na obrázky
  $microtime_start= microtime();
  $CMS= true;
  ezer_connect('setkani4');
  $href= "index.php?page=";
//   $href= "index.php?trace=uU&page=";
//                                                 display("href=$href");
  $path= $page ? explode('!',$page) : array('home');
//                                                 debug($path,"path");
  $show_hidden= $_SESSION['web']['hidden'];
  $show_deleted= $_SESSION['web']['deleted'];
  $fe_user_display= 'none';
  $fe_user= isset($_SESSION['web']['fe_user']) ? $_SESSION['web']['fe_user'] : 0;
  $be_user= isset($_SESSION['cms']['user_id']) ? $_SESSION['cms']['user_id'] : 0;
  $fe_host= $fe_user || $be_user ? 0 : (
  isset($_SESSION['web']['fe_host']) ? $_SESSION['web']['fe_host'] : 0);
  $userid= isset($_SESSION['web']['id_user']) ? $_SESSION['web']['id_user'] : 0;
  $mode= array(1=>isset($_COOKIE['web_mode_1']) ? $_COOKIE['web_mode_1'] : 1); // mode1=dlaždice
  $ret->html= template($href,$path,$fe_host,$fe_user,$be_user,0);
  $ret->kb= $page_kb;
  $ret->ms= $page_ms;
  $ret->start= $microtime_start;
  return $ret;
}
# --------------------------------------------------------------------------------------==> popup
function popup($title,$obsah,$back,$typ='info') {
  global $popup_tit, $popup_vol, $popup_bck, $popup_typ;
  $popup_tit= $title;
  $popup_vol= $obsah;
  $popup_bck= $back;
  $popup_typ= $typ;
}
# --------------------------------------------------------------------------------------==> template
# path= [stranka, id2, ...] jako rozklad předaného url
#   stránka = home | kontakty | search | mapa
function template($href,$path,$fe_host0,$fe_user0=0,$be_user0=0,$echo=1) { trace();//trace("mode=$mode");
  global $CMS, $fe_user_display, $fe_host, $fe_user, $be_user, $mode, $microtime_start;
  global $def_pars, $eb_link, $href0, $clear;
  global $popup_tit, $popup_vol, $popup_bck, $popup_typ, $info_note;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $trace, $ezer_server;
  global $page_mref, $def_block, $news_time;
  $found= 0;
  $fe_user= $fe_user0;
  $be_user= $be_user0;
  $fe_host= $fe_user || $be_user ? 0 : $fe_host0;
  $username=   isset($_SESSION['web']['fe_username']) ? $_SESSION['web']['fe_username'] : '' ;
  $usergroups= isset($_SESSION['web']['fe_usergroups']) ? $_SESSION['web']['fe_usergroups'] : '';
  $usergroups= $usergroups ? "0,$usergroups" : '0';
  $currpage= implode('!',$path);
  ezer_connect('setkani4');
  $href0= $href;
  $clear= "<br style='clear:both'>";

  /** ==============================================> MENU */

# blok = typ_bloku : ref : název : next : elem* : title -- popis stránky, pokud typ=menu je popsáno dál
# path = id*                                            -- defaulní pokračování cesty
# elem = typ_elementu = id*                             -- popis elementu stránky

// odstraníme administrátorské části, pokud tento není přihlášen admin (fe-user=1)
  if ( !$CMS || in_array($fe_user,array(1,4))===false ) {
    foreach ($def_block as $id => $desc) {
      if ( substr($id,0,2)=='a_' ) unset($def_block[$id]);
    }
  }

  $def_on= array(
      'akce'   => "přidej novou akci",
      'aakce'  => "přidej novou akci v DS",
      'clanek' => "přidej nový článek",           // 1
      'kniha'  => "přidej nový svazek",           // 3
      'prezentace' => "přidej novou prezentaci"   // 7
  );
  $def_menu= array(
      'tance'    => ",['seřaď tance podle abecedy',function(el){ seradit(322,'tance'); }]",
      'knihy'    => ",['seřaď četbu podle autorů', function(el){ seradit('228,250,241','knihy'); }]",
      'a_online' => ",['přidej novou knížku', function(el){ vytvorit('kniha',324,16); }]"
          . ",['přidej novou prezentaci', function(el){ vytvorit('prezentace',324,16); }]",
      'audio'    => ",['seřaď audio podle autorů', function(el){ seradit('242','knihy'); }]"
  );
  $def_pars= array(
      'komu' => array(
          'rodiny'   => 'rodiny:1',
          'manzele'  => 'manželé:2',
          'chlapi'   => 'chlapi:3',
          'zeny'     => 'ženy:4',
          'mladez'   => 'mládež:5',
          'alberice' => 'pouze akce v Domě setkání:6', // nezobrazuje se
      ),
      'bylo' => array(
          'letos'  => 'letos:YEAR(FROM_UNIXTIME(fromday))=YEAR(NOW())',
          'vloni'  => 'loni:YEAR(NOW())-YEAR(FROM_UNIXTIME(fromday))=1',
          '5let'   => 'před 2-5 lety:YEAR(NOW())-YEAR(FROM_UNIXTIME(fromday)) BETWEEN 2 AND 5',
          '10let'  => 'před 6-10:YEAR(NOW())-YEAR(FROM_UNIXTIME(fromday)) BETWEEN 6 AND 10',
          'starsi' => 'dříve:YEAR(NOW())-YEAR(FROM_UNIXTIME(fromday))>10',
      ),
      'komu_reversed' => array(
         "1" => 'rodiny',
         "2" => 'manzele',
         "3" => 'chlapi',
         "4" => 'zeny' ,
         "5"  => 'mladez',
         "6" => 'alberice'
      )
  );
# zobrazíme topmenu a hlavní menu, přitom zjistíme případné doplnění cesty a její správnost
  $topmenu= $submenu_komu= $mainmenu= $mobile_mainmenu= $page= $elems= $mid= $pars= $web_banner= '';
//                                                         debug($path,"před corr");
# ---------------------------------------------------------------- . test pro explicitně udaný part
// goto decode_path;
  $last= count($path)-1;
  list($id,$tag)= explode('#',$path[$last].'#');
  $url= '';
  if ( is_numeric($id) && ($path[0]=='plan_akci' || $path[0]=='archiv_akci') ) {
//                                                         display("part=$part");
    # pro zjištění KDY
    $when= '';
    foreach ( $def_pars['bylo'] as $iid=>$nazev_expr ) {
      list($nazev,$expr)= explode(':',$nazev_expr);
      $when.= "\n      WHEN $expr THEN '$iid' ";
    }
    # pro zjištění KOMU
    $_komu= array();
    foreach($def_pars['komu'] as $ii=>$nazev_i) {
      list($nazev,$i)= explode(':',$nazev_i);
      if ( $i!=6 ) $_komu[$i]= $ii;
    }
    $test= $path[$last-1]=='case' ? "c.uid=$id" : "p.uid=$id";
    $rr= pdo_qry("
    SELECT cid, p.uid, LEFT(FROM_UNIXTIME(fromday),10) AS _od, program,
      CASE WHEN LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10) THEN 'bude' $when
      ELSE '' END
    FROM setkani4.tx_gncase_part AS p
    JOIN setkani4.tx_gncase AS c ON p.cid=c.uid
    WHERE $test
  ");
    list($cid,$pid,$od,$kdo,$kdy)= pdo_fetch_row($rr);
    $komu= $del= '';
    if ( $kdo ) {
      foreach (explode(',',$kdo) as $p) {
        $komu.= "$del{$_komu[$p]}"; $del= ',';
      }
    }
    if ( $kdy=='bude' ) {
      $url= "plan_akci!$komu!$pid";
      $path= array();
      $path[0]= 'plan_akci';
      $path[1]= $komu;
      $path[2]= "$pid$tag";
    }
    else {
      $url= "archiv_akci!$kdy!$pid$tag";
      $path= array();
      $path[0]= 'archiv_akci';
      $path[1]= "$komu,$kdy";
      $path[2]= "$pid$tag";
    }
//                                                         display("<u>part $part:$cid,$pid,$od,$kdo,$kdy</u>");
//                                                         display("<u>$url</u>");
  }
  decode_path:
# ---------------------------------------------------------------- . dekódování $path
//                                                         debug($path,"před menu");
  $web_title= 'YMCA Setkání';
  $page_mref= '';  // reference stránky pro abstrakty a návraty z článků - global
  $dnu= isset($_COOKIE['web_show_changes']) ? $_COOKIE['web_show_changes'] : 1;
  $news_time= time() - $dnu*24*60*60;
  $search= isset($_COOKIE['web_search']) ? $_COOKIE['web_search'] : '';

  //use cookies do diferentiate between show operational modes, instead of url
  if (!isset($_COOKIE['akce']) || strlen($_COOKIE['akce']) < 3) {
    $_COOKIE['akce'] = urlencode('rodiny,manzele,chlapi,zeny,mladez');
    setcookie('akce', $_COOKIE['akce'], time()+86400, "/", COOKIE_DOMAIN);
  }

  $mainmenu = "<ul class='menu_hm'>";
  $mobile_mainmenu = "<ul class='menu_hm'><li></li>";
  foreach ($def_block as $ref=>$def) {
    list($typ_bloku,$mid1,$site,$mref,$context,$nazev,$next,$default1,$elems1,$title1)= explode(':',$def);
    // UPD se uplatní pouze pokud je aspoň o den větší než NEW
    if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
    $upd1= select1("IF(tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(tstamp))>TO_DAYS(FROM_UNIXTIME(crdate)),' upd',' new'),'')",
        "tx_gnmenu","mid=$mid1");
    $mref= trim($mref);
    $nazev0= trim($nazev);
    $nazev= "<span>$nazev0</span>";
    $next= str_replace(' ','',$next);
    $default1= str_replace(' ','',$default1);
    $elems1= str_replace(' ','',$elems1);
    $input= "0";
    $a_ref= substr($ref,0,2)=='a_' ? " admin" : '';
    if ( $typ_bloku=='tm' ) {
      list($elem)= explode(';',$elems1);
      $active= $path[0]==$ref ? ' active' : '';
      if ( $active ) {
        if ( $title1 ) $web_title= $title1;
        $page= $ref;
        $elems= $elems1;
        $mid= $mid1;
        if ( $ref=='hledej' )  $page_mref= "/$mref/$search";
        $web_banner= "web_$ref";
//                                                         display("tm:web_banner='$web_banner'");
      }
      if ( $ref=='hledej' ) {
        $jmp= $CMS ? "onclick=\"go(arguments[0],'$href$ref','/$mref',1,1);\"" : "onclick=\"searchByQuery();\"";
        $submit = $CMS ? "onsubmit=\"event.preventDefault();\"" : "onsubmit=\"event.preventDefault();\"";
        $topmenu.= "
            <div class=\"header-search\">		
            <span class=\"search-icon\" onclick=\"jQuery(this).parent().toggleClass('active');jQuery(this).children().each(function() {jQuery(this).toggleClass('nodisplay');});\">
                <span class=\"ic-search\">
                    <svg focusable=\"false\" role=\"presentation\" xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"21\" viewBox=\"0 0 20 21\">
                        <path fill=\"currentColor\" fill-rule=\"evenodd\" d=\"M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z\"></path>
                    </svg>
                </span>
                <span class=\"ic-close nodisplay\">
                    <svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" x=\"0px\" y=\"0px\" width=\"612px\" height=\"612px\" viewBox=\"0 0 612 612\" fill=\"currentColor\" style=\"enable-background:new 0 0 612 612;\" xml:space=\"preserve\"><g><g id=\"cross\"><g><polygon points=\"612,36.004 576.521,0.603 306,270.608 35.478,0.603 0,36.004 270.522,306.011 0,575.997 35.478,611.397 306,341.411 576.521,611.397 612,575.997 341.459,306.011 \"></polygon></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
                </span>
            </span>
              
              <div class=\"header-search-container\">
                <form id='search_form' role=\"search\" class=\"search-form\" $submit>
                    <label>
                        <span class=\"hidden-label-text\">Hledat:</span>
                        <input id='search' size=10 title='hledané slovo' placeholder='Hledat...' /> 
                    </label>
                    <button class=\"search-submit\" $jmp>
                        <svg focusable=\"false\" role=\"presentation\" xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"21\" viewBox=\"0 0 20 21\">
                            <path fill=\"currentColor\" fill-rule=\"evenodd\" d=\"M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z\"></path>
                        </svg>
                    </button>
                </form>
              </div>
            </div>";
      } else {
        $jmp= $CMS || substr($mref,0,1)=='-'
            ? "onclick=\"go(arguments[0],'$href$ref','/$mref',$input,1);\""
            : "href='/$mref'";
        if ( $nazev0!='-' ) { //ignore 'hledej' and perform custom code
          $topmenu.= " <a $jmp class='jump$active$upd1'>$nazev</a>";
        }
      }
    }
    elseif ( $typ_bloku=='hm' ) {
      list($elem)= explode(';',$elems1);
      list($typ,$ids)= explode('=',$elem.'=');
      # upřesnění cesty pro akce - z cookie nebo defaultu
      if ( $ref=='akce' ) {
        //$cookie1= isset($_COOKIE[$ref]) && $_COOKIE[$ref] ? '!'.urldecode($_COOKIE[$ref]) : '';
        //$cont= $cookie1 ?: $default1;
        $cont= 'akce';
        //$mref= str_replace('!','',$cont);
        $mref = $cont;
//                                                 display("Cookie[$ref]=$cookie1 ... $default1 ... $cont");
      }
      if ( $CMS || substr($mref,0,1)=='-' ) {
        $cont= $ref.($next ? "!$next" : '');
        $cookie= isset($_COOKIE[$ref]) ? '!'.urldecode($_COOKIE[$ref]) : '';
//                                               display("Cookie? $ref=$cookie");
        $cont.= $default1 ? ($cookie ?: '!'.$default1) : '';
      }
      else {
        $cont= $mref;
      }

      $active= $path[0]==$ref ? ' active' : '';
      if ( $active ) {
        if ( $title1 ) $web_title= $title1;
        if ( $a_ref ) $a_ref.= "_active";
        if ( $mref=='akce' ) $web_akce= 'akce';
        $elems= $elems1;
        $mid= $mid1;
        $href0.= $ref;
        $web_banner= "web_$ref";
        $page_mref= $mref;
//                                                         display("1 page_mref = $page_mref");
      }
      if ( $typ=='menu' && $active ) {
        $submenu = '';
        if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
        foreach (explode(',',$ids) as $ref2) {
          list($typ2,$mid2,$site2,$mref2,$context2,$nazev2,$next2,$default2,$elems2,$title2)= explode(':',$def_block[$ref2]);
          $upd2= select1("IF(tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(tstamp))>TO_DAYS(FROM_UNIXTIME(crdate)),' upd',' new'),'')",
              "tx_gnmenu","mid=$mid2");
          $a_ref2= substr($ref2,0,2)=='a_' ? " admin" : '';
          $mref2= trim($mref2);
          $nazev2= "<span>$nazev2</span>";
          $default2= str_replace(' ','',$default2);
          $elems2= str_replace(' ','',$elems2);
          $active2= $path[1]==$ref2 ? ' active' : '';
          # doplnění defaultní cesty
          $cont2= $ref2;
          if ( $active2 ) {
            if ( $title2 ) $web_title= $title2;
            if ( $a_ref2 ) $a_ref2.= "_active";
            $page_mref= $mref2;
            $elems= $elems2;
            $mid= $mid2;
            $href0.= "!$ref2";
            $cookie2= isset($_COOKIE[$ref2]) ? urldecode($_COOKIE[$ref2]) : '';
//                                                   display("Cookie2? $ref2=$cookie2");
            $cont= "$ref!$ref2". ($default2 ? ('!'.$cookie2 ?: '!'.$default2) : '');
            $cont2.= $default2 ? ($cookie2 ? "!$cookie2": "!$default2") : '';
//                                                         display("$ref2:$default2:");
            if ( $next && $default2 ) {
              $path[2]= ($cookie2 ?: $default2);
            }
          }
          $on= " oncontextmenu='return false;'";

          // if 'odkaz', create redirect href and do not add context menu opts
          if ( $mref2 == "odkaz" ) {
            foreach (explode(";", $elems2) as $chunk) {
              $urldata = explode('=', trim($chunk) . "=");
              for ($i = 0; $i < count($urldata); $i += 2) {
                if ($urldata[$i] == "odkaz") {
                  $redirect = urldecode($urldata[1]);
                  break;
                }
              }
            }
            $submenu.= $CMS || substr($mref2,0,1)=='-'
                ? "<li><a href='$redirect' class='$a_ref2$upd2' target='_blank' $on>$nazev2</a></li>"
                : "<li><a href='$redirect' class='$upd2'>$nazev2</a></li>";
          } else {
            // přidání kontextového menu pro přidávání aj.
            if ( $context2 ) {
              list($ctyp,$pgid)= explode(',',$context2);
              $on_plus= isset($def_menu[$ref2]) ? $def_menu[$ref2] : '';
              $on= " oncontextmenu=\"
                go_with_menu(arguments[0],'$href{$path[0]}!$cont2','/$mref2',$input,1,[
                ['{$def_on[$ctyp]}',function(el){ vytvorit('$ctyp','$pgid','$mid2'); }]
                $on_plus
              ]); return false;\"";
            }

            $submenu.= $CMS || substr($mref2,0,1)=='-'
                ? "<li><a onclick=\"go(arguments[0],'$href{$path[0]}!$cont2','/$mref2',$input,1);\" "
                . "class='$active2$a_ref2$upd2'$on>$nazev2</a></li>"
                : "<li><a href='/$mref2' class='$active2$upd2'>$nazev2</a></li>";
          }
        }
        array_shift($path);
      } elseif ($typ=='menu') {
        $submenu = '';
        if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
        foreach (explode(',',$ids) as $ref2) {
          list($typ2,$mid2,$site2,$mref2,$context2,$nazev2,$next2,$default2,$elems2,$title2)= explode(':',$def_block[$ref2]);
          $upd2= select1("IF(tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(tstamp))>TO_DAYS(FROM_UNIXTIME(crdate)),' upd',' new'),'')",
              "tx_gnmenu","mid=$mid2");
          $a_ref2= substr($ref2,0,2)=='a_' ? " admin" : '';
          $mref2= trim($mref2);
          $nazev2= "<span>$nazev2</span>";
          # doplnění defaultní cesty
          $cont2= $ref2;
          $on= " oncontextmenu='return false;'";

          // if 'odkaz', create redirect href and do not add context menu opts
          if ( $mref2 == "odkaz" ) {
            foreach (explode(";", $elems2) as $chunk) {
              $urldata = explode('=', trim($chunk) . "=");
              for ($i = 0; $i < count($urldata); $i += 2) {
                if ($urldata[$i] == "odkaz") {
                  $redirect = urldecode($urldata[1]);
                  break;
                }
              }
            }
            $submenu.= $CMS || substr($mref2,0,1)=='-'
                ? "<li><a href='$redirect' class='$a_ref2$upd2' target='_blank' $on>$nazev2</a></li>"
                : "<li><a href='$redirect' class='$upd2'>$nazev2</a></li>";
          } else {
            // přidání kontextového menu pro přidávání aj.
            if ( $context2 ) {
              list($ctyp,$pgid)= explode(',',$context2);
              $on_plus= isset($def_menu[$ref2]) ? $def_menu[$ref2] : '';
              $on= " oncontextmenu=\"
                go_with_menu(arguments[0],'$href".explode("!", $cont)[0]."!$cont2','/$mref2',$input,1,[
                ['{$def_on[$ctyp]}',function(el){ vytvorit('$ctyp','$pgid','$mid2'); }]
                $on_plus
              ]); return false;\"";
            }

            $submenu.= $CMS || substr($mref2,0,1)=='-'
                ? "<li><a onclick='go(arguments[0],\"$href".explode("!", $cont)[0]."!$cont2\",\"/$mref2\",$input,1);' "
                . "class='$a_ref2$upd2' $on>$nazev2</a></li>"
                : "<li><a href='/$mref2' class='$upd2'>$nazev2</a></li>";
          }
        }
      }

      // přidání kontextového menu pro přidávání
      $on= " oncontextmenu='return false;'";

      if ( $cont ) {
        if ( $mref == "odkaz" ) {
          foreach (explode(";", $elems1) as $chunk) {
            $urldata = explode('=', $chunk . "=");
            for ($i = 0; $i < count($urldata); $i += 2) {
              if ($urldata[$i] == "odkaz") {
                $redirect = urldecode($urldata[1]);
                break;
              }
            }
          }
          $menuitem = $CMS || substr($mref,0,1)=='-'
              ? " <a href='$redirect' class='jump$active$a_ref$upd1' target='_blank' $on>$nazev</a>"
              : " <a href='$redirect' class='jump$active$upd1'>$nazev</a>";
        } else {
          if ( $context ) {
            list($ctyp,$pgid)= explode(',',$context);
            $on_plus= isset($def_menu[$ref]) ? $def_menu[$ref] : '';
            $on= " oncontextmenu=\"
                go_with_menu(arguments[0],'$href$cont','/$mref',$input,1,[
                ['{$def_on[$ctyp]}',function(el){ vytvorit('$ctyp','$pgid','$mid1'); }]
                $on_plus
              ]); return false;\"";
          }

          $menuitem = $CMS || substr($mref,0,1)=='-'
              ? " <a onclick='go(arguments[0],\"$href$cont\",\"/$mref\",$input,1);' "
              . "class='jump$active$a_ref$upd1' $on>$nazev</a>"
              : " <a href='/$mref' class='jump$active$upd1'>$nazev</a>";
        }
        if ($submenu) {
          $mainmenu .= "<li>$menuitem<ul class='menu_sm'>$submenu</ul></li>";
          $mobile_mainmenu .= "<li>$menuitem<span class=\"navigation-toggle\" onclick='
            jQuery(this).next().slideToggle(); return true;'><i class=\"navigation-icon\"></i></span>
            <ul class='menu_sm' style='display: none'>$submenu</ul></li>";
          $submenu = "";
        } else {
          $mainmenu .= "<li>$menuitem</li>";
          $mobile_mainmenu .= "<li>$menuitem</li>";
        }

      }
    } else if ($path[0]==$ref) {       //unknown type of submenu --> just load the requested elements
      $elems= $elems1;
    }
  }
  $kontakty = "<li><a onclick='jQuery(\"html, body\").animate({scrollTop: (jQuery(\"#footer_contacts\").offset().top - 100)}, 500);'
       class='jump'><span>Kontakty</span></a></li>";
  $mainmenu .= $kontakty . "</ul>";
  $mobile_mainmenu .= $kontakty . "</ul>";

# zobrazíme stránku $page podle jeho $elems a $pars
  $body= '';
  $par= array_shift($path);
//                                                         debug($path,"po menu, par=$par");
//                                                         display("par=$par");
# projdeme elementy
  $rok= $cased_vyber ='';
  foreach (explode(';',$elems) as $elem) {
    list($typ,$ids)= explode('=',$elem.'=');
    $typ= str_replace(' ','',$typ);
    switch ($typ) {
      /** ==========================================> ELEMENTY */
      case 'web_zmeny':    # ----------------------------------------- . web zmeny
      case 'web_odkazy':   # ----------------------------------------- . web odkazy
      case 'web_reconstr': # ----------------------------------------- . web reconstr
      case 'web_sitemap':  # ----------------------------------------- . web sitemap
        # administrátorské výpisy
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= admin_web($typ,$id);
        break;

      case 'odkaz':
        $url= urldecode(array_shift($path));
        header("Location: $url");
        die();

      case 'knihy': # ------------------------------------------------ . knihy
        # seznam autorů
        $body.= "<div class='content'><h1>$web_title</h1></div>";
        $body.= "<div class='content'><h1>$web_title</h1></div>";
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= knihy($ids,$id);
        $body.= "<div class='content'><h2>Z vybraných článků</h2></div>";
        $body.= clanky($ids,$id);
        break;

      case 'mknihy': # ----------------------------------------------- . mknihy
        # seznam autorů
        $body.= "<div class='content'><h1>$web_title</h1></div>";
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= knihy('',$id,$mid);
//        $body.= "<div class='content'><h2>Z vybraných článků</h2></div>";
//        $body.= clanky('',$id,$mid);
        break;

      case 'clanek': # ------------------------------------------------ . clanek
        //todo probably not called anymore
        # článek zadaný názvem nebo uid
        $x= clanek($ids);  //todo does not exist
        if ( $CMS ) {

//       $menu= "oncontextmenu=\"
//         Ezer.fce.contextmenu([
//           ['editovat',function(el){ opravit('clanek','$ids'); }],          staré argumenty
//           ['nový',function(el){ vytvorit('clanek','$ids'); }],
//           ['vymazat',function(el){ zrusit('clanek','$ids'); }]
//         ],arguments[0],'clanek');return false;\"";
          $body.= "<div id='clanek' class='clanek x' $menu>
                 <h1>$x->nadpis</h1>$x->obsah
               </div>";
        }
        else {
          $body.= "<div id='clanek' class='x'><div class='clanek x'>
                 <h1>$x->nadpis</h1>$x->obsah
               </div></div>";
        }
        break;

      case 'vlakno': # ---------------------------------------------==> . vlakno
        # vlákno zadané cid
        if ( !$ids && count($path) ) $ids= array_shift($path);
        $body.= vlakno($ids,'clanek','',true, true);
        break;

      case 'clanky': # ------------------------------------------------ . clanky
        # seznam abstraktů článků zadaných jménem menu nebo pid
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        $body.= "<div class='content'><h1>$web_title</h1></div>";
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= clanky($ids,$id);
        break;

      case 'mclanky': # ----------------------------------------------- . mclanky
        # seznam abstraktů článků zadaných mid
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        $body.= "<div class='content'><h1>$web_title</h1></div>";
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= clanky('',$id,$mid);
        break;

      case 'home':    # ----------------------------------------------- . home
//    $id= array_shift($path);
//    list($id)= explode('#',$id);
        $body.= "<div class='content'><h1>Nejbližší akce</h1></div>";
        $body.= home();
        break;

      case 'team':    # ----------------------------------------------- . team
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= "týmové informace";
        break;

      case 'single':
        $id= pdo_real_escape_string(array_shift($path));
        if (is_numeric($id)) {
          $qry = pdo_qry("SELECT cid FROM setkani4.tx_gncase_part WHERE uid=$id LIMIT 1");
          while ($qry && (list($cid) = pdo_fetch_array($qry))) {
            $body .= vlakno($cid, 'clanek', '', true);
            break 2;
          }
        }  //else let search handle it :)
      case 'search': # ------------------------------------------------ . search
        # seznam nalezených abstraktů článků nebo akcí
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        if (!$CMS) {
          $body .= facebook_dependency();
          $id= array_shift($path);
          $search = array_shift($path);

        } else {
          $id= array_shift($path);
        }
        $body.= "<div class='content'><h1>Výsledky hledání &nbsp;<b>$search</b></h1></div>";
        list($id)= explode('#',$id);
//                                                 display("page_mref/s=$page_mref");
        $body.= akce('hledej',$ids,$id,'',$search);
        if (!$CMS) {$body.= facebook();}
        break;

      case 'akce':   # ------------------------------------------------ . akce
        # může následovat rok (vznikne kliknutím na abstrakt)
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        # STRUKTURA
        #   div.list
        #     div.kniha_bg
        #       br#rok{yyyy}
        #       div.kniha_br                        ... Archiv {n} akcí ...
        #       div.list
        #         div.{abstr|abstr_line}#n{i}       ... i=1..n
        #           div.code                        ... CMS: technické údaje cid/pid
        #           A: div.abstract                 ... zavřený článek/akce
        #           B: div#vlakno                   ... otevřený článek = cid
        #                div                        ... část článku = pid
        #       div.kniha_br                        ... konec archivu
//                                                         display("* page_mref = $page_mref");
        $id= array_shift($path);
        if ( $ids=='prehled' ) {
          $body.= "<div class='content'><br><h1>YMCA Setkání - naše akce</h1><br>";

          $body.= "<div class='commentary_div'> Prohlédněte si seznam akcí které nabízíme, nebo těch které již proběhly.
                Pokud je k dispozici i kalendář, pak je zobrazen vepředu přede všemi akcemi.
                Akce lze třídit podle toho, komu jsou určeny - akce pro rodiny, manžele, chlapy,
                ženy či mládež. Akce pořádané v Domě Setkání v Krkonošských Albeřicích jsou také
                dostupné v menu 'Dům Setkání > Akce v Domě' nebo 'Dům Setkání > Prožili jsme'.
                Některé fotografie z akcí naleznete ve fotogalerii, více je pak dostupné na dalších
                webech z odkazů ve fotogalerii.
                </div></div><br><br>";

          $body.= akce_prehled('', $rok,$id);
        } elseif ( $ids=='aprehled' ) { // proběhlé akce v Domě setkání
                                                 debug($path,"path= $id,...");
          $body .= "<div class='content'><h1>Archiv akcí v domě</h1><br>";

          $body.= "<div class='commentary_div'> Prohlédněte si seznam akcí které proběhly v Domě Setkání. Některé fotografie z akcí 
                naleznete ve fotogalerii, více je pak dostupné na dalších
                webech z odkazů ve fotogalerii.
                </div></div><br>";
          $rok= $id?:date('Y');
          $id= array_shift($path);
          list($page_mref,$roks)= explode('/',$page_mref);
          $body.= akce_prehled('dum',$rok,$id);
        } else {
          $kdy= $ids=='bude' ? $ids : '';
          $content_akce = akce('',$ids,$id);
          if (strlen($content_akce) < 30) {
            $content_akce = "<div class='content'><br><br>Nejsou k dispozici žádné akce k zobrazení.<br><br><br><br></div>";
          }

          $body.= $content_akce;
        }
        break;

      case 'dum':   # ----------------------------------------------==> . dum
        # seznam akcí podle proměnné $vyber
        # může následovat ident jednoho z článků (vznikne kliknutím na pokoj)
        # nebo slovo send_mail
        require_once("pokoje.php");
        $body.= mesice($path);
        break;

      case 'proc':
        # ----------------------------------------------- . výběr komu
        $proc_kdo= function ($dum_setkani) { trace();
          // by default, the server always fetches all the data and filtering is done on client with JS and classes
          global $def_pars;
          //display("kdo($x) - $href0 - $kernel");
          $dum = $dum_setkani ? "true" : "false";
          $html= "<div id='vyber' class='x'><script type='text/javascript'>jQuery(document).ready(function() { sync_proc_kdo($dum)});</script><div class='content'>";
          foreach($def_pars['komu'] as $id=>$nazev_i) {
            list($nazev,$i)= explode(':',$nazev_i);
            $alberice= $i==6 ? " style='display:none'" : '';
            $nazev= $i==6 ? '' : $nazev;
            if ( $i==6 ) continue;
            $on= " onchange='proc_kdo($dum);'"; //if(jQuery("#inclusion_icon").hasClass("exclusive")) {jQuery(this).parent().parent().children("label").each(function() {jQuery(this).find("input").prop( "checked", false);}); jQuery(this).prop( "checked", true);}
            $html.= "<input name='komu' id='komu$id' data-value='$i' value='$id' type='checkbox'$on$alberice><label for='komu$id'>$nazev</label>";
          }
          $html.= "</div></div>";
          return $html;
        };
//     # ----------------------------------------------- . výběr kdy - minulost
//     $proc_kdy= function ($x,$level) {
//       global $def_pars,$href0;
//       $html= "<div id='vyber' class='x'>";
//       foreach($def_pars['bylo'] as $id=>$nazev_i) {
//         list($nazev,$i)= explode(':',$nazev_i);
//         $checked= strpos($x,$id)!==false ? ' checked="checked"' : '';
//         $on= " onclick='history_push(\"$href0$komu\",\"komu,bylo\",$level);'";
//         $html.= "<input type='radio' value='$id' name='bylo'$checked$on>$nazev</input>";
//       }
//       $html.= "</div>";
//       return $html;
//     };
        # specifické elementy
        switch ($ids) {

          case 'plan':  # ----------------------------------------------- . proc plan = komu
            //todo this is not probably called anymore
            //$vyber= array_shift($path);
            array_shift($path); //get rid of "komu_kdo" from path
            $submenu_komu.= $proc_kdo(false);
            break;

          case 'plan_rok':  # ------------------------------------------- . proc plan = komu
            $old_url_data = array_shift($path); //get rid of "komu_kdo" from path
            $vyb = explode(',',$old_url_data);
            $rok= '';
            foreach ($vyb  as $i=>$x) {
              if ( is_numeric($x) || $x=='nove' ) {
                $rok= $x;
                unset($vyb[$i]);
              }
            }

            //display("plan_rok:$vyber_rok+$rok");
            $submenu_komu.= $proc_kdo(false);
            break;

          case 'aplan':  # ---------------------------------------------- . proc aplan = alberice,bude
            $body .= "<div class='content'><h1>Plánované akce v domě</h1></div>";
            array_shift($path);
            break;

          case 'objednavky': # ------------------------------------------ . proc objednávky
            $body.= "<div id='clanek' class='x' onclick='history_back();'>
                 <div class='clanek x'>objednávky pobytů v Albeřicích</div>
               </div>";
            break;
          default:
        }
        break;
      default:
    }
  }
  end:
# --------------------------------------------------------------------------------------==> html
  $icon= array("cms/img/web_local.png","cms/img/web_test.png","cms/img/web_dsm.png",
      "cms/img/web_local.png","cms/img/web_local.png","cms/img/web_local.png")[$ezer_server];
  global $kernel;
  $setStyle= $kernel=='ezer3.1' ? 'css' : 'setStyle';
  $hash= $kernel=='ezer3.1' ? '#' : '';
  $dolar= $kernel=='ezer3.1' ? 'jQuery' : '$';

  $trace= "<!-- ------------------ trasování ------------------------ -->$trace";
  $xtrace= $echo ? $trace : '';
  $popup= '';
  if ( $popup_tit ) {
    $style= $switch= '';
    if ( $popup_typ=='order' ) {
      $attrs= " style='display:none' ";
      $popup= "
      <!-- order -->
      <div id='order' class='order_popup' $attrs>
        <span id='order_tit'>$popup_tit</span>
        <div id='order_div'>
        $popup_vol
        </div>
      </div>";
    }
    else {
      if ( $popup_typ=='foto' ) {
        $atrs= "position:absolute;top:1em;width:34%;height:100%;opacity:.5;color:white;font-size:2em;padding-top:30%";
        $switch= "<div onclick='foto_next(-1);' style='$atrs;padding-left:2em'>
                  <i class='fa fa-backward'></i></div>
                <div onclick='foto_back();' style='$atrs;right:34%;text-align:center'>
                  <i class='fa fa-stop'></i></div>
                <div onclick='foto_next(1);' style='$atrs;right:1em;text-align:right;padding-right:2em;'>
                  <i class='fa fa-forward'></i></div>";
        $attrs= " style='display:none' data-foto-lst='$popup_vol'";
        $popup_vol= '';
      }
      $action = "go(arguments[0],'$popup_bck','$popup_bck');";
      $popup="<div id='popup'$attrs>
        <span onclick=\"$action\" >$popup_tit</span><i class='fa fa-times popup_close' onclick=\"$action\"></i>
        <div id='popup_div' onclick=\"$action\" >
          $popup_vol
        </div>
        <div id='popup_bot'>popiska</div>
        $switch
      </div>";
    }
  }
// předání kontextu pro FE
//$Ezer_web= "index:'index.php',"; $del= '';
  $Ezer_web= ""; $del= '';
  if ( isset($_SESSION['web']) ) foreach ($_SESSION['web'] as $wi=>$w) {
    $Ezer_web.= "$del\n  $wi:'$w'";
    $del= ',';
  }

  /** ==========================================> HTML */
// samostatná hlavička
  $base= array(
      "http://setkani.bean:8080",
      "https://www.setkani.org",
      "http://setkani4.doma",
      "http://setkani4.bean:8080",
      "http://setkani4m.bean:8080",
      "http://setkani4.ide"
  );
  $base= $base[$ezer_server];
  global $kernel;
  $onLoad= $kernel=='ezer3.1'
      ? "jQuery(document).ready( function() { fe_init(); });"
      : "window.addEvent('load', function() { fe_init(); });";
// Google Analytics
  $GoogleAnalytics= $ezer_server!=1 ? '' : <<<__EOD
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-99235788-1', 'auto');
    ga('send', 'pageview');
__EOD;
  $cookieSettings = COOKIE_JS_PROPERTIES;
  $head=  <<<__EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "https://www.w3.org/TR/html4/strict.dtd">
<html lang="cs-CZ">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
  <meta http-equiv="X-UA-Compatible" content="IE=9" >
  <meta name="viewport" content="width=device-width,user-scalable=yes,initial-scale=1" >
  <base href="$base" >
  <title>YMCA Setkání</title>
  <link rel="shortcut icon" href="$icon" >
  <script type="text/javascript">
      window.COOKIE_PROPERTIES = '$cookieSettings';
  </script>
  
  $eb_link
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C700%2C700i%2C800%2C800i&amp;ver=0.3.5" type="text/css" media="all">
<link rel="stylesheet" href="cms/web.css?v=5.1" type="text/css" media="screen" charset="utf-8">
  <script type="text/javascript">
    var Ezer={web:{ $Ezer_web},cms:{form:{}}};
    if ( !console ) {
      var console={};
      console.log= function(){};
    }
    $GoogleAnalytics
    $onLoad
  </script>
</head>
__EOD;

// SSL viditelný
  $navrcholu= $ezer_server!=1 ? '' : <<<__EOD
  <a href="https://navrcholu.cz/Statistika/75293/" class="mereni" target='statistika'>
    <img src="https://s1.navrcholu.cz/hit?site=75293;t=o14;ref=;jss=0"
      width="14" height="14" alt="NAVRCHOLU.cz" style="border:none" />
  </a>
__EOD;

// menu pro změnu vzhledu, přihlášení ...
  $choice_js= "bar_menu(arguments[0],'menu_on'); return true;";
  $loginout= ($fe_user || $be_user)
      ? "<span onclick=\"be_logout('$currpage');\" class='separator'>
       <i class='fa fa-power-off'></i>&nbsp; odhlásit se</span>"
      : "<span onclick=\"bar_menu(arguments[0],'fe_login');\" class='separator'>
       <i class='fa fa-user'></i>&nbsp; přihlásit se</span>"
      . ( ip_watch($ip,0)
          ? "<span onclick=\"bar_menu(arguments[0],'be_login');\">
           <i class='fa fa-user-plus'></i>&nbsp; přihlásit se k editaci</span>" : ''
      )
      . ( 1 // ip_watch($ip,0)
          ? "<span onclick=\"bar_menu(arguments[0],'me_login');\">
           <i class='fa fa-user-secret'></i>&nbsp; přihlásit se emailem</span>" : ''
      );
  $menu= "
  <span id='bar_menu' data-mode='$mode[1]' >
    <i class='fa fa-bars' id='bar_image' onclick=\"$choice_js\" oncontextmenu=\"$choice_js\"></i>
  
    <div id='bar_items' class='mobile_nodisplay'>
      <span onclick=\"bar_menu(arguments[0],'new1');\"><img src='cms/img/new.png'> změny za den</span>
      <span onclick=\"bar_menu(arguments[0],'new7');\"><img src='cms/img/new.png'> změny za týden</span>
      <span onclick=\"bar_menu(arguments[0],'new30');\"><img src='cms/img/new.png'> změny za měsíc</span>
      <span onclick=\"bar_menu(arguments[0],'grid');\" class='separator mobile_nodisplay'><i class='fa fa-th'></i> akce jako mřížka</span>
      <span onclick=\"bar_menu(arguments[0],'rows');\" class='mobile_nodisplay'><i class='fa fa-bars'></i> akce jako řádky</span>
      $loginout
    </div>
  </span>";

// body
  $footer = footer();
  $cur_year = date('Y');

  $notice = $CMS ? tutorial(!isset($_COOKIE["article_tutorial"]) && !$_COOKIE["article_tutorial"]) : "";
  $body= <<<__EOD
  <div id='page'>
    <div class="pc_nodisplay" style="height: 30px"><!--Space for mobile devices - menu always fixed, shift content--></div>
    $notice
    $body
    
    
   
    <div id='user_login' style="display:$fe_user_display">
      <span>Přihlášení uživatele</span>
      <div id="login_err"></div>
      <input name='username' placeholder='Uživatelské jméno' id='name' value='' type='text'>
      <br><input id='pass' placeholder='Heslo' type='password' value=''>
      <input id='akey' type='hidden' value='-'>
      <br>
      <button id='fe_zpet' onclick="$dolar('{$hash}user_login').$setStyle('display','none'); $dolar('{$hash}web-shadow').$setStyle('display','none');">Zpět</button>
      <button id='fe_login' onclick="fe_login('$currpage');">Přihlásit se</button>
    </div>
    <div id='user_mail' style="display:$fe_user_display">
      <span>Přihlášení uživatele</span>
      <div id='user_mail_txt'>Napiš svoji mailovou adresu, na kterou ti dojde mail s PINem,
        který ti zpřístupní např. fotky z akcí, kterých ses zúčastnil ...</div>
      <input id='mail' type='text' title='mailová adresa' placeholder='emailová adresa'>
      <input id='pin' type='text' title='PIN z došlého mailu' placeholder='PIN'>
      <br>
      <button id='me_zpet' style='position:initial'
        onclick="$dolar('{$hash}user_mail').$setStyle('display','none'); $dolar('{$hash}web-shadow').$setStyle('display','none');">Zpět</button>
      <button id='me_login' style='position:initial'
        onclick="me_login('$currpage');">Přihlásit</button>
    </div>
    <div id='user_msg' style="display:none">
      <span></span>
      <div></div>
      <button onclick="jQuery('#user_msg').hide();" style="margin:0">Ok</button>
    </div>
  </div>

  $footer 
    
  <div id="page_footer_bar" class="container footer" style="background:black">
    <div class="content white" style="paddingcode: 0 12px">
      $navrcholu            
      Vaše dary na účet <img src="cms/img/bank.png" class="footerIcon">  <b>2400465447/2010</b> nám pomáhají uskutečňovat naše programy pro vás i vaše blízké
      <span id='site_signature'> YMCA Setkání, $cur_year&emsp;</div>
      <div>$info_note</div>
    </div>
  </div>
  <div id='msg' class='box' style="display:none">
    <div class='box_title'>title</div>
    <div class='box_text'>text</div>
    <div class='box_ok'><button onclick="box_off();">OK</button></div>
  </div>
  <div id='table_choose' class='box' style="display:none">
    <div class='box_title'>title</div>
    <div class='box_text'>text</div>
    <div class='box_ok'>
      <button onclick="table_chng('del');" style='position:relative'>Odstranit</button>
      <button onclick="table_chng('mov');" style='position:relative'>Přesunout</button>
    </div>
  </div>
  <div id='prompt' class='box' style="display:none">
    <div class='box_title'>Doplň příjmení: Richard ...</div>
    <div class='box_input'>
      <input type='text' title='místo pro test' onchange="_table_test(this.value);">
    </div>
  </div>
  $popup
  $xtrace
__EOD;

// změření času od $microtime_start (pro web a cms je jinde)
  global $page_kb, $page_ms;
  $page_kb= round(strlen($body)/1024);

  $microtime_end= microtime();
  $ms= "<div id='info_screen'>1280*780</div>";
  $page_ms= gn_militime($microtime_end) - gn_militime($microtime_start);
  $ms.= "$page_ms ms";
// informace o přihlášení
  $ms.= " $fe_user/$be_user";
  $ms.= $fe_host ? '<br>host' : '';
  $ms.= $username ? "<br>$username" : '';
  $ipok= ip_watch($ip,0) ? '+ ' : '- ';
  $ms.= "<br>$ipok$ip";
  $ms.= $found ? "<br>$found" : ''; // počet nalezených článků, knih, ...

  $gallery = gallery();

// dokončení stránky
  if ( $echo ) {
    $info= "$page_kb KB, $page_ms ms";
    echo <<<__EOD
$head 
<body>
    <div id='web-shadow'></div>
    $gallery 
    <a href="/"><img id='logo_ymca' src='cms/img/ymca_zakladni.png' alt='YMCA Setkání - homepage'></a>
    <a href="/"><img id='logo_setkani' src='cms/img/husy_bile.png' onclick="change_info();"  alt='YMCA Setkání - homepage'></a>
    <div id='menu'>
      <div class='content'>
        <div id='page_tm' class='x'>
          $topmenu    
          $menu      
        </div>
        <div id='page_hm' class='x mobile_nodisplay'>
          $mainmenu          
        </div>
        <div class="clear"></div>
      </div>
    </div>
  
  <div id='mobile_menu' class="pc_nodisplay">
      <br>
 
      $mobile_mainmenu
      <div id="mobile_barmenu">
        <span onclick='bar_menu(arguments[0],"new1");'><img src='cms/img/new.png'>&nbsp; změny za den</span>
        <span onclick='bar_menu(arguments[0],"new7");'><img src='cms/img/new.png'>&nbsp; změny za týden</span>
        <span onclick='bar_menu(arguments[0],"new30 ");'><img src='cms/img/new.png'>&nbsp; změny za měsíc</span>
        $loginout
      </div>
  </div>
  <div id="menu-cross" class="nodisplay" onclick="close_mobile_menu(); return true;"></div>

  <div id='web' style="position: initial !important;" class='$web_banner container'>
    $submenu_komu
    <div id='info' style='display:none' onclick="change_info();"><br>$ms</div>
    $body
  </div>
  <div style='color:transparent;position:fixed;left:0;top:0' title='$info'>zatížení serveru</div>
</body>
</html>
__EOD;
  }
  else
    return <<<__EOD
    <div id='menu'>
      <div class='content'>
        <div id='page_tm' class='x'>
          $topmenu
          $menu
        </div>
        <div id='page_hm' class='x mobile_nodisplay'>
          $mainmenu
        </div>
        <div class="clear"></div>
      </div>
    </div>
    
    <div id='mobile_menu' class="pc_nodisplay">
      $mobile_mainmenu
      <div id="mobile_barmenu">
        <span onclick='bar_menu(arguments[0],"new1");'><img src='cms/img/new.png'> změny za den</span>
        <span onclick='bar_menu(arguments[0],"new7");'><img src='cms/img/new.png'> změny za týden</span>
        <span onclick='bar_menu(arguments[0],"new30");'><img src='cms/img/new.png'> změny za měsíc</span>
        $loginout
      </div>
    </div>
    <div id="menu-cross" class="nodisplay"  onclick="close_mobile_menu(); return true;"></div>

    <div id='web' class='$web_banner'>
      $submenu_komu
      <div id='info' onclick='change_info();'><br>$ms</div>
      $body
    </div>
__EOD;
} // end template()

function tutorial($doDisplay = true) {
  $positive = $doDisplay ? "" : "nodisplay";
  $negated = $doDisplay ? "nodisplay" : "";
  return "
  <div id='article_button' class='content mobile_nodisplay $negated'> 
     <div style='cursor: pointer; text-align: right; margin-top: 10px' onclick='jQuery(\"#article_tutorial\").toggleClass(\"nodisplay\"); jQuery(\"#article_button\").toggleClass(\"nodisplay\");'>
      <span style='padding: 6px 12px; background-color: #efbf54'>Ukázat návod 'Zásady psaní pěkných článků'.</span></div >
      </div>
  <div id='article_tutorial' class='content mobile_nodisplay $positive' style='background: #efbf54; padding: 12px; padding-bottom: 25px;'>
  <h2>Zásady psaní pěkných článků</h2> 
  <ul>
  <li><b>Nepoužívejte</b> pouze <b>velká písmena</b>. Je to obecně chápano jako KŘIČENÍ/NADÁVÁNÍ, nechtěli byste, aby na vás články křičely.</li>
  <li>Pozor na málo nadpisů. <b>Každá logická část textu by měla mít nadpis</b>. Jinak se v těxtu špatně orientuje. Použijte menší styl, pokud si nejste jistí, vypadá to lépe (například ten největší bude použit na titulek a jen zřídka jej chcete znovu použít v textu).</li>
  <li><b>Pozor na dlouhé nebo velký počet napisů</b>. Pokud je výrazná část příliš dlouhá nebo je nadpisů přiliš mnoho, text vypadá přeplácaný.</li>
  <li><b>Nepoužívejte</b> ASCII art (<b>vykreslování či pozicování pomocí znaků</b>). Většinou se rozbije kvůli různým velikostem písem, stylů fontů atd.
    <ul>
      <li><b>Na ceníky použijte tabulky.</b> Zadejte jen počet sloupců/řádků a nezapomeňte si přidat záhlaví.</li>
      <li>Místo -------- a podobných odsazení <b>použijte vodorovnou linku</b> (hned vedle 'obrázek').</li>
      <li>Na odsazení textu používejte bloky (odrážky, zarovnání..) a ne bílé mezery. Pokud vám chybí nějaký prvek, dejte vědět programátorům.</li>
      <li><b>Používejte styly</b> (vlevo nahoře v editoru). <b>Klikněte na objekt</b>, u kterého chcete změnit vzhled <b>a vyberte ze sekce 'Objektové styly'</b> svůj vlastní. Tyto styly mizí a objevují se podle toho, kde je váš kurzor.</li>
         <ul>
             <li>Styly 'citace', 'upozornění' apod. nepodporují odřádkování - použijte vodorovnou linku (hned vedle ikony vložení obrázku).</li>
             <li><b>Nenadužívejte grafických zvýraznění textu</b> - platí to, co s nadpisy. Moc zvýrazňování = přeplácané.</li>
        </ul>
    </ul>
  </li>
  <li><b>Nepřehánějte to s různorodostí textu či vlastními styly:</b> <span style='color: red'>barevné</span>, <span style='background: #00FFFF'>strakaté</span> a <span style='font-family: \"Courier New\", Monospace; font-size: 18pt;'>různorodé</span> fonty jsou <b>fuj!</b> Méně je často více.</li>
 </ul>
 <p>&emsp;A hlavně: článek <b>začínejte vždy odstavcem textu</b>, alespoň dva-tři řádky. </p><p style='text-align: center'></p>
 <div id='article-example' style='background: white; margin: 0 auto; max-width: 850px; clear: both; '>
    <img src='cms/img/banner-tutorial.png' style='margin: 0; width: 100%;'/>
    <div style='padding: 0 15px 10px 15px; max-height: 450px; overflow-x: hidden; overflow-y: scroll;'>
     <p>Začínám odstavcem textu. Hlavní nadpis jsem vyplnil nahoře v kolonce 'název:' a chci, aby článek hned pod nadpisem (bude mít styl 'nadpis článku') pokračoval textem - jak z informačních, tak vizuálních důvodů.
     Nedávám nahoru žádné tabulky, pokud obrázek tak je obtékán textem zprava/zleva.
    U článku je důležité začít textem z několika důvodů: asi budete chtít představit/shrnout, o čem vlastně píšete. Ale hlavně bude tento text často
    zobrazen jako abstrakt u článků ('abstrakt', který máte možnost editovat, se používá na hlavní straně) - začněte tak zajímavě, jak to jen jde!
    </p>
    <h3>Povídání o textu a nadpisech</h3>
     <p>Všimněte si, že se v textu dobře orientuje, když je použit nadpis. A také si všimněte, že nepoužívám ten největší - ten bude u titulku a to stačí. Nadpis je krátký a zlepšuje
    orientaci v mém článku. </p>
    <img style='float: right; width: 263px' src='cms/img/foto_home.jpg' />
    <p>Nedávám nadpis podsekce hned pod nadpis sekce. Začnu textem. Podsekce se hodí až v případě, že mám pocit, že v dané sekci popisuji
    větší detaily a sekce se natahuje. Pokud tomu tak je, nadpis podsekce opět zlepší orientaci. Jinak zvýší jen přeplácanost.</p>
    <p>Zde už je dobré (na rozdíl od úvodu) dát nějaký obrázek a trochu si pohrát s jeho pozicí a velikostí. Udělá to text vzdušnější a líbivější. Obrázek se hodí k odstavci s velkým množstvím textu.
    S nadpisy to nepřeháním, dávám nový až v momentě, kdy se přesunu k další části článku. Dělám větší odstavce, pokud to jde. Píšu smysluplné věty, ne výtah z textu. Pokud chci použít méně textu
     pro informativní účely, použiji odrážky místo jednořádkových odstavců. Pro odsazení textu použiji ikonu označenou číslem 2. Celkově snažím o to, aby text objímal elementy,
    které do něj přidávám a působil dojmem, že všechno je umistěno tam, kde to má být, a má to k tomu svůj důvod.</p>
    <h3>ASCII art</h3>
    <div class='pquote'>Zde se budu snažit na co nejkratším kousku textu představit všemožné možnosti vyhýbání-se ascii art.<hr><span style='float: right; font-style: italic;'>Jirka</span></div>
    <p>Napsal jsem výše uvedený text, pak jej označil a ve stylech (1.) zvolil 'citace'. Jenže jsem zapomněl přidat svůj podpis! Nevadí, použiji vodorovnou linku
    pro nový řádek (4.), podepíšu se, nastavím zarovnání doprava a kurzívu.</p>
    <h4>Ceníky a tabulky</h4>
    <p>Kliknutím na ikonu tabulky (3.) přidám tabulku do textu. Jediné, co musím nastavit, je počet řádků/sloupců, šířku a zda chci záhlaví (většinou ano).
    Tyto vlastnosti lze kdykoliv měnit - stačí kliknout pravým tlačítkem na tabulku. Kromě 'vlastnosti tabulky' nabídka umožňuje i složitější operace typu
    slučování buněk atp.</p>
    <img src='cms/img/tabulky-tutorial.png' style='display: block; margin: 0 auto; max-width: 100%'/>
     <p>Vlastní ceník tedy netvořím mezerami, ale v tabulce. Ta se sama přizpůsobí obsahu. Pokud se mi nelíbí normální tabulka s černými okraji (obrázek
     uprostřed), kliknu někam do tabulky a zvolím jiný styl. Ceník tak bude <b>pěknější a výraznější</b>.</p>
     <table border=\"1\" class=\"table3\" style=\"width:500px;margin: 0 auto;\">
      <thead><tr>
          <th scope=\"col\">Položka</th>
          <th scope=\"col\">Cena</th>
          </tr>
      </thead>
      <tbody><tr><td>Za manželský pár celkem</td><td style=\"text-align:center\">8840,-</td></tr><tr><td colspan=\"2\"><strong>Dítě od 6let</strong></td></tr><tr><td>lůžko, celá porce, pečovatel+program</td><td style=\"text-align:center\">4280,-</td></tr><tr><td>lůžko, dětská porce, pečovatel+program&nbsp;</td><td style=\"text-align:center\">3960,-</td></tr>
      </tbody>
      </table>
     <br>
     <hr>
     <p>V případě, že chci následující část textu oddělit od předchozí, použiji vodorovnou linku (4.). 
     Většinou se ale musím zamyslet: chci použít linku nebo nadpis? Oddělení znamená říci 'a teď jdu psát o něčem jiném'.
     Zde by se místo linky mnohem více hodil nadpis 'Vodorovné linky', podobně jako je to u 'Ceníky a tabulky'. Kromě toho, 
     že také text oddělí, navíc sdělí čtenáři o čem bude následující část.
     <h3>Závěrem...</h3>
     <p>A mohl bych takto pokračovat dál. Děkuji, že jste se prokousali až sem. Doufám, že se nám společně podaří udržet obsah
     našeho webu líbivý a čtivý, abychom mohli dál růst.</p>
     </div>
 </div>
      <br>
      <div style='float: left'>S problémy se obracejte na <i>horakj7@gmail.com</i>.</div>
      <div style='cursor: pointer; float: right' onclick='document.cookie=\"article_tutorial=true; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=\"; jQuery(\"#article_tutorial\").toggleClass(\"nodisplay\"); jQuery(\"#article_button\").toggleClass(\"nodisplay\");'>
      Všechno to už vím. Dám ruku do ohně za svoje články [skrýt].</div>
 </div>";
}

# -------------------------------------------------------------------------------------- gn militime
function gn_militime($microtime) {
  // konvertuje čas získaný funkcí microtime() na milisekundy
  $parts = explode(' ',$microtime);
  return round(($parts[0]+$parts[1])*1000);
}
# ------------------------------------------------------------------------------------------ ms from
function ms_from($microstart) {
  // vrátí interval v ms od startu doteď
  $microend= microtime();
  return gn_militime($microend) - gn_militime($microstart);
}

/** ====================================================================================> JAVASCRIPT */
function javascript_init() {
  global $eb_link,$kernel;
//  $mootools= isset($_SESSION['web']['try']) && $_SESSION['web']['try']=='prihlasky'
//          || isset($_GET['try']) && $_GET['try']=='prihlasky'
//  ? <<<__EOJ
//    <link rel="stylesheet" href="ezer3/client/ezer_cms3.css" type="text/css" media="screen" charset="utf-8">
//    <script src="ezer3/client/ezer_cms3.js" type="text/javascript" charset="utf-8"></script>
//    <script src="cms/MooTools-Core-1.6.0-compressed.js" type="text/javascript" charset="utf-8"></script>
//__EOJ
//  : <<<__EOJ
//    <script src="cms/MooTools-Core-1.6.0-compressed.js" type="text/javascript" charset="utf-8"></script>
//__EOJ;
  // on-line přihlášky
  $cms_root= $kernel=='ezer3.1' ? 'ezer3.1' : 'ezer3.2';
  $k3= $kernel=='ezer3.1' ? '3' : '3';
  $framework= '';
  if ( $kernel<'ezer3.1' )
    $framework= <<<__EOJ
    <script src="cms/fotorama/jquery-1.12.4.min.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">jQuery.noConflict();</script>
    <script src="cms/MooTools-Core-1.6.0-compressed.js" type="text/javascript" charset="utf-8"></script>
__EOJ;
  else
    $framework= <<<__EOJ
    <script src="ezer3.1/client/licensed/jquery-3.3.1.min.js" type="text/javascript" charset="utf-8"></script>
__EOJ;
  $fotorama= <<<__EOJ
    <script src="cms/fotorama/fotorama.js" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="./cms/fotorama/fotorama.css" type="text/css" media="screen" charset="utf-8">
__EOJ;
  $eb_link= <<<__EOJ
    $framework    
    <script src="cms/cms{$k3}.js?v=5.1" type="text/javascript" charset="utf-8"></script>
    <script src="cms/cms{$k3}_fe.js?v=5.0" type="text/javascript" charset="utf-8"></script>
    <script src="cms/modernizr-custom.js?v=5.0" type="text/javascript" charset="utf-8"></script>
    $fotorama
    <link rel="stylesheet" href="./$kernel/client/licensed/font-awesome/css/font-awesome.min.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="$cms_root/client/ezer_cms3.css?v=5.0" type="text/css" media="screen" charset="utf-8">
    <script src="$cms_root/client/ezer_cms3.js?v=5.0" type="text/javascript" charset="utf-8"></script>
    <script src="cms/custom.js?v=5.0" type="text/javascript" charset="utf-8"></script>
__EOJ;
//     <link rel="stylesheet" href="cms/gallery/baguetteBox.min.css">
//     <script src="cms/gallery/baguetteBox.min.js" async>
//     <script src="cms/MooTools-More-1.6.0-compat.js" type="text/javascript" charset="utf-8"></script>
}
/** =======================================================================================> SETKANI */
# ------------------------------------------------------------------------------------------ cid pid
# v režimku CMS zobrazí ladící informace
function cid_pid($cid,$pid) {
  global $CMS;
  $code= '';
  if ( isset($_SESSION['cms']['cid_pid']) && $_SESSION['cms']['cid_pid'] ) {
    $event= "onclick=\"cms_cid_pid(arguments[0],'$cid','$pid');\"";
    $code= $CMS ? "<div class='code' $event>$cid/$pid</div>" : '';
  }
  return $code;
}
# ------------------------------------------------------------------------------------- cid pid chng
# v režimku CMS zobrazí ladící informace včetně času poslední změny
function cid_pid_chng($cid,$pid,$chng) {
  global $CMS, $fe_user;
  $code= '';
  if ( $fe_user==1 ) {
    $event= "onclick=\"cms_cid_pid(arguments[0],'$cid','$pid');\"";
    $code= $CMS ? "<div class='code' $event>$cid/$pid/$chng</div>" : '';
  }
  return $code;
}
# =======================================================================================> part,case
# ---------------------------------------------------------------------------------==> . delete part
# pokud part $pid je poslední smaže i case
function delete_part($pid,$delete,$on) {
  $ask= 1;
  list($cid,$tags,$nadpis,$obsah)= select("cid,tags,title,text","tx_gncase_part","uid=$pid");
  if ( $delete ) {
    $cid= select("cid","tx_gncase_part","uid=$pid");
    if ( $on ) {
      query("UPDATE tx_gncase_part SET deleted=1 WHERE uid=$pid");
      $n= select("COUNT(*)","tx_gncase_part","cid=$cid AND !deleted");
      if ( !$n )
        query("UPDATE tx_gncase SET deleted=1 WHERE uid=$cid");
    }
    else {
      query("UPDATE tx_gncase_part SET deleted=0 WHERE uid=$pid");
      query("UPDATE tx_gncase SET deleted=0 WHERE uid=$cid");
    }
  }
  else {
    // jen se zeptej
    $ask= "Mám opravdu ".($on ? "smazat " : "obnovit");
    switch ($tags) {
      case 'F':
        $ask.= " seznam <b>$nadpis</b> s ".substr_count($obsah,',,')." fotkami?";
        break;
    }
  }
  return $ask;
}
# -----------------------------------------------------------------------------------==> . hide part
function hide_part($uid,$hide,$on) {
  $ask= 1;
  list($cid,$tags,$nadpis,$obsah)= select("cid,tags,title,text","tx_gncase_part","uid=$uid");
  if ( $hide ) {
    query("UPDATE tx_gncase_part SET hidden=$on WHERE uid=$uid");
  }
  else {
    // jen se zeptej
    $ask= "Mám opravdu ".($on ? "skrýt" : "zviditelnit");
    switch ($tags) {
      case 'F':
        $ask.= " seznam <b>$nadpis</b> s ".substr_count($obsah,',,')." fotkami?";
        break;
    }
  }
  return $ask;
}

# ------------------------------------------------------------------------------------ wp post_request
/**
 * Upload an article by CID onto wordpress subsite
 * only FIRST record in tx_gncase_part is uploaded
 * @param $site_name "ms" for MS gallery, "dum" for DS gallery
 * @param $process 0 if ask only, 1 if fire request
 * @param $cid the post cid
 * @param $update 0 if already created - used only for better messages ($process = 0)
 * @return string result message
 * @throws Exception
 */
function wp_upload($site_name, $process, $cid, $update) {
  global $wp_upload_token;
  //todo the url might change in future, if main web becomes used

  $url = "http://manzelska.setkani.org/extern/upload_article.php";
  switch ($site_name) {
    case "dum":
      $site = 2;
      $web = "Galerie Domu";
      break;
    case "ms":
      $site = 3;
      $web = "Galerie MS";
      break;
    default:
      return "Neplatný web :$site_name";
  }

  if (!$process) {
    if ($update) return "Obsah kopie tohoto článku na webu $web bude nahrazen novou verzí. Pokračovat?";
    return "Opravdu chcete zkopírovat tento článek na web $web?";
  }

  global $usergroups; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $news_time;
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $cr= pdo_qry("
      SELECT p.title,p.abstract,p.text,p.author,date
      FROM setkani4.tx_gncase AS c
      JOIN setkani4.tx_gncase_part AS p ON c.uid=p.cid
      WHERE cid='$cid' AND !p.hidden AND !p.deleted $groups
      ORDER BY tags,date
    ");

  $msg = "";
  while ( $cr && (list($title,$abstrakt,$text,$autor,$psano) = pdo_fetch_row($cr)) ) {
    $text= web_text($text);

    if (strlen($title) < 3 || strlen($text) < 20) return "Prosíme, sdílejte pouze plnohodnotné články. Nadpis nebo obsah nejsou dostatečně vyplněny.";

    //get rid of absolute paths (so next call won't make them '2x absolute'
    $text = preg_replace("#https://(www.)?setkani.org/fileadmin/img#", "/fileadmin/img", $text);
    //covert everything to absolute paths
    $text = preg_replace("#/?fileadmin/img/#", "https://www.setkani.org/fileadmin/img/", $text);

    $msg = cms_post_request(
        $url,
        array(
            "token" => $wp_upload_token,
            "target_web_id" => $site,
            "cid" => $cid,
            "title" => $title,
            "content" => $text,
            "excerpt" => $abstrakt,
            "date" => $psano)
    );
    if (!$msg) $msg = "Operace byla dokončena.";
    break; //todo post only first article? probably Yes, or somehow merge the content
  }
  if (!$msg) $msg = "Tento článek není možné sdílet (je pravděpodobně zablokovaný nebo jinak zabezpečený).";
  return $msg;
}

# =========================================================================================> GALLERY
# nageneruje fotky do záhlaví
# zatím samostantná tabulka, neví kde má brát fotky
function gallery_images() {
  $directory = "fileadmin/index";
  return glob("$directory/*.{jpg,png,bmp}", GLOB_BRACE);
}
function gallery() {
  $images = gallery_images();
  $result = "<div id='header_gallery' class='mobile_nodisplay'>";

  $numOfImages = count($images);
  if ($numOfImages > 0) {
    shuffle($images);

    $result .= "<img alt='YMCA setkání' src='$images[0]' class='act'>";
    for ($i = 1; $i < $numOfImages; $i++) {
      $result .= "<img alt='YMCA setkání' src='$images[$i]' style='display:none'>";
    }
    $defaultImage = "style='background-image: url(\"{$images[0]}\");";
  } else {
    $defaultImage = "";
  }
  return $result .
      "</div><div id='titler' class='mobile_nodisplay'></div><div id='gallery_shadow' class='mobile_nodisplay'></div>
        <div id='header_mobile_image' class='pc_nodisplay' $defaultImage'><div id='header_mobile_image_shadow'></div></div>";
}
function facebook_dependency() {
  return "<div id=\"fb-root\"></div>
        <script async defer crossorigin=\"anonymous\" src=\"https://connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v5.0\"></script>";
}

# =========================================================================================> FACEBOOK BAR
function facebook() {
  return <<<__EOD
    <div class='full_width facebook' id="vlakno">
      <div class='content centered_infull'>
          <div class="fb_bar">
            <div class="fb_bar">
                <div class="fb_bar_icon">
                    <i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i>
                </div>
                <div class="fb_bar_links">
                    <div>
                        <a href="https://www.facebook.com/manzelska.setkani.org">fb.com/manzelska.setkani.org</a>
                    </div>
                    <div>
                        <a href="https://www.facebook.com/dum.setkani.org">fb.com/dum.setkani.org</a>
                    </div>
                </div>
            </div>
            <div class="fb_bar">
                <div class="fb_bar_icon ">
                    <i class="fa fa-instagram fa-2x" aria-hidden="true"></i>
                </div>
                <div class="fb_bar_links">
                    <div>
                        <a href="https://www.instagram.com/manzelska.setkani.ymca">instagram.com/manzelska.setkani.ymca</a>
                    </div>
                    <div>
                        <a href="https://www.instagram.com/dum.setkani.ymca">instagram.com/dum.setkani.ymca</a>
                    </div>
                </div>
            </div>
          </div>
      </div>
  </div>
__EOD;
}
# ========================================================================================> TIMELINE
# nageneruje fotky do záhlaví
# zatím samostantná tabulka, neví kde má brát fotky
# ========================================================================================> TIMELINE
# nageneruje fotky do záhlaví
# zatím samostantná tabulka, neví kde má brát fotky
function timeline()
{
  trace();
  global $def_pars;
  global $usergroups; // skupiny, počet nalezených článků
  // překlad $komu na regexpr

  $groups = $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  //AND LEFT(FROM_UNIXTIME(fromday) + INTERVAL 1 MONTH,10) >= LEFT(FROM_UNIXTIME(untilday),10)
  $qry = "
        SELECT p.uid, c.uid, p.title, p.text, fromday, untilday, c.program, p.id_akce, status
        FROM setkani4.tx_gncase AS c
        JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
        LEFT OUTER JOIN ezer_db2.akce AS a ON p.id_akce=a.id_duakce
        WHERE !c.deleted AND !c.hidden AND !p.hidden AND !p.deleted AND status!=1 AND (a.zruseno IS null OR a.zruseno!=1)
          $groups
          AND LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)
          AND tags='A'
        ORDER BY fromday, untilday
      ";
  $cr = pdo_qry($qry);
  $max_date = 0;
  while ($cr && (list($p_uid, $cid, $title, $text, $uod, $udo, $program, $ida, $status) = pdo_fetch_row($cr))) {
    $text = x_shorting($text);
    $max_date = max($max_date, $udo);
    $xx[$cid] = (object)array('ident' => $p_uid, 'od' => $uod, 'do' => $udo, 'nadpis' => $title,
        'text' => $text, 'program' => $program, 'ida' => $ida, 'status' => status_class($status));
  }

  $h = "<br><br><br><h2 class='float-left mobile_text_shadow' style='margin-top: 0;'>Chystáme <span class='mobile_nodisplay' style='transform: translate(0px, -2px);
    display: inline-block;'>&emsp;| přihlašte se na nadcházející akce:</span></h2><div class='float-right legend'>";
  foreach ($def_pars['komu'] as $ki) {
      list($k, $i) = explode(':', $ki);
      if ($i==6) $k="ostatní";
      $bgcolor = barva_programu_z_cisla($i);
      $h .= "<span class='timeline_legend' style='background: $bgcolor;'>$k&emsp;</span>&emsp;";
  }
  $h.= "</div><div class='relative clear'><div class='horizontal_scroll'><div class='relative'><ul id='timeline_header'>";
  $day = 24 * 3600;
  $day_size = 7;
  $month_size = $day_size * 30;

  $to_end_month = 30 - date('j', time());
  $time = time() + ($to_end_month + 1/*sichr*/) * $day;
  $month_gap = $to_end_month *$day_size;
  $month = date('n', $time);
  $month_name = czechMonth($month);
  $h .= "<li class='timeline_month' style='left: ${month_gap}px'>$month_name</li>";

  do {
    $month++;
    $time += numOfDaysInMonth($month) * $day;
    $month_gap += $month_size;
    $month_name = czechMonth(date('n', ($time + 12*$day)));
    $h .= "<li class='timeline_month' style='left: ${month_gap}px'>$month_name</li>";
  } while ($time < $max_date);

  $h .= "</div></ul><ul id='timeline'>";
  $n = 0;
  foreach ($xx as $cid => $x) {
    $n++;
    $dateCzech = datum_cesky($x->od, $x->do);
    $jmp = "href='/akce/nove/$x->ident'/";
    $in_time = $x->od - time();
    if ($in_time < 0)  {
      $in_time = 0;
    }
    $ends_in = $x->do - time();
    $duration = $ends_in - $in_time;
    $width = ($duration / $day) * $day_size;
    $gap = ($in_time / $day) * $day_size;
    //
    $color = ($x->zruseno) ? "#000000"  : barva_programu($x->program);
    $date = datum_cesky($x->od, $x->do);
    $h .= <<<EOF
<li>
  <input class="timeline_radio" id="akce$n" name="akce" type="radio">
  <label  for="akce$n"  class="timeline_circle $x->status" onclick="(function(){
    if (!focusElement(document.getElementById('pozvanka-masonry-{$x->ident}'))) {
      var radio = document.getElementById('akce$n');
      radio.checked = !radio.checked;
    }
  })();return false;"style="margin-left: ${gap}px;">
  <label class="timeline_title" for="akce$n">$x->nadpis</label>
  <span class="timeline_date">$date</span>
  <div class="timeline_text">
     <div class="timeline_text_style">
        <span class="post_date">$dateCzech</span>
        <b style="float:left;"><a $jmp>$x->nadpis</a></b><br>
        <p class="clear"><a $jmp>$x->text</a></p>
     </div>
  </div>
  </label>
  <div class="timeline_bar" style="width:${width}px;margin-left: ${gap}px; background:${color}"></div>
</li>
EOF;
  }
  $h .= "</ul></div></div>";
  return $h;
}

# ==========================================================================================> FOOTER
# footer ze své vlastní tabulky
function footer() {
  global $CMS, $href0, $mode;
  trace();
  $contacts = '';
  $organizations = '';
  $query = pdo_qry("
    SELECT title, text, part
    FROM setkani4.footer
    ORDER BY part, sorting DESC
  ");

  $menu= $CMS
      ? " oncontextmenu=\"
          Ezer.fce.contextmenu([
            ['editovat patičku',function(el){ opravit('footer',0,0); }]
          ],arguments[0],'page_footer_info');return false;\""
      : '';

  while ( $query && (list($title, $text, $part) = pdo_fetch_row($query)) ) {
    $text= preg_replace("/{(.*)}/","<i class='fa fa-$1'></i>",$text);
    if ($part == 'C') {
      $contacts .= "<div class='tile'><h3>$title</h3>$text</div>";
    } else if ($part == 'O') {
      $organizations .= ($title) ? "<h3>$title</h3>" : "" . $text;
    }
  }
  return "<div id='page_footer_info' class='container footer'$menu><div id='page_footer_info_overlay'>
            <span class='anchor' id='footer_contacts'></span>
            <div class='content white'>
              <div id='footer-left-part'>
                 $contacts
              </div>
              <div id='footer-right-part'>
                <div class='organizations'>
                  <h3> SPŘÁTELENÉ ORGANIZACE</h3>
                  $organizations
                </div>
              </div>
            </div>
            </div>
          </div>";
}

# ============================================================================================> HOME
# homepage je tvořena výběrem záznamů v CASE,PART podle hodnoty homepage
# --------------------------------------------------------------------------------------------- home
# vybrané stránky na home-page
# může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
function home() { trace();
  global $CMS, $def_pars, $href0, $clear, $usergroups;
  global $show_deleted, $show_hidden, $news_time;
  $now = time();
  $xx= array();
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $cr= pdo_qry("
    SELECT p.pid, p.uid, p.cid, c.mid, m.ref, m.mref, c.type, p.homepage, p.title, p.text, p.abstract,
      c.fromday, c.untilday, c.program, p.id_akce, a.web_prihlasky, p.status, tags,
      IF(c.tstamp>$news_time,IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),''),
      IF(LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10),'nove',YEAR(FROM_UNIXTIME(fromday)))
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    LEFT JOIN setkani4.tx_gnmenu AS m USING (mid)
    LEFT OUTER JOIN ezer_db2.akce AS a ON a.id_duakce=p.id_akce
    WHERE (tags='A' OR tags='D') AND !c.deleted AND !c.hidden $p_show
      AND (p.pid=100 OR (p.homepage>0 AND p.homepage NOT IN (5)))
      AND fe_groups IN ($usergroups)
      AND (homepage!=2 OR untilday > $now) 
    ORDER BY IF(p.pid=100,2,IF(homepage=6,0,1)),
    CASE
      WHEN homepage IN (2) THEN untilday
      WHEN homepage IN (1,6,7,8) THEN -c.sorting
      ELSE 0
    END,
    untilday DESC
  ");

  $num_of_present_articles = 0;
  while ($cr && (list($page,$uid,$cid,$mid,$ref,$mref,$type,$home,$title,$text,
          $abstract,$uod,$udo,$program,$ida,$prihlaska,$status,$tags,$upd,$rok)= pdo_fetch_row($cr))) {
    //if ($status==1) continue; //zruseno

    $kdy = "";
    $text = "";
    if ( $page!=100 ) {
      $text= $abstract && mb_strlen($abstract)>10
          ? web_text($abstract)
          : x_shorting($text);
      if ($home != 2 && $home != 6) $num_of_present_articles++;
    } else {
      $text = web_text($text);
    }

    if ( $type==2 ) {
      $kdy= "<span class='post_date'>". datum_cesky($uod,$udo) . "</span> ";
    }
    $xx[$cid]= (object)array('uid'=>$uid,'type'=>$type,'page'=>$page,'mid'=>$mid,'ref'=>$ref,'mref'=>$mref,'ida'=>$ida,'prihlaska'=>$status==1 ? null : $prihlaska,
        'status'=>status_class($status),'nadpis'=>$title,'abstract'=>$abstract, 'text'=>$text,'home'=>$home,'program'=>$program,'kdy'=>$kdy,'tags'=>$tags,'upd'=>$upd, 'rok'=>$rok);
  }
  $telo= $CMS ? "" : facebook_dependency();

  $akce=$aktual= $cist= '';

  // články obsahují odkazy, takže nemůže být použito zanoření do <a>..</a>
  $num_of_articles = 10;

  $cist="";

  foreach($xx as $cid=>$x) {
    $code= cid_pid($cid,$x->uid);
    //todo ugly, consider "main page" category
    if ( $x->page==100 && $x->tags == 'A' ) { // ---------------------------------------- hlavní strana - úvodní článek & timeline
      $telo .= vlakno($cid,'clanek','home',false);
    }
    elseif ( ($x->home==2 || $x->home==6) && $x->tags == 'A' ) { // ----------------------- abstrakt na home | nahoru
      $prihlaska= $x->prihlaska ? cms_form_ref("ONLINE PŘIHLÁŠKA") : '';
      if ($prihlaska) $prihlaska="<span style='position: absolute; top: 12px; left: 7px'>$prihlaska</span>";
      $data = query2menu($x->uid, $cid, $x->mid, $x->ref, $x->mref,$x->type,$x->program, $x->rok);
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\" id=\"pozvanka-masonry-{$x->uid}\"";
      $akce.= masonry_item($code, $x->upd, $jmp, $x->kdy, $x->nadpis, $prihlaska . $x->text, "position: relative;");
    }
    elseif ($x->home==1) {
      $data = query2menu($x->uid, $cid, $x->mid, $x->ref, $x->mref,$x->type,$x->program, $x->rok);
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $cist.= masonry_item($code, $x->upd, $jmp, $x->kdy, $x->nadpis, strlen($x->abstract) > 30 ? $x->abstract : $x->text, "max-height: 350px;");
    } //all other sections in masonry, always include first article='Literatura nejen pro muže'
    elseif ( $x->home==7 ){ // --------------------------------------- přečtěte si
      $data = query2menu($x->uid, $cid, $x->mid, $x->ref, $x->mref,$x->type,$x->program, $x->rok);
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $cist.= masonry_item($code, $x->upd, $jmp, $x->kdy, $x->nadpis, strlen($x->abstract) > 30 ? $x->abstract : $x->text, "max-height: 350px;");
    }
  }
  //add new events first
  if ($akce) {
    //$timeline = timeline();
    $telo = <<<EOF
$timeline
<div class='notif_event_container'><div class='masonry_container x'>  $akce  </div></div>
$telo
EOF;
  }
  if (!$CMS) {$telo.= "</div>" .  facebook() . "<div class='content'>";}

  $aktual = ($aktual) ? "<h2>Novinky na webu</h2><div class='masonry_container x'>" . $aktual . "</div>" : $aktual;

  $h= <<<__EOD
  <div class='content'>
  <div id='home_telo' class='x'>$telo</div>
  $aktual
  </div>
__EOD;
  return $h;
}
#funkce pro extrakci vhodných obrázků pro přečtete si ==> todo pouze pro text, abstrakt bývá prázdný??
                                                          #todo podobná funkce x_first_img()
function masonry_text($text) {
  $ret = '';
  $found = preg_match_all('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $text, $images);

  $foundImage = false;
  for ($i = 0; $i < $found; $i++) {
    $img= $images[1][$i];
    if ( !file_exists($img)) continue;
    if (masonry_suitable_image($img)) {
      $ret = '<img src="'.$images[1][$i].'" alt="Obrázek k abstraktu"/>';
      $foundImage = true;
      break;
    }
  }
  if (!$foundImage && isset($images[1][0])) {
    $ret = '<img src="'.$images[1][0].'" alt="Obrázek k abstraktu" class="masonry-image-fit"/>';
  }
  $ret .= preg_replace("/<img[^>]+\>/i", "", $text);
  return $ret;
}
function masonry_item($cms_code, $updated, $custom, $date, $title, $text, $style="") {
  return "$cms_code <div class='abstrakt short_post x $updated$x->upd' style='padding: 9px;$style' $custom>
             $date<div class='post_title' style='display: block; width: 100%'>$title</div>
             <div class='clear'></div>". masonry_text($text)."</div>";
}
/*function masonry_text($abstract, $text) { //todo verze pro abstrakt..
  $image = null;
  preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $abstract, $image);
  if (!masonry_suitable_image($image['src'])) {
    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $text, $image);
    if (!masonry_suitable_image($image['src'])) {
      $image = null;
    }
  }
  $ret = preg_replace("/<img[^>]+\>/i", "", $abstract ? $abstract : $text);
  return ($image ? '<img src='.$image["src"].' alt="Obrázek k abstraktu"/>' : '') . $ret;
}*/
function masonry_suitable_image($image) {
  list($width, $height) = getimagesize($image);
  return $width > 249 && $width >= $height;
}
# ==========================================================================================> clanky
# článek je tvořen záznamem v CASE a záznamy v PART
# A-záznam = základní text
#   F-záznam = přidá ikonu fotky
#   T-záznam = přidá ikonu tabulky
# ------------------------------------------------------------------------------------------- clanky
# zobrazení článků stránek daných $pids nebo $mid
# nebo pro chlapi.online vzorem $chlapi a zpětnou referencí
# id=pid nebo název menu
function clanky($pids,$uid=0,$mid=0,$chlapi='',$back='') { trace();
  global $CMS, $href0, $mode;
  global $usergroups, $userid, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $page_mref, $news_time;
//                                                         display("c page_mref = $page_mref");
  $spec= 0;     // vlákna s chráněným přístupem
  list($cid0,$uid0)= explode(',',$uid);
  // pokud je mezi články rozečtená knížka
  if ( $uid0 ) {
    $uid= $uid0;
  }
  else {
    $cid0= 0;
  }
  $xx= array();
  $AND= $chlapi
      ? " AND chlapi RLIKE '$chlapi' "
      : ($mid ? "AND c.mid=$mid -- AND tags='A'"
          : "AND c.pid IN ($pids) AND type IN (1,3,6) -- AND tags='A'");
  $ORDER= $chlapi
      ? " c.uid DESC"
      : " c.sorting DESC, c.uid DESC";
  $spec_user= $userid ? "IF(ids_osoba,FIND_IN_SET($userid,ids_osoba),1)" : "!ids_osoba";
//   $AND= "AND c.pid IN ($pids) AND tags='A'";
  $p_show= ($show_hidden ?  '' : " AND !c.hidden AND !p.hidden")
      . ($show_deleted ? '' : " AND !c.deleted AND !p.deleted");
  // NEW - nový článek, UPD - opravený později než den po vytvoření ... vždy platí UPD>NEW>=$news_time
  // předpoklady: c.tstamp>=c.crdate, c.tstamp>=p.tstamp, p.tstamp>=p.crdate=p.date
  // upd=1 => UPD (oprava později než den po vytvoření), upd=2 => NEW (vytvoření bez opravy), upd=0 => -
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $cq= "
    SELECT p.uid, c.uid, fe_groups, tags, c.type, p.title, text, abstract, p.deleted, p.hidden,
      IF(c.tstamp>$news_time, 
        IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),''),
      SUM(IF(tags='D',1,0)),SUM(IF(tags='F',1,0))
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE  1 $p_show $AND
      $groups
      AND $spec_user 
    GROUP BY c.uid    
    ORDER BY $ORDER";
  $cr= pdo_qry($cq);
  while ( $cr && (list($p_uid,$cid,$fe_group,$tags,$type,$title,$text,$abstr,$del,$hid,$upd,$ds,$fs)
          = pdo_fetch_row($cr)) ) {
    $tags= $del ? 'd' : '';
    $tags.= $hid ? 'h' : '';
    $flags= ($ds ? 'D' : '').($fs ? 'F' : '');
    $text= web_text($text);
    if ( $p_uid!=$uid ) {
      $text= x_shorting($abstr ? $abstr : $text);
    }
    if ( $fe_group ) {
      $spec++;
      $tags.= '6';
    }
    $xx[$cid]= (object)array('ident'=>$p_uid,'nadpis'=>$title,'abstract'=>$text,
        'tags'=>$tags, 'flags'=>$flags, 'type'=>$type, 'upd'=>$upd);
  }
  $found= count($xx)." článků" . ($spec ? " ($spec)" : '');
//                                                         debug($xx);
  $h= "<div id='list' class='x'>";
  $abstr= $mode[1] ? 'abstr' : 'abstr-line';
  foreach($xx as $cid=>$x) {
//                                                       display("články {$x->ident} ? $uid");
    $flags= '';
    $flags.= strpos($x->flags,'D')!==false
        ? " <i class='fa fa-exchange tooltip'><span class='tooltip-text'>datum akce</span></i> " : '';
    $flags.= strpos($x->flags,'F')!==false
        ? " <i class='fa fa-camera-retro tooltip'><span class='tooltip-text'>fotogalerie</span></i> " : '';
    $flags.= strpos($xx[$cid]->tags,'6')!==false
        ? " <i class='fa fa-key tooltip' style='color:red'><span class='tooltip-text'>soukromý článek</span></i> " : '';
    $ex= strpos($xx[$cid]->tags,'d')!==false ? ' abstrakt_deleted' : (
    strpos($xx[$cid]->tags,'h')!==false ? ' abstrakt_hidden' : '');
    $code= cid_pid($cid,$x->ident);
    if ( $back ) {
      $jmp= str_replace('*', $x->ident, $back);
    }
    else {
      $jmp_code= "go_anchor(arguments[0],'$href0!$x->ident#vlakno','$page_mref/$x->ident#anchor$x->ident');";
      $jmp= $CMS ? "onclick=\"$jmp_code\"" : "href='$page_mref/$x->ident#anchor$x->ident'";
    }
    $menu= '';
    $typ= 'clanek';
    $ident= $x->ident;
    if ( $CMS )
      $menu= $x->type==3
          ? " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['otevřít knihu',function(el){ go(null,'$href0!$x->ident#vlakno','$page_mref/$x->ident'); }],
              ['editovat knihu',function(el){ opravit('$typ','$ident','$cid'); }],
              ['-skrýt knihu',function(el){ skryt('$typ','$ident',1); }],
              ['zobrazit knihu',function(el){ skryt('$typ','$ident',0); }],
              ['-zahodit knihu',function(el){ zrusit('$typ','$ident',1); }],
              ['obnovit knihu',function(el){ zrusit('$typ','$ident',0); }]
            ],arguments[0],'abstr$ident');return false;\""
          : " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['editovat článek',function(el){ opravit('$typ','$ident','$cid'); }],
              ['přidat pokračování',function(el){ pridat('part','$cid'); }],
              ['přidat fotky',function(el){ pridat('foto','$cid'); }],
              ['-skrýt článek',function(el){ skryt('$typ','$ident',1); }],
              ['zobrazit článek',function(el){ skryt('$typ','$ident',0); }],
              ['-zahodit článek',function(el){ zrusit('$typ','$ident',1); }],
              ['obnovit článek',function(el){ zrusit('$typ','$ident',0); }]
            ],arguments[0],'abstr$ident');return false;\"";
    $h.= ($x->type==3 || $x->type==7) && ( $x->ident==$uid || $cid==$cid0 )
        ? ( $x->type==3
            ? knihy(0,"$cid,$uid",0,$back)
            : "<div class='pdf'>".knihy(0,"$cid,$uid",0,$back)."</div>"
        ) : (
        $x->ident==$uid
            ? vlakno($cid,'clanek','', false, true)
            : "<div class='$abstr x'$menu>
             $code
             <a id='abstr$ident' class='abstrakt $ex x $x->upd' $jmp>
               $flags <span class='h7'>$x->nadpis:</span> $x->abstract
             </a>
           </div>"
        );
  }
  $h.= "</div>";
  return $h;
}
# ---------------------------------------------------------------------------------==> . load clanek
# přenos článku do editoru
function load_clanek($uid) { trace();
  $x= (object)array();
  // zamkni článek
  $x= record_lock($uid);
  if ( $x->uid ) {
    // článek je zamknutý
    goto end;
  }
  list($x->uid,$x->mid,$x->tags,$x->autor,$x->nadpis,$x->obsah,$x->abstract,$psano,$od,$do,
      $x->program,$x->homepage,$x->cruser_id,$x->ctype,$x->sorting,$x->kapitola,$x->pro,
      $x->id_akce, $x->status)=
      select(
          "p.uid,c.mid,tags,author,title,text,abstract,FROM_UNIXTIME(p.date),FROM_UNIXTIME(fromday),
       FROM_UNIXTIME(untilday),program,homepage,p.cruser_id,type,c.sorting,p.kapitola,c.fe_groups,
       id_akce, status",
          "setkani4.tx_gncase_part AS p JOIN setkani4.tx_gncase AS c ON cid=c.uid",
          "p.uid='$uid'");
  $x->od= sql_date1($od);
  $x->do= sql_date1($do);
  $x->kalendar= $x->tags=='K' ? 1 : 0;
  $x->psano= sql_date1($psano);
  debug($x,"akce=$x->nadpis");
end:
  return $x;
}
# ---------------------------------------------------------------------------------==> . save clanek
function save_clanek($x,$uid,$ref='') { trace(); //debug($x,"save_clanek");
  // konec pokud nebyla změna
  if ( !$x ) { goto end; }
  $msg= $ref; // poznámka do logu
  // staré hodnoty
  list($cid,$mid,$type)= select("p.cid,c.mid,type",
      "tx_gncase_part AS p JOIN tx_gncase AS c ON cid=c.uid","p.uid='$uid'");
  // nové hodnoty
  $psano= '';
  $upd= 1;      // většinou jde o podstatnou změnu
  $part= $case= array();
  foreach ($x as $elem=>$val) {
    switch ($elem) {
      // změny podstatné pro klienty
      case 'autor':       $part[]= "author='".pdo_real_escape_string($val)."'"; break;
      case 'nadpis':      $part[]= "title='".pdo_real_escape_string($val)."'"; break;
      case 'obsah':       $part[]= "text='".pdo_real_escape_string($val)."'"; break;
      case 'abstract':    //check whether abstract filled in (database had abstract fields with '&nbsp;' content only)
        if (strlen($val) < 20) $part[]= "$elem=''";
        else  $part[]= "$elem='".pdo_real_escape_string($val)."'";
        break;
      case 'id_akce':     $part[]= "id_akce='$val'"; break;
      case 'od':          $case[]= "fromday=UNIX_TIMESTAMP('".sql_date1($val,1)."')"; break;
      case 'do':          $case[]= "untilday=UNIX_TIMESTAMP('".sql_date1($val,1)."')"; break;
      case 'program':     $case[]= "$elem='".implode(',',(array)$val)."'"; break;
      case 'psano':       $sql_psano= "UNIX_TIMESTAMP('".sql_date1($val,1)."')";
        $part[]= "date=$sql_psano"; break;
      // přepínání mezi 0=akcí a 1=kalendářem
      case 'kalendar':    $val= $val?'K':'A'; $part[]= "tags='$val'";
                          $msg= ($val=='K'?'založení':'zrušení').' kalendáře'; break;
      // nepodstatné pro klienty
      case 'ctype':       $upd= 0; $case[]= "type='$val'"; $type=$val; break;
      case 'cruser_id':   $upd= 0; $part[]= "$elem='$val'"; break;
      case 'homepage':    $upd= 0; $part[]= "$elem='$val'"; break;
      case 'status':      $upd= 0; $part[]= "$elem='$val'"; break;
      case 'sorting':     $upd= 0; $case[]= "$elem='$val'"; break;
      case 'kapitola':    $upd= 0; $part[]= "$elem='$val'"; break;
      case 'pro':         $upd= 0; $case[]= "fe_groups='$val'"; break;
      default: fce_error("save_clanek - neznámá položka '$elem'");
    }
//                                                         debug($part,'part');
//                                                         debug($case,'case');
  }
  // aktualizace změn pro part a case
  if ( $upd ) $part[]= $case[]= "tstamp=UNIX_TIMESTAMP()";
  if ( $part ) query("UPDATE tx_gncase_part SET ".implode(',',$part)." WHERE uid=$uid");
  if ( $case ) query("UPDATE tx_gncase SET ".implode(',',$case)." WHERE uid=$cid");
  // odemkni článek
  record_unlock($uid);
  // zápis o opravě
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_menu,uid_case,uid_part,message) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Update','$mid','$cid','$uid','$msg')");

  // aktualizace bez podstatných změn => nepíšeme do menu
  if ( !$upd ) goto end;

  // aktualizace změn do menu
  $mid= $type==2 ? 10 : $mid;           // 10=akce, 21=nové DS, 22=staré DS
  $set= "tstamp=UNIX_TIMESTAMP()";
  if ( $sql_psano ) $set.= ",crdate=IF(crdate<$sql_psano,$sql_psano,crdate)";
  query("UPDATE setkani4.tx_gnmenu SET $set WHERE mid='$mid'");

  // aktualizace změn na home-page
  list($upd,$new)= select("MAX(tstamp),MAX(crdate)","tx_gncase_part","homepage IN (1,2,6,7,8)");
  query("UPDATE tx_gnmenu SET tstamp=$upd,crdate=$new WHERE mid='32'");

  // přenesení změn ze submenu do hlavního menu
  query("
    UPDATE tx_gnmenu AS h JOIN (
      SELECT mid_top,MAX(tstamp) AS s_tstamp FROM tx_gnmenu GROUP BY mid_top
    ) AS s ON s.mid_top=h.mid
    SET h.tstamp=s_tstamp");
  end:
  return 1;
}
# -------------------------------------------------------------------------------==> . create clanek
# vytvoření článku
function create_clanek($x,$ref) { //$pid,$autor,$nadpis,$obsah,$psano) { trace();
  debug($x,'create_clanek');
  $pid= $x->pid;
  $mid= $x->mid;
  $type=$x->ctype;
  $cruser_id= $x->cruser_id;
  $autor= pdo_real_escape_string($x->autor);
  $nadpis= pdo_real_escape_string(web_text($x->nadpis));
  $obsah= pdo_real_escape_string(web_text($x->obsah));
  $abstract= pdo_real_escape_string(web_text($x->abstract));
  $f_od= $f_do= $v_od= $v_do= '';
  $od= $do= 0;
  if ( $x->od ) {
    $f_od= ",fromday";
    $od= sql_date1($x->od,1);
    $v_od= ",UNIX_TIMESTAMP('$od')";
  }
  if ( $x->do || !$x->do && $od) {
    $f_do= ",untilday";
    $do= $x->do ?: $x->od;
    $v_do= ",UNIX_TIMESTAMP('".sql_date1($do,1)."')";
  }
  $psano= sql_date1($x->psano,1);
  $program= implode(',',(array)$x->program);
  $sorting= $x->sorting;
  $kapitola= $x->kapitola;
  $pro= $x->pro;
  query("INSERT INTO setkani4.tx_gncase (pid,mid,crdate$f_od$f_do,program,cruser_id,type,sorting,fe_groups)
         VALUES($pid,$mid,UNIX_TIMESTAMP('$psano')$v_od$v_do,
           '$program','$cruser_id','$type','$sorting','$pro')");
  $cid= pdo_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,kapitola,date,
      tstamp,cruser_id) VALUES ($pid,$cid,'A','$autor','$nadpis','$obsah','$abstract','$kapitola',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= pdo_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  $ref.= "!$uid#anchor$uid";
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part,message) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid','$ref')");
  return $uid;
}
# -------------------------------------------------------------------------------==> . create clanek
# přidání části
function add_part($cid,$tags='D') {
  $kapitola= 0;
  if ( $tags=='E' ) {
    // pro kapitolu knihy navrhni nové číslo
    $kapitola= 1+select1("MAX(kapitola)","tx_gncase_part","cid=$cid");
  }
  list($pid,$mid,$cruser_id)= select("pid,mid,cruser_id","setkani4.tx_gncase","uid=$cid");
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,date,tstamp,cruser_id,kapitola) "
      . "VALUES ($pid,$cid,'$tags',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),$cruser_id,$kapitola)");
  $uid= pdo_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid')");
  return 1;
}
# ======================================================================================> prezentace
# ---------------------------------------------------------------------------==> . create prezentace
# vytvoření prezentace
function create_prezentace($x) { //$pid,$autor,$nadpis,$obsah,$psano) { trace();
  debug($x,'create_prezentace');
  $pid= $x->pid;
  $mid= $x->mid;
  $type=$x->ctype;
  $cruser_id= $x->cruser_id;
  $autor= pdo_real_escape_string($x->autor);
  $nadpis= pdo_real_escape_string(web_text($x->nadpis));
  $obsah= pdo_real_escape_string(web_text($x->fname));
  $abstract= pdo_real_escape_string(web_text($x->obsah));
  $psano= sql_date1($x->psano,1);
  $program= implode(',',(array)$x->program);
  $sorting= $x->sorting;
  query("INSERT INTO setkani4.tx_gncase (pid,mid,crdate,program,cruser_id,
           type,sorting,fe_groups)
         VALUES($pid,$mid,UNIX_TIMESTAMP('$psano'),'$program','$cruser_id',
          '$type','$sorting','$pro')");
  $cid= pdo_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,date,tstamp,
      cruser_id) VALUES ($pid,$cid,'C','$autor','$nadpis','$obsah','$abstract',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= pdo_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid')");
  return $uid;
}
# -----------------------------------------------------------------------------==> . save prezentace
function save_prezentace($x,$uid) { trace();
  debug($x,'save_prezentace');
  $x->obsah= $x->fname;
  unset($x->fname);
  return save_clanek($x,$uid);
}
# ===========================================================================================> kniha
# --------------------------------------------------------------------------------==> . create kniha
# vytvoření knihy
function create_kniha($x) { //$pid,$autor,$nadpis,$obsah,$psano) { trace();
  debug($x,'create_clanek');
  $pid= $x->pid;
  $mid= $x->mid;
  $type=$x->ctype;
  $cruser_id= $x->cruser_id;
  $autor= pdo_real_escape_string($x->autor);
  $nadpis= pdo_real_escape_string(web_text($x->nadpis));
  $obsah= pdo_real_escape_string(web_text($x->obsah));
  $abstract= pdo_real_escape_string(web_text($x->abstract));
  $od= sql_date1($x->od,1);
  $do= sql_date1($x->do ?: $x->od,1);
  $psano= sql_date1($x->psano,1);
  $program= implode(',',(array)$x->program);
  $sorting= $x->sorting;
  $pro= $x->pro;
  query("INSERT INTO setkani4.tx_gncase (pid,mid,crdate,fromday,untilday,program,cruser_id,
           type,sorting,fe_groups)
         VALUES($pid,$mid,UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP('$od'),UNIX_TIMESTAMP('$do'),
           '$program','$cruser_id','$type','$sorting','$pro')");
  $cid= pdo_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,date,tstamp,
      cruser_id) VALUES ($pid,$cid,'C','$autor','$nadpis','$obsah','$abstract',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= pdo_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid')");
  return $uid;
}
# =======================================================================================> kalendáře
# kalenář akce je v databázi poznačen
function kalendare($vyber, $rok, $id, $chlapi_ignore=false) { trace();
  global $CMS, $news_time, $mode, $href0, $page_mref, $usergroups, $show_hidden, $show_deleted;
  // výběr podle programu - překlad na regexpr
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");

  // výběr podle času
  $c_kdy= $rok=='nove'
      ? " LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)"
      : " (YEAR(FROM_UNIXTIME(fromday))=$rok OR YEAR(FROM_UNIXTIME(untilday))=$rok)";
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $cr= pdo_qry("
      SELECT p.uid, p.cid, c.type,p.title,p.text,p.author,FROM_UNIXTIME(date),p.tags,
       p.deleted,p.hidden,fromday,untilday,FROM_UNIXTIME(fromday),c.program,
       IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),
         ' upd',' new'),'')
      FROM setkani4.tx_gncase AS c
      JOIN (SELECT * FROM setkani4.tx_gncase_part WHERE tags='K') AS p ON c.uid=p.cid 
      WHERE $c_kdy $groups $p_show
      ORDER BY fromday DESC
    ");

  $h = "";
  $n = 0;
  while ( $cr && (
      list($uid,$cid,$type,$title,$text,$autor,$psano,$tags,$del,$hid,$uod,$udo,$od,$program,$upd)
          = pdo_fetch_row($cr)) ) {

    if (($vyber == "dum" || $chlapi_ignore) && ($program == 3 || $program == "3")) continue;

    $n++;
    $tagc = "#k$n";
    $kdy= $ex= '';
    $ex.= $del ? 'd' : '';
    $ex.= $hid ? 'h' : '';
    if ( $type==2 && $tags=='A' ) {
      $kdy= datum_akce($uod,$udo);
    }
    $code= cid_pid($cid,$uid);
    $abstr= $mode[1] ? 'abstr' : 'abstr-line';
    $roks= $rok ? "/$rok" : '';
    $cms_roks= $rok ? ",$rok" : '';
    $back= $CMS ? $href0."$vyber$tagc"
        : "$page_mref$roks";
    $img= '';
    $text= xi_shorting($text,$img);

    if ($n > 1) {
      $h .= "<div class='relative abstrakt_separator'></div>";
    }

    //todo temporary solution hardcoded, can be based on program value
    if ( $program == 3 || $cid == 1644) {
      if (!strpos($vyber, "chlapi")) continue;
      $h.= "<div class='$abstr relative status_chlapi' id='n$n'>
           $code 
           <a class='abstrakt$ex' target='_blank' href='http://chlapi.cz/skupiny!brno!343'>
             <div class='abstrakt_calendar_title'>$title:</div><div class='clear'></div>$img 
               <p class='abstrakt_calendar_paragraph'>$text</p>
           </a>
         </div>";
      continue;
    }

    //todo remove $vyber from url? (and other useing $vyber as well!, dont forget htaccess! -> redirect)
    $jmp= $CMS ? "onclick=\"go_anchor(arguments[0],"
        . "'$href0!{$vyber}$cms_roks!$uid#anchor$uid','$page_mref$roks/$uid#anchor$uid');\""
        : "href='$page_mref$roks/$uid#anchor$uid'";

    //if selected a certain calendar, do not show other calendars...
    //todo possible reflect $vyber in the selection? how to get rkomu?
    if ($uid==$id) return vlakno($cid,'clanek',$back, false, true) . "<div class='clear'></div>";
    $h.= "<div class='$abstr' id='n$n'>
           $code 
           <a class='abstrakt$ex{$upd}' $jmp>
             <b>$title:</b><div class='clear'></div>$img 
               <p>$text</p>
           </a>
         </div>";
  }
  return $h ? "$h<div class='clear'></div>" : '';
}
# ============================================================================================> akce
# akce je tvořena vždy záznamem v CASE a záznamy v PART s tags A,F,D,T
# jako abstrakt se ukazuje jen A-záznam
# ------------------------------------------------------------------------------------ chlapi - akce
# volání ze servant
function chlapi_prehled($chlapi,$id,$backref) { trace();
  return akce_prehled('chlapi.online','',$id,'','',$chlapi,$backref);
}
# ------------------------------------------------------------------------------------- akce_prehled
# id=pid nebo název menu
# $fotogalerie je první abstrakt pro stránku
# $vyber - string key for specific category (foto | chlapi.online | chlapi.online-free | dum)
function akce_prehled($vyber,$kdy,$id,$fotogalerie='',$hledej='',$chlapi='',$backref='') { trace();
  global $CMS, $href0, $def_pars, $mode;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $page_mref, $news_time;
//                                                         display("p page_mref = $page_mref");
  $chlapi_online= 0;
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  list($id,$tag)= explode('#',$id);
  $typ= '';
  $hledej= trim($hledej);
  $xx_tags= $xx_foto= $xx_img= array();
  $h= "<div class='akce_content relative'>";
  $summary = "";
  $rkomu= array();

  if ( $vyber=='foto' ) {
    $typ= 'foto';
    $vyber= '';
    $c_komu= " 1";
    $c_kdy= " LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
    $ORDER= "DESC";
  }
  elseif ( $vyber=='chlapi.online' ) {
    $chlapi_online= 1;
    list($kdy,$cid)= explode(',',$id);
    $c_komu= " chlapi RLIKE '$chlapi'";
    $c_kdy= " 1 ";
  }
  elseif ( $vyber=='dum' ) { // proběhlé akce v DS
    $c_komu= "program REGEXP '6'";
    $c_kdy= "LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
//     if ( $id ) $kdy= $id;
  }
  else {
    // překlad $komu na regexpr
//    $rkomu= $_vyber= array();
//    foreach(explode(',',$vyber) as $kdo) {
//      $ki= $def_pars['komu'][$kdo];
//      if ( $ki ) {
//        list($k,$i)= explode(':',$ki);
//        $_vyber[]= $k;
//        if ( !in_array($i,$rkomu) )
//          $rkomu[]= $i;
//      }
//    }
//    $_vyber= implode(', ',$_vyber);
//    $c_komu= "0";
//    $c_kdy= 1;
//    if ( $rkomu ) {
//      $komu= implode('|',$rkomu);
//      $i= array_search('6',$rkomu);
//      if ( $i===false ) {
//        $c_komu= "program REGEXP '$komu'";
//      }
//      else {
//        unset($rkomu[$i]);
//        $c_komu= ($rkomu ? "program REGEXP '$komu' AND" : '')." program REGEXP '6'";
//      }
//    }
//
    if ( !$kdy ) $kdy= 'nove';

    $c_komu= "1";
    $c_kdy= "1";
  }
  // konec překladu
  $novych= 0;
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  // výběr roků podle cílové skupiny
  $cr= pdo_qry("
      SELECT
        IF(LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10),'nove',YEAR(FROM_UNIXTIME(fromday))) AS _rok,
        SUM(IF(tags='A',1,0)),
        IF(MAX(c.tstamp)>$news_time,IF(TO_DAYS(MAX(FROM_UNIXTIME(c.tstamp)))>TO_DAYS(MAX(FROM_UNIXTIME(c.crdate))),' upd',' new'),''),
        program
      FROM setkani4.tx_gncase AS c
      JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
      WHERE !c.deleted AND !c.hidden $p_show
        $groups AND $c_komu AND $c_kdy AND p.tags!='K'
      GROUP BY _rok
      ORDER BY _rok DESC  ");
  $counter = 1;
  while ( $cr && (list($rok,$pocet,$upd,$program)= pdo_fetch_row($cr)) ) {
    $mark= $rok=='nove' ? 'nove' : "rok$rok";
    $novych+= $rok=='nove' ? $pocet : 0;
    $rok_display = $rok=='nove' ? 'nové ' : $rok;

    if (!$rok_display) continue;

    $rok_nadpis = $rok;
    if ( $kdy==$rok ) {


      // otevřený archiv
      $akci= kolik_1_2_5($pocet,"akci,akce,akcí");
      $akce= kolik_1_2_5($pocet,"akce,akcí,akcí");
      if ( $rok=='nove' ) {
        $rok_display = date('Y');
        $rok_nadpis = 'nové ';
        $zacatek= "Zveme vás na <span class='js-update-vyber-count' style='font-size: 18pt;'>$akci</span>:";
        $zacatek_lowcase = "zveme vás na <span class='js-update-vyber-count'>$akci</span>:";
      } else {
        $zacatek= "Archiv <span class='js-update-vyber-count' style='font-size: 18pt;'>$akce</span>";
        $zacatek_lowcase = "archiv <span class='js-update-vyber-count'>$akce</span>";
      }

      $summary .= "<a class='akce_rok akce_rok_active' id='$mark' $upd>
                  <span class='akce_rok_title'>$rok_nadpis</span><span class='akce_rok_text'>$zacatek_lowcase</span>
                </a>";


      $back= "onclick=\"go(arguments[0],'$href0!$vyber#$mark','');\"";
      $back= '';

      $kalendare = kalendare($vyber, $kdy, $id);


      $h.= "<div id='$mark' $back><div class='content'>
              <span class='anchor' id='anchor$rok'></span>";

      if ($kalendare != '') {
        $h .= "<div class='akce_calendars_outer'>
                <div class='akce_calendars_design'>
                    <div class='akce_calendar_title'>Kalendáře akcí</div>
                    <div class='akce_calendar_year'>
                    <h2 class='akce_calendars_year_watermark'>$rok_display</h2>
                    </div>
                    
                 </div>

                <div class=\"akce_calendars\">
                <div style='padding: 0 10px; height: 100%; display: flex;overflow: hidden;'>$kalendare</div> </div></div>";
      }



      $h .= "<h2 class='akce_prehled_title'>$zacatek</h2>";

      $h.= akce($vyber,$kdy,$id,$fotogalerie,$hledej,$chlapi,$backref, false);
      $h.= "</div></div>";
    }
    else {
      // neaktivní archiv
      if ( $backref ) {
        $next= str_replace('*', "$rok", $backref);
      }
      elseif ( $CMS || $typ=='hledej' ) {
        $next= $typ=='hledej'
            ? "{$href0}hledej!$rok$hledej#$mark" : ( $vyber=='dum'
                ? "{$href0}!$rok#$mark"
                : "{$href0}!$vyber,$rok#$mark" );
        $next= "onclick=\"go_anchor(arguments[0],'$next','$page_mref/$rok#anchor$rok');\"";
      }
      else {
        $next= "href='$page_mref/$rok#anchor$rok'";
      }

      if ( $chlapi_online ) {
        $nadpis= $rok=='nove'
            ? "Připravujeme pro vás"
            : "Archiv roku $rok";
        $h.= "<div class='kniha_bg' >
                  <a class='jump' $next>$nadpis</a>
                </div>";
      } else {
        $nadpis= $rok=='nove'
            ? "připravujeme pro vás"
            : "archiv roku $rok_display";
        $summary .= "<a class='akce_rok' id='nove' $next $upd>
                  <span class='akce_rok_title'>$rok_display</span><span class='akce_rok_text'>$nadpis</span>
                </a>";
      }
    }
    $counter++;
  }
  if ( !$novych && $vyber!='dum' && $vyber!='chlapi.online') {
    $akce_class = '';
    if ( $CMS || $typ=='hledej' ) {
      $next= "{$href0}!$vyber,nove#$mark";
      $next= "onclick=\"go_anchor(arguments[0],'$next','$page_mref/nove#anchornove');\"";
    }
    else {
      $next= "href='$page_mref/nove#anchornove'";
    }

    if ($kdy == 'nove') {
      $h.= "<div class='kniha_br'><b>Nejsou k dispozici žádné nové akce.</b></div>";
      $akce_class = " akce_rok_active";
    }
    $summary = "<a class='akce_rok$akce_class' id='$mark' $next $upd>
                 <span class='akce_rok_title'>nové</span><span class='akce_rok_text'>žádné akce</span>
               </a>" . $summary;

  }
  $h.= "</div>";
  // navrácení textu
  //$h.= $chlapi_online ? '' : $c_komu;
  $h.= "</div>";

  return "<div class='akce_container content'><div class='akce_summary'>$summary</div>$h</div>";
}
# --------------------------------------------------------------------------------------------- akce
# id=pid nebo název menu
# $fotogalerie je první abstrakt pro stránku
function akce($vyber,$kdy,$id=0,$fotogalerie='',$hledej='',$chlapi='',$backref='', $wrap_container=true) { trace();
  global $CMS, $href0, $def_pars, $mode;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $page_mref, $news_time;
  global $ezer_path_root, $FREE;
//                                                         display("a page_mref = $page_mref");
  list($id,$tag)= explode('#',$id.'#');
  $rok= $tag= $chlapi_url= '';
  $typ= 'akce';
  $hledej= trim($hledej);
  $calendar="";
  $xx= $xx_tags= $xx_foto=$rkomu= $xx_img= array();
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  if ( $vyber=='foto' ) {
    $typ= 'foto';
    $vyber= "";
    $c_komu= " 1";
    query("SET SESSION group_concat_max_len = 1000000");
    $cids= select("GROUP_CONCAT(cid)","tx_gncase_part","tags='F'");
    if ( $cids ) $c_komu= " c.uid IN ($cids)";
    $c_kdy= " LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
    $c_kdy= " 1";
    $ORDER= "DESC";
  }
  elseif ( $vyber=='chlapi.online' ) {
    // volání z chlapi.online přes servant.php
//    $chlapi_url= "?page=foto&rok=$kdy";
    ezer_connect('setkani4');
//    $usergroups= '6';
    $typ= 'clanek';
    $vyber= 'chlapi';
//    $c_komu= " program=3";
    $c_komu= " chlapi RLIKE '$chlapi'";
    $c_kdy= " YEAR(FROM_UNIXTIME(untilday))=$kdy";
    list($rok,$id)= explode(',',$id);
    $ORDER= "DESC";
  }
  elseif ( $vyber=='chlapi.online-free' ) {
    // volání z chlapi.online přes servant.php
    $chlapi_url= "?page=foto";
    ezer_connect('setkani4');
    $usergroups= '0';
    $typ= 'clanek';
    $vyber= 'chlapi';
    $c_komu= " program=3";
    $c_kdy= " p.uid=$id";
    $ORDER= "DESC";
  }
  elseif ( $vyber=='dum' ) { // proběhlé akce v DS
    $vyber= "!$kdy";
    $c_komu= "program REGEXP '6'";
    $c_kdy= " YEAR(FROM_UNIXTIME(untilday))=$kdy";
    if ( $kdy==date('Y') ) {
      $c_kdy.= " AND LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
    }
    $rok= $kdy;
    $ORDER= "DESC";
  }
  elseif ( $vyber=='hledej' ) {
    $typ= 'hledej';
    $vyber= 'hledej';
    $c_komu= " 1";
    $hledej = mysql_real_escape_string($hledej);
    $c_kdy= in_array($hledej,array(6,9,12,13))  // mrop,ritualy,vps, 13=rodina :-)
        ? " fe_groups='$hledej'"
//      : " MATCH(author,p.title,text) AGAINST ('$hledej' IN BOOLEAN MODE)";
        : " CONCAT(author,p.title,text) RLIKE '$hledej'";
////     if ( $id )
//       $c_kdy.= " AND YEAR(FROM_UNIXTIME(fromday))=$id";
    $hledej= "!$hledej";
    $ORDER= "DESC";
  }
  else {
    //make sure if article is requested to show that article although $komu does not intersect with article 'komu'
//    $data = array();
//    $dataSize = 0;
//    if ($id) {
//      $cr = pdo_qry("SELECT c.program FROM (setkani4.tx_gncase AS c JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid) WHERE p.uid=$id LIMIT 1");
//      $data = pdo_fetch_row($cr);
//      if ($data && count($data)) {
//        $data = explode(",", $data[0]);
//        $dataSize = count($data);
//      } else {
//        $id = 0;
//      }
//    }
//
//    // překlad $komu na regexpr
//    foreach(explode(',',$vyber) as $kdo) {
//      $ki= $def_pars['komu'][$kdo];
//      if ( $ki ) {
//        list($k,$i)= explode(':',$ki);
//        if ( !in_array($i,$rkomu) )
//          $rkomu[]= $i;
//
//        $pos = array_search($i, $data);
//        if ($pos !== false) unset($data[$pos]);
//      }
//    }
//
//    //if no intersection, add
//    if (count($data) == $dataSize && $dataSize > 0) {
//      $rkomu = array_merge($rkomu, $data);
//      foreach ($data as $_ => $komuId) {
//        array_push($rkomu, $komuId);
//        $vyber .= "," . $def_pars['komu_reversed'][$komuId];
//      }
//      setcookie('akce', urlencode($vyber), time()+86400, "/", COOKIE_DOMAIN);
//    }
//
//    $c_komu= "0";
//    if ( $rkomu ) {
//      $komu= implode('|',$rkomu);
//      $i= array_search('6',$rkomu);
//      if ( $i===false ) {
//        $c_komu= "program REGEXP '$komu'";
//      }
//      else {
////                                                           debug($rkomu);
//        unset($rkomu[$i]);
//        $c_komu= ($rkomu ? "program REGEXP '$komu' AND" : '')." program REGEXP '6'";
//      }
//    }
//                                                          display("komu=$c_komu ... $i ... {$rkomu[$i]}");
    // překlad $kdy na podmínku
    $c_komu = "1";

    $c_kdy= 0;
    if ( is_numeric($kdy) || $kdy=='nove' ) {
      $rok= $kdy;
      $tag= $kdy=='nove' ? "#nove" : "#rok$kdy";
      $c_kdy= $kdy=='nove'
          ? " LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)"
          : " YEAR(FROM_UNIXTIME(untilday))=$kdy AND LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
      $ORDER= $kdy=='nove' ? "ASC" : "DESC";
    }
    elseif ( $kdy=='bude' || $kdy=='bude_alberice' ) { //alberice
      $c_kdy= "LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)";
      $ORDER= "ASC";

      $c_komu = "program REGEXP '6'";

      //in case alberice future events, display calendar
      $calendar = kalendare('alberice', 'nove', $id, true);
      if ($calendar) $calendar .= "<div class='content'><h2>Plánované akce</h2></div>";
    }
    else {
      //todo  $vyber used?  looks like bug
      foreach(explode(',',$vyber) as $v) {
        $ki= $def_pars['bylo'][$v];
        if ( $ki ) {
          list($k,$c_kdy)= explode(':',$ki);
          $c_kdy.= " AND LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
          $ORDER= "DESC";
          break;
        }
      }
    }
    $vyber= "!$vyber".($rok?",$rok":'');
    display("úprava vyber=$vyber");
  }

//                                                        display("kdy=$c_kdy");
  $spec= 0;     // vlákna s chráněným přístupem
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $qry= "
    SELECT p.uid, c.uid, fe_groups, tags, p.title, text, p.abstract, p.deleted, p.hidden, fromday, untilday,
           id_akce, status, a.web_prihlasky,
      IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),''),
      program
      -- DATEDIFF(FROM_UNIXTIME(untilday),FROM_UNIXTIME(fromday))+1 AS _dnu,
      -- FROM_UNIXTIME(fromday) AS _od, FROM_UNIXTIME(untilday) AS _do
    FROM (setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid)
    LEFT OUTER JOIN ezer_db2.akce AS a ON a.id_duakce=p.id_akce
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE !c.deleted AND !c.hidden $p_show
      $groups
      AND $c_komu AND $c_kdy
      AND IF(tags='F',LENGTH(text)>3,1)
    ORDER BY fromday $ORDER
    -- LIMIT 6,2
  ";
  $cr= pdo_qry($qry);
  while ( $cr && (list($p_uid,$cid,$fe_group,$tags,$title,$text,$abstract,$del,$hid,$uod,$udo,$ida,$status,$prihlaska,$upd,$program)=
          pdo_fetch_row($cr)) ) {
    if ( $ida && !in_array($kdy,array('nove','bude','bude_alberice')) ) $ida= 0;

    $classes = array_map(function ($el) {
      global $def_pars;
      return "js-update-vyber-category-" . $def_pars['komu_reversed'][$el];
    }, explode(",", $program));
    $classes = "js-update-vyber-category " . implode(" ", $classes);

    $xx_tags[$cid].= $del ? 'd' : '';
    $xx_tags[$cid].= $hid ? 'h' : '';
    if ( $tags=='F' ) {
      $xx_tags[$cid].= $tags;
      if ($abstract) {       //fotogallery --> check if main photo defined
        $xx_foto[$cid]= "fileadmin/photo/$p_uid/.$abstract";
      } else {
        list($foto)= explode(',',$text);
        $xx_foto[$cid]= "fileadmin/photo/$p_uid/.$foto";
      }
    }
    else {
      $text= web_text($text);
      if ( $tags=='A' ) {
        $akdy= datum_akce($uod,$udo);
        if ( $p_uid!=$id || 1 ) { //todo always true -- delete?
          $text= xi_shorting($text,$img);
          if ( $img ) {
            $xx_img[$cid]= $img;
          }
        }
        $xx[$cid]= (object)array('ident'=>$p_uid,'kdy'=>$akdy, 'rok'=>date("Y", $uod), 'nadpis'=>$title, 'classes'=>$classes,
            'abstract'=> $text,'upd'=>$upd,'ida'=>$ida,'prihlaska'=>$status==1 ? null : $prihlaska,'status'=>status_class($status),'from'=>$uod);
        if ( $fe_group ) {
          $spec++;
          $xx_tags[$cid].= '6';
        }
      }
      else {
        $xx_tags[$cid].= $tags;
      }
    }
  }

  // pokud je typ==akce a je požadavek na akce pro chlapy, doplníme xx voláním servant_ch
  if ( $typ=='akce' && in_array(3,$rkomu) ) {
    global $chlapi_cz;
    $key= -1;
    $parm= is_numeric($kdy) ? "typ=2&rok=$kdy" : "typ=1";
    $chlapi_url= "$chlapi_cz/servant_ch.php?$parm&err=3";
   //                                                       trace($chlapi_url);
    $a_json= url_get_contents($chlapi_url,false,false);
    if ( $a_json ) {
      $json= json_decode($a_json);
      foreach( $json->clanky as $c) {
        $from= strtotime($c->od);
        $od_do= datum_oddo($c->od,$c->do);
        $xx[$key]= (object)array('extern'=>1,'from'=>$from,'href'=>$c->href,'flags'=>$c->flags,
                  'kdy'=>$od_do,'nadpis'=>$c->nadpis,'abstract'=>$c->abstrakt
                  );
        $key--;
      }
    }
    // seřadíme podle začátku akce
    uasort($xx, $ORDER == "DESC" ?
        function ($a,$b) { return ($a->from <= $b->from ? 1 : -1); } :
        function ($a,$b) { return ($a->from > $b->from ? 1 : -1); });
  }
  $found= count($xx)." akcí" . ($spec ? " ($spec)" : '');
//                                                         debug($xx);
  // případné doplnění helpu na začátek
  $info= akce_info($typ,count($xx));
  // generování stránky
  $rok_ted = '';
  $h= ($wrap_container ? "<div class='content'>" : "") . $info;
  $h.= $calendar;

  $abstr= $mode[1] ? 'abstr' : 'abstr-line';
  $n= 0; // pořadí akce v roce
  foreach($xx as $cid=>$x) {
    $n++;

    $classes = $x->classes;
    if ( isset($x->extern) ) {
      $code= $CMS ? "<div class='code'>chlapi.cz</div>" : '';
      $flags= '';
      if ( $x->flags ) {
        foreach (str_split($x->flags) as $f) {
          $flags.=
              $f=='T' ? " <i class='fa fa-table tooltip'><span class='tooltip-text'>přihlašovací tabulka</span></i> " : (
              $f=='F' ? " <i class='fa fa-camera-retro tooltip'><span class='tooltip-text'>fotogalerie</span></i> " : '');
        }
      }
      //$jmp= "href='$x->href' target='chlapi.cz'";
      $jmp= $CMS ? " onclick=\"go_chlapi_cz('$x->href')\"" : " href=\"$x->href\" target=\"_blank\"";
      $h.= "<div class='$abstr relative status_chlapi $classes' 
             title='Po kliknutí přepnu na text na web chlapi.cz'>
           $code 
           <a class='abstrakt' $jmp>
             <span class='akce_datum'>$x->kdy $flags</span>  <b>$x->nadpis:</b><div class='clear'></div> 
               <p>$x->abstract</p>
           </a>
         </div>";
      continue;
    }
    $tagn= "#n$n";
    $flags= $mini= '';
    $foto= strpos($xx_tags[$cid],'F')!==false;
    if ( $foto ) {
      // překlad na globální odkazy pro ty lokální (pro servant.php)
      $http= $FREE && preg_match("/fileadmin/",$xx_foto[$cid]) ? "https://www.setkani.org/" : '';
      $mini = $xx_foto[$cid] ?
          ($typ=='foto' ?
              "style='background-image:url($http{$xx_foto[$cid]})'"
              : "<div class='mini' style='background-image:url($http{$xx_foto[$cid]})'></div>") : '';
      $flags.= "<i class='fa fa-camera-retro tooltip'><span class='tooltip-text'>fotogalerie</span></i>";
    }
    $flags.= strpos($xx_tags[$cid],'6')!==false
        ? " <i class='fa fa-key' style='color:red'></i> " : '';
    $flags.= strpos($xx_tags[$cid],'D')!==false
        ? " <i class='fa fa-exchange'></i> " : '';
    if ( $typ=='foto' && !$flags ) continue;
    if ( strpos($xx_tags[$cid],'T')!==false ) {
      $flags.= " <i class='fa fa-table tooltip'><span class='tooltip-text'>přihlašovací tabulka</span></i> ";
    }
    if ( $backref ) {
      $jmp= str_replace('*', "$rok,$x->ident", $backref);
    }
    elseif ( $FREE ) {
      $jmp= "href='$chlapi_url&id=$x->ident'";
    }
    else {
      $roks= $rok ? "/$rok" : '';
      $jmp= $CMS ? "onclick=\"go_anchor(arguments[0],'$href0{$vyber}!$x->ident$hledej#vlakno','$page_mref$roks/$x->ident#anchor$x->ident');\""
          : "href='$page_mref$roks/$x->ident#anchor$x->ident'";
      $back= $CMS ? $href0.($hledej?"$vyber!$hledej":"$vyber$tagn")
          : "$page_mref$roks";
    }
    $img = $mini || $typ=='foto' ? $mini : $xx_img[$cid]; //do not user xx_img if typ==foto
    $ex= strpos($xx_tags[$cid],'d')!==false ? ' abstrakt_deleted' : (
    strpos($xx_tags[$cid],'h')!==false ? ' abstrakt_hidden' : '');
    $code= cid_pid($cid,$x->ident);
//     $back= $foto ? "#foto$cid" : '';

    $prihlaska= $x->ida && $x->prihlaska && !$x->status ? cms_form_ref("ONLINE PŘIHLÁŠKA") : '';
//    $prihlaska= cms_form_ref("ONLINE PŘIHLÁŠKA");

    $port = ""; //$_SERVER['SERVER_PORT'] == "80" ? "" : ":".$_SERVER['SERVER_PORT'];
    $permalink = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$port."/clanek/".$x->ident;
    $h.= $x->ident==$id
        ? vlakno($cid,$typ,$back, false, true)
        : "<div class='$abstr $x->status $classes' id='n$n'>
           $code 
           <a class='abstrakt$ex{$x->upd}' $jmp>
             $prihlaska
            
             <span class='akce_datum'>
             <i class=\"fa fa-link tooltip clickable\" style='color: darkgray' onclick=\"copyTextToClipboard('$permalink');return false;\"><span class='tooltip-text'>kopírovat odkaz</span></i>&nbsp;
             &nbsp;&nbsp;$x->kdy $flags</span>
             <b>$x->nadpis:</b><div class='clear'></div>$img 
               <p>$x->abstract</p>
           </a>
         </div>";
  }
  if ($wrap_container) $h.= "</div>";
  if ( $FREE ) {  // překlad na globální odkazy
    $h= preg_replace("/(src|href)=(['\"])fileadmin/","$1=$2https://www.setkani.org/fileadmin",$h);
  }
  return $h;
}
# ---------------------------------------------------------------------------------------- akce info
# uvozující "abstrakt" k výběru
function akce_info($typ,$pocet) { trace();
  global $mode, $usergroups;
  $abstr= $mode[1] ? 'abstr' : 'abstr-line';
  if ( $typ=='hledej' ) {
    $n= $pocet ? "Bylo nalezeno $pocet článků" : "Nic nebylo nalezeno";
    $mrop= strpos($usergroups,'6')===false ? ''
        : "<br><span class='input'>6</span> = články viditelné jen iniciovaným chlapům";
    $info= <<<__EOD
        <br>
        <div class='help'>
          <b>$n<br>
          <br>Pár rad pro úspěšné hledání:</b><br>
          <br>Na velikosti písmen záleží. Vzor může být doplněn znakem * nebo uvozen pomlčkou.
          <br>
          <br><b>Příklady</b>:<br><span class='input'>Seno*</span> = něco v Senoradech
          <br><span class='input'>chlap* -Brn*</span> = něco pro chlapy a ne v Brně
          $mrop&nbsp;
      </div>
      <br>
__EOD;
  }
  elseif ( $typ=='foto' ) {
    $pic= "<img alt='fotky' src='./fileadmin/icons/picasa.png' style='margin:0.1em .5em 0 0.1em;float:left;width: 20px;'>";
    $sty= "style='clear:both;display:block'";
    $info= <<<__EOD
        <div class='help'>
          <b>Fotografie z akcí naleznete také na následujících webech:</b>
          <br><br>
          <a class='styled' target="picasa" $sty href="http://manzelska.setkani.org/o-nas/galerie">$pic Galerie Manželských setkání</a>
          <a class='styled' target="picasa" $sty href="http://dum.setkani.org/o-nas/galerie">$pic Galerie Domu setkání</a>
          </b>
      </div>
__EOD;
  }
  return $info;
}
# ------------------------------------------------------------------------------------------- status by int
# 1... event was canceled  2... event is full
function status_class($status) {
  switch ($status) {
    case 1: return "relative status_cancelled";
    case 2: return "relative status_full";
    default:
      return "";
  }
}

# ==========================================================================================> vlakno
# vlákno je tvořeno záznamem v CASE a záznamy v PART:
#   A-záznam = základní text
#   F-záznam = fotky
#   D-záznam = doplnění textu
#   T-záznam = přihlašovací tabulka
# vlákno se zobrazí po kliknutí na abstrakt: clanky,akce
# ------------------------------------------------------------------------------------------- vlakno
# typ=akce|clanek|foto
#   pro typ=foto se zobrazí tag=A jako abstrakt
# back=1 přidá návrat při kliknutí
# $h2titler - zobrazí h2 stylem h1
function vlakno($cid,$typ='',$back_href='', $h1 = false, $h2titler = false) { trace();
  global $CMS, $href0;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $news_time;
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $dnes= date('Y-m-d');
  $xx= array();
  $h2titler = $h2titler ? ' class="vlakno-titler"' : ' class="vlakno-title"';
  $uid_a= 0;    // uid A-záznamu
  $spec= 0;     // vlákna s chráněným přístupem
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $cr= pdo_qry("
    SELECT p.uid,fe_groups,c.type,p.title,p.text,p.author,FROM_UNIXTIME(date),p.tags,
      p.deleted,p.hidden,fromday,untilday,FROM_UNIXTIME(fromday),id_akce,a.web_prihlasky,status,
      IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),'')
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON c.uid=p.cid
    LEFT OUTER JOIN ezer_db2.akce AS a ON a.id_duakce=p.id_akce
    WHERE cid='$cid' $p_show $groups
    ORDER BY tags,date
  ");
  while ( $cr && (
      list($uid,$fe_group,$type,$title,$text,$autor,$psano,$tags,$del,$hid,$uod,$udo,$od,$ida,$prihlaska,$status,$upd)
          = pdo_fetch_row($cr)) ) {
    $kdy= $ex= '';
    $ex.= $del ? 'd' : '';
    $ex.= $hid ? 'h' : '';
    $text= web_text($text);
    if ( $type==2 && ($tags=='A' || $tags=='K')  ) {
      $kdy= datum_akce($uod,$udo);
//       $kdy= "<span class='datum'>"
//           . sql_date1($od) . ($dnu==1 ? '' : ($dnu<5 ? " - $dnu dny" : " - $dnu dnů"))
//           . "</span> ";
    }
    if ( $tags=='A' || $tags=='K' ) $uid_a= $uid;
    if ( ($tags=='A' || $tags=='K') && $fe_group ) $spec++;
    $xx[]= (object)array('uid'=>$uid,'nadpis'=>$title,'obsah'=>$text,'tags'=>$tags,'ex'=>$ex,
        'kdy'=>$kdy,'od'=>$od,'autor'=>$autor,'psano'=>sql_date1($psano),'ida'=>$ida,'prihlaska'=>$status==1 ? null : $prihlaska,
        'status'=>status_class($status),'upd'=>$upd);
  }
//                                                         debug($x,"vlákno=$cid");
  $found= count($xx)." článků" . ($spec ? " ($spec)" : '');
//   $back_href= "$href0$go_back";
//  $back= $back_href ? "onclick=\"go(arguments[0],'$back_href','');\"" : '';
  $h= "<div id='vlakno' class='x bg_white'>";
  foreach($xx as $x) {
//                                                         debug($x,"$i");
    $style= '';
    $uid= $x->uid;
    $obsah= $x->obsah;
    $port = ""; //$_SERVER['SERVER_PORT'] == "80" ? "" : ":".$_SERVER['SERVER_PORT'];
    $permalink = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$port."/clanek/".$x->uid;

    $podpis= "<div class='podpis'><i class=\"fa fa-link tooltip clickable\" style='color: darkgray' onclick=\"copyTextToClipboard('$permalink');return false;\"><span class='tooltip-text'>kopírovat odkaz</span></i>&emsp;";
    $podpis.= ($x->kdy) ? "<i class='fa fa-calendar-alt tooltip'><span class='tooltip-text'>datum akce</span></i>&nbsp;$x->kdy&emsp;" : '';
    $podpis.= "<i class='fa fa-user tooltip'><span class='tooltip-text'>autor</span></i>&nbsp;$x->autor,&nbsp;$x->psano</div>";
    $menu= '';
    $event= '';
    $code= cid_pid($cid,$uid);
    if ( ($x->tags=='A' || $x->tags=='D' || $x->tags=='K') && ($typ=='clanek' || $typ=='hledej')) {
      if ( $CMS )
        $menu= " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['editovat článek',function(el){ opravit('$typ','$uid','$cid'); }],
              ['přidat pokračování',function(el){ pridat('part','$cid'); }],
              ['přidat fotky',function(el){ pridat('foto','$cid'); }],
           // ['-posunout nahoru',function(el){ nahoru('$typ','$uid','$cid'); }],
           // ['posunout dolů',function(el){ dolu('$typ','$uid','$cid'); }],
              ['-skrýt článek',function(el){ skryt('$typ','$uid',1); }],
              ['zobrazit článek',function(el){ skryt('$typ','$uid',0); }],
              ['-zahodit článek',function(el){ zrusit('$typ','$uid',1); }],
              ['obnovit článek',function(el){ zrusit('$typ','$uid',0); }],
              ['-odstranit embeded img',function(el){ opravit('img','$uid','$cid'); }]
            ],arguments[0],'clanek$uid');return false;\"";
      $titleh1 = $h1 ? "<h1>$x->nadpis</h1>" : "";
      $titleh2 = $h1 ? "" : "<h2$h2titler>$x->nadpis</h2>";
      $h.= "<div id='list' class='x relative' $event><span class='anchor' id='anchor$uid'></span>
           $titleh1 $code
           <div id='clanek$uid' class='clanek x$x->upd'$menu$style>
            <div class='text'>
              $titleh2$podpis
              $obsah
            </div>
          </div></div>";
    }
    elseif ( ($x->tags=='A' || $x->tags=='D' || $x->tags=='K') && $typ=='akce') {
      $menu= '';
      if ( $CMS ) {
        //check if article already copied to WP

        $menu_wp_ms= $menu_wp_ds= "kopírovat na";
        $ds_exists= $ms_exists = 0;
        $cr= pdo_qry("SELECT copy_web_id FROM wordpress.wp_shared_posts  WHERE post_id='$cid'");
        while ( $cr && (list($web_id) = pdo_fetch_row($cr)) ) {
          switch ($web_id) {
            case 2: $menu_wp_ds = "aktualizovat pro";
              $ds_exists = 1;
              break;
            case 3: $menu_wp_ms = "aktualizovat pro";
              $ms_exists = 1;
              break;
          }
        }
        $tabulku= $x->od>=$dnes ? "['přidat tabulku',function(el){ pridat('table','$cid'); }]," : '';
        $menu= " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['editovat akci',function(el){ opravit('$typ','$uid','$cid'); }],
              ['přidat pokračování',function(el){ pridat('part','$cid'); }],
              ['přidat fotky',function(el){ pridat('foto','$cid'); }],
              $tabulku
          //  ['-posunout nahoru',function(el){ nahoru('$typ','$uid','$cid'); }],
          //  ['posunout dolů',function(el){ dolu('$typ','$uid','$cid'); }],
              ['-skrýt akci',function(el){ skryt('$typ','$uid',1); }],
              ['zobrazit akci',function(el){ skryt('$typ','$uid',0); }],
              ['-zahodit akci',function(el){ zrusit('$typ','$uid',1); }],
              ['obnovit akci',function(el){ zrusit('$typ','$uid',0); }],
              ['-odstranit embeded img',function(el){ opravit('img','$uid','$cid'); }],
              ['-$menu_wp_ms web MS',function(el){ sdilet('ms','$cid',$ms_exists); }],
              ['$menu_wp_ds web DS',function(el){ sdilet('dum','$cid',$ds_exists); }]
            ],arguments[0],'clanek$uid');return false;\"
            ";
      }
      $prihlaska= '';
//      if ( $x->ida && isset($_SESSION['web']['try']) && $_SESSION['web']['try']=='prihlasky') {
      //TODO FOR SOME REASON USING DIRECT ACCESS IN THE CONDITION DID NOT WORK!!!
      $a = $x->ida;
      $b = $x->prihlaska;
      $AKCE= array(); // NENABÍZET: 3094=LK MS YS 2025
      if ( $a && $b && !in_array($x->ida,$AKCE)) {
        list($nazev_akce,$web_online,$web_prihlasky)= select(
            "TRIM(nazev),IF(web_online!='' AND web_online RLIKE '\"p_enable\":1',1,0),web_prihlasky",
            'akce',"id_duakce=$x->ida",'ezer_db2');
        // test online přihlášek verze 2
        $AKCE= array(3120,3056); // TESTOVAT: 3120=Krtci, 3056=Ostrava, 3094=LK MS
        if (in_array($x->ida,$AKCE) && $_COOKIE['martin']==1) { 
//          error_reporting(-1);
          $prihlaska= app_form_ref("<i style='vertical-align:2px' class='fa fa-star-o'></i> ONLINE PŘIHLÁŠKA",$a);
        }
        else {
          if (!in_array($x->ida,$AKCE))
          $prihlaska= cms_form_ref("ONLINE PŘIHLÁŠKA",'akce',$x->ida,$nazev_akce);
        }
      }
      $wp_presence = ($ds_exists) ? "<span style='position: absolute; right: 0; color: #47a369'>Kopie na webu DS&nbsp;</span>" : "";
      if ($ms_exists) {
        if ($wp_presence) $wp_presence .= "| ";
        $wp_presence .= "<span style='position: absolute; right: 0; color: #c47b19'>Kopie na webu MS&nbsp;</span>";
      }

      $titleh1 = $h1 ? "<h1>$x->nadpis</h1>" : "";
      $titleh2 = $h1 ? "" : "<h2$h2titler>$x->nadpis</h2>";
      $h.= "<div class='x relative' $event><span class='anchor' id='anchor$uid'></span>
            $titleh1 $code 
            <div id='clanek$uid' class='akce_prehled clanek x$x->upd'$menu$style>
              $wp_presence $prihlaska 
              <div class='text $x->status'>
                $titleh2 $podpis
                $obsah
              </div>
           </div></div>";
    }
    elseif ( $x->tags=='A' && $typ=='foto') {
      $ex= strpos($x->ex,'d')!==false ? ' abstrakt_deleted' : (
      strpos($x->ex,'h')!==false ? ' abstrakt_hidden' : '');
      $abstract= ($x->obsah) ? xi_shorting($x->obsah,$img) ." <b>pokračování pod odkazem</b>" :
          '<b>detaily akce naleznete pod tímto odkazem</b>';
      $data = pid2menu($x->uid);
      $jmp= $CMS
          ? "onclick=\"go_anchor(arguments[0],'$data->page','$data->direct_url');\""
          : "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $h .= "<div class='abstr_line relative'><span class='anchor' id='anchor$uid'></span>
         <h2$h2titler>$x->nadpis</h2>
         $code
         <div class='abstrakt_foto$ex' $jmp>
            <span class='datum'>$x->kdy</span> $img $abstract 
         </div></div>";
    }
    elseif ( $x->tags=='F' ) {
      $galery= show_fotky2($uid,$obsah,"$back_href!$uid_a#vlakno");
      $podpis= "<div class='podpis'><i class='fa fa-user tooltip'><span class='tooltip-text'>autor</span></i>&nbsp;$x->autor, $x->psano</div>";
      $note= $CMS ? "<span style='float:right;color:red;font-style:italic;'>
            ... v režimu editace stránky je fotogalerie zobrazena zjednodušeně</span>" : '';
      if ( $CMS ) {
        $menu= " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['organizovat fotky',function(el){ opravit('foto','$uid','$cid'); }],
              ['-skrýt fotky',function(el){ skryt('foto','$uid',1); }],
              ['zobrazit fotky',function(el){ skryt('foto','$uid',0); }],
              ['-zahodit fotky',function(el){ zrusit('foto','$uid',1); }],
              ['obnovit fotky',function(el){ zrusit('foto','$uid',0); }]
            ],arguments[0],'clanek$uid');return false;\"";
      }
      $h.= "
        $code
        <div id='clanek$uid' class='relative galerie$x->upd'$menu><span class='anchor' id='anchor$uid'></span>
          <div class='text $x->status'>
            <h3>$x->nadpis $note</h3> $podpis
            $galery
          </div>
        </div>";
    }
    elseif ( $x->tags=='T' && $typ!='foto' ) {
      $obsah= tabulka($cid,substr($x->od,0,10));
      $style= " style='columns:1'";
      $podpis= '';
//                                                 display("$od>=$dnes");
      if ( $CMS && $x->od>=$dnes ) $menu="['upravit tabulku',function(el){ opravit('table',0,'$cid'); }],";
      $event= ' onclick="return false;"';
      $menu= $menu ? " oncontextmenu=\"
              Ezer.fce.contextmenu([
                $menu
              ],arguments[0],'clanek$uid');return false;\"" : '';
      $h.= "<div class='x' $event>
           <div id='clanek$uid' class='relative clanek x$x->upd'$menu$style><span class='anchor' id='anchor$uid'></span>
            $code
            <div class='text $x->status'>
              <h2$h2titler>$x->nadpis</h2>$podpis
                 $obsah
            </div>
          </div></div>";
    }
  }
  $h.= "</div>";
  return $h;
}
# ===========================================================================================> knihy
# knihy (type=3) nebo prezentace (type=7)
# ------------------------------------------------------------------------------------------- knihy
# id=pid nebo název menu
function knihy($ids,$cpid0=0,$mid=0,$backref='') { trace();
  global $CMS, $href0, $clear, $mode;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $page_mref;
  $chlapi_online= 0;
  $pid0_kapitola= -1;
  if ( $ids==='chlapi.online') {
    $chlapi_online= true;
    $pid0_kapitola= $mid;;
    $ids= '';
    $mid= 0;
  }
  $abstr= $mode[1] ? 'abstr' : 'abstr-line';
  list($cid0,$pid0)= explode(',',$cpid0);
  $AND= $mid ? "AND c.mid=$mid " : " AND c.uid=$cid0 ";
  $AND2= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $xx= array();
  $cr= pdo_qry("
    SELECT p.uid, c.uid, fe_groups, tags, author, p.title, text, kapitola, type
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE !c.deleted AND !c.hidden $AND $AND2 AND IF(c.uid='$cid0',1,tags='C')
    ORDER BY c.sorting DESC,c.uid,p.kapitola,tags
  ");
  while ( $cr && (list($pid,$cid,$fe_group,$tags,$autor,$title,$text,$kapitola,$type)
          = pdo_fetch_row($cr)) ) {
    $text= web_text($text);
    if ( $chlapi_online ? $pid0_kapitola!=$kapitola : $pid!=$pid0 ) {
      $text= x_shorting($text);
    }
    else {
      $pid0_kapitola= $kapitola;
    }
    $n= $tags=='C' ? "$autor: $title" : "$title";
    $key = '';
    if ( $tags=='C' && $fe_group )
      $key = " <i class='fa fa-key tooltip' style='color:red'><span class='tooltip-text'>soukromý článek</span></i> ";
    $xx[$cid][]= (object)array('pid'=>"$pid",'nadpis'=>$n.$key,'nadpis_plain'=>$n,'text'=>$text,
        'tags'=>$tags,'kapitola'=>$kapitola,'type'=>$type);
  }
  $found= count($xx)." knih";
//                                                         debug($xx);
  // generování stránky
  $h= "<div class='content'>";
  foreach($xx as $cid=>$xs) {
    $nadpis_cid= $xs[0]->nadpis_plain;
    $back0= ''; //"onclick=\"go(arguments[0],'$href0');\"";
    $back= '';  //"onclick=\"go(arguments[0],'$href0!$cid');\"";
    $h.= $cid==$cid0 //&& $n>1
        ? ( $cid->type!=7
            ? "<hr class='hr-text' data-content='Začátek knihy $nadpis_cid'/>"
            : "<hr class='hr-text' data-content='Začátek prezentace $nadpis_cid'/>"
        ) : '';
    foreach($xs as $i=>$x) {
      //                                                       display("články $x->ident ? $id");
      $pid= $x->pid;
      $code= cid_pid($cid,$pid);
      if ( $x->tags=='F' ) {
        $uid= $pid;
        if ( $pid0_kapitola==$x->kapitola ) {
          $galery= show_fotky2($uid,$x->text,"$back_href!$uid_a#vlakno");
          $podpis= "<div class='podpis'>$x->autor, $x->psano</div>";
          $note= $CMS ? "<span style='float:right;color:red;font-style:italic;'>
                ... v režimu editace stránky je fotogalerie zobrazena zjednodušeně</span>" : '';
          if ( $CMS ) {
            $menu= " oncontextmenu=\"
                Ezer.fce.contextmenu([
                  ['organizovat fotky',function(el){ opravit('foto','$uid','$cid'); }],
                  ['-skrýt fotky',function(el){ skryt('foto','$uid',1); }],
                  ['zobrazit fotky',function(el){ skryt('foto','$uid',0); }],
                  ['-zahodit fotky',function(el){ zrusit('foto','$uid',1); }],
                  ['obnovit fotky',function(el){ zrusit('foto','$uid',0); }]
                ],arguments[0],'clanek$uid');return false;\"";
          }
          $h.= "
            $code
            <div id='clanek$uid' class='relative galerie$x->upd'$menu><span class='anchor' id='anchor$cid'></span><div class='kniha_bg'  $back0>
              <div class='text'>
                <h2>$x->kdy $x->nadpis $note</h2>
                $galery
                $podpis
              </div>
            </div>";
        }
      } else {
        $goal= $pid==$pid0 ? 'vlakno' : "kap$pid";
        $menu= $CMS ? ( $x->tags=='C'
            ? " oncontextmenu=\"
          Ezer.fce.contextmenu([
            ['editovat úvod',function(el){ opravit('kapitola','$pid','$cid'); }],
            ['přidat kapitolu',function(el){ pridat('kapitola','$cid'); }], 
            ['přidat knize fotky',function(el){ pridat('foto','$cid',0); }] 
          ],arguments[0],'clanek');return false;\""
            : " oncontextmenu=\"
          Ezer.fce.contextmenu([
            ['editovat kapitolu',function(el){ opravit('kapitola','$pid','$cid'); }],
            ['přidat kapitolu',function(el){ pridat('kapitola','$cid'); }], 
            ['přidat kapitole fotky',function(el){ pridat('foto','$cid','$x->kapitola'); }] 
          ],arguments[0],'$goal');return false;\""
        ) : '';
        if ( $backref ) {
          $jmp= str_replace('*', "$cid,$pid", $backref);
        }
        else {
          $jmp= $CMS ? "onclick=\"go_anchor(arguments[0],"
                     . "'$href0!$cid,$pid#clanek','$page_mref/$cid,$pid#anchor$pid');\""
              : ($chlapi_online ? "href='$page_mref!$x->kapitola'" : "href='$page_mref/$cid,$pid#anchor$pid'");
          //                  : "href='$href0!$cid,$pid#clanek'";
        }
        $tit= $x->type!=7 ? "<b>$x->nadpis</b>" : '';
        $txt= $x->type!=7 ? $x->text : "###$x->text###";
        $css= $x->type!=7 ? "clanek" : 'prezentace';
        $h.= $cid==$cid0
            ? ( ($chlapi_online ? $pid0_kapitola==$x->kapitola : $pid==$pid0)
                ? "<div id='vlakno' class='x kniha_bg'$menu><div id='clanek' class='relative $css x'$back>
                    <span class='anchor' id='anchor$pid'></span><div class='kniha_bg'  $back0>
                     $code
                     <h2>$tit</h2>
                     <div class='text'>$txt</div>
                    </div></div>"
                : "<div class='relative $abstr'$menu>
                     $code
                     <a id='kap$pid' class='abstrakt x plain_href' $jmp>
                        <span class='h7'>$x->nadpis</span>$x->text
                     </a></div>"
            )
            : "<div class='$abstr'>
               $code 
               <a class='abstrakt x plain_href' $jmp>
                 <span class='h7'>$x->nadpis</span>$x->text
               </a></div>"
        ;
      }
    }
    $h.= $cid==$cid0 // && $n>1
        ? ( $cid->type!=7
            ? "</div><hr class='hr-text' data-content='Konec knihy: $nadpis_cid'/>"
            : "</div><hr class='hr-text' data-content='Konec prezentace: $nadpis_cid'/>"
        ) : '';
  }
  return $h . "</div>";
}
?>
