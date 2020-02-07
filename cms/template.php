<?php
# --------------------------------------------------------------------------------------==> def menu 
function def_menu($from_table=false) { trace();
  global $def_block;
  $letos= date('Y');
  if ( $from_table ) {
    $def_block= array();
    $mn= mysql_qry("
      SELECT mid,ref,typ,site,mref,event,nazev,next,val,elem,title 
      FROM tx_gnmenu WHERE wid=1 ORDER BY mid
    ");
    while ($mn && (
        list($mid,$ref,$typ,$site,$mref,$event,$nazev,$next,$val,$elems,$title)
            = mysql_fetch_array($mn))) {
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
        'akce'        => "hm:10:   :akce             :akce,102: Naše akce::   $def_vse:          proc=plan_rok; akce=prehled:      Akce pro rodiny, muže i ženy pořádané YMCA Setkání",
        'dum'         => "hm:11:   :alberice/dum     ::     Dům setkání:::                       menu=alberice,chystame,archiv,objednavky,ceny",
        'foto'        => 'hm:12:   :fotogalerie      ::     Foto&shy;galerie:::                  foto:                             Fotografie z akcí YMCA Setkání',
        'libr'        => 'hm:13:   :manzelak         ::     Knihov&shy;nička:bulletin::          menu=bulletin,tance,modlitby,knihy,audio,obrazy,odkazy',
        'my'          => 'hm:14:0.9:ymca-setkani     :clanek,21:O nás:::                         mclanky;-clanky=21,15,13,284,287,263:      Informace o YMCA Setkání', //o_nas',
//      'team'        => "hm:33:   :team             ::Tým :::                                   team:                             Informace pro tým",
        'a_web'       => "hm:15:   :-                ::web/admin:::                              menu=a_zmeny,a_odkazy,a_reconstr,a_sitemap",
        'a_old'       => "hm:37:   :-                ::web/oldies:::                             menu=a_ys,a_ms,a_ds,a_lb,a_ch,a_us,a_wb,a_ja,a_my",
        'a_online'    => 'hm:16:   :-                :clanek,324:chlapi.online:::                mclanky;-clanky=324:              Články na server chlapi.online',
      # Web
        'a_zmeny'     => 'sm:17:   :-                ::   změny obsahu:::                        web_zmeny',
        'a_odkazy'    => 'sm:18:   :-                ::   staré odkazy:::                        web_odkazy',
        'a_reconstr'  => 'sm:36:   :-                ::   rekontrukce:::                         web_reconstr',
        'a_sitemap'   => 'sm:19:   :-                ::   sitemap.xml:::                         web_sitemap',
        'a_ys'        => "sm:38:   :-                ::YS:::                                     mclanky;-clanky=12,14,16,17,20,246,323",
        'a_ms'        => "sm:41:   :-                ::MS:::                                     mclanky;-clanky=72,198,260,303",
        'a_ds'        => "sm:40:   :-                ::DS:::                                     mclanky;-clanky=259,108,131,132,236",
        'a_lb'        => "sm:45:   :-                ::LIB:::                                    mclanky;-clanky=313",
        'a_ch'        => "sm:44:   :-                ::CH:::                                     mclanky;-clanky=239,249,262,319,321",
        'a_us'        => "sm:43:   :-                ::U.S:::                                    mclanky;-clanky=237",
        'a_wb'        => "sm:42:   :-                ::WEB:::                                    mclanky;-clanky=251,304,264,245,244,209,210,211,213,215,216,217,219,229",
        'a_ja'        => "sm:39:   :-                ::MŠ:::                                     mclanky;-clanky=317,311,308,261,291,293,295,294,300,301,302,309,310",
        'a_my'        => "sm:46:   :-                ::ROD:::                                    mclanky;-clanky=316",
      # Dům setkání
        'alberice'    => 'sm:20:0.9:alberice/dum     :clanek,37: Albeřice:::                     mclanky;-clanky=37,28,29:         Dům setkání',
        'chystame'    => "sm:21:0.9:alberice/akce    :aakce,222: Akce v Domě::  $def_vse:        proc=aplan; akce=bude:            Akce v Domě setkání",
        'archiv'      => "sm:22:   :alberice/$letos  ::          Prožili jsme:::                 akce=aprehled:                    Archiv akcí v Domě setkání",
        'objednavky'  => 'sm:23:0.8:alberice/objednavky::        Objed&shy;návky:::              dum:                              Objednávky pobytů v Domě setkání',
        'ceny'        => 'sm:24:0.8:alberice/ceny    ::          Ceny:::                         vlakno=51:                        Ceny služeb Domu setkání',
      # knihovnička
        'bulletin'    => 'sm:25:0.9:manzelak         ::          Manželák:::                     vlakno=80:                        Manželák - časopis YMCA Setkání',
        'tance'       => 'sm:26:0.9:tance            :clanek,322:Tance:::                        mclanky;-clanky=322:              Biblické tance',    // 322=tance
        'modlitby'    => 'sm:27:0.1:modlitby         :clanek,254:Modlitby:::                     mclanky;-clanky=254:              Modlitby',    // 254=modlitby
        'knihy'       => 'sm:28:0.0:cetba            :kniha,228: Četba:::                        mknihy;-knihy=228,250,241:                Vybrané texty', // 241=články
        'audio'       => 'sm:29:0.2:audio            :kniha,242: Audio:::                        mknihy;-knihy=242:                Vybraná audia',
        'obrazy'      => 'sm:30:0.4:video            :clanek,320:Video:::                        mclanky;-clanky=320:              Videa a obrazy',
        'odkazy'      => 'sm:31:0.8:doporuceni       :clanek,320:Doporu&shy;čujeme:::            mclanky;-clanky=223,9:            Doporučujeme s podobnou tématikou',
      # speciální stránky
        'home'        => "tm:32:0.9:home             ::<i class='fa fa-home'></i> Domů:::        home:                             Akce pro rodiny, muže i ženy pořádané YMCA Setkání",
//      'kontakty'    => 'hm:33:0.9:kontakty         ::Kontakty:::                               vlakno=79:                        Kontakty na YMCA Setkání', //kontakty',
        'hledej'    => 'tm:34:     :hledej           ::<i class="fas fa-search"></i>:::                               search:                             Hledej', //kontakty',
      #'clanek'      => 'hm:35:   :-                ::-:::                                      vlakno:                           Vybraný článek',
    );
//    $def_mid= array();  // vznikne transformací i => mref
//    $def_mref= array(); // vznikne transformací mref => id
//    foreach ($def_block as $ref=>$def) {
//      list($typ_bloku,$mid,$site,$mref)= explode(':',$def);
//      $mref= trim($mref);
//      if ( $mref=='-' ) continue;
//      $def_mid[$mid]= $mref;
//      $def_mref[$mref]= $ref;
//    }
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
          'manzele'  => 'manžele:2',
          'chlapi'   => 'chlapy:3',
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
      )
  );
# zobrazíme topmenu a hlavní menu, přitom zjistíme případné doplnění cesty a její správnost
  $topmenu= $mainmenu1= $submenu= $submenu_komu= $mainmenu2= $page= $elems= $mid= $pars= $web_banner= '';
  $page_ok= false;        // je dobře definovaná aktivní stránka
//                                                         debug($path,"před corr");
# ---------------------------------------------------------------- . test pro explicitně udaný part
// goto decode_path;
  $last= count($path)-1;
  list($id,$tag)= explode('#',$path[$last].'#');
// $tag= $tag ? "#$tag" : '#vlakno';
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
    $rr= mysql_qry("
    SELECT cid, p.uid, LEFT(FROM_UNIXTIME(fromday),10) AS _od, program,
      CASE WHEN LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10) THEN 'bude' $when
      ELSE '' END
    FROM setkani4.tx_gncase_part AS p
    JOIN setkani4.tx_gncase AS c ON p.cid=c.uid
    WHERE $test
  ");
    list($cid,$pid,$od,$kdo,$kdy)= mysql_fetch_row($rr);
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
  $web_akce= '';   // 'akce' pokud je výběr rodiny,chlapi,...
  $page_mref= '';  // reference stránky pro abstrakty a návraty z článků - global
  $dnu= isset($_COOKIE['web_show_changes']) ? $_COOKIE['web_show_changes'] : 1;
  $news_time= time() - $dnu*24*60*60;
  $search= isset($_COOKIE['web_search']) ? $_COOKIE['web_search'] : '';
//                                                         display("search/c=$search");
  $search_go= "go(arguments[0],'{$href}hledej','/hledej',1,1);";

  $do_menu2 = false; //whether the menu should be placed into _1 or _2 variable
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
        $page_ok= true;
        $page= $ref;
        $elems= $elems1;
        $mid= $mid1;
        if ( $ref=='hledej' )  $page_mref= "/$mref/$search";
        $web_banner= "web_$ref";
//                                                         display("tm:web_banner='$web_banner'");
      }
      if ( $ref=='hledej' ) {
        $input= "1";
//       $search_go= "go(arguments[0],'{$href}hledej','/$mref',1,1);";
//       $jmp= "onclick=\"go(arguments[0],'$href$ref','',$input,1);\"";
        $jmp= $CMS
            ? "onclick=\"go(arguments[0],'$href$ref','/$mref',$input,1);\""
            : "href='/$mref/$search'";
      }
      else {
        $jmp= $CMS || substr($mref,0,1)=='-'
            ? "onclick=\"go(arguments[0],'$href$ref','/$mref',$input,1);\""
            : "href='/$mref'";
      }
      if ( $nazev0!='-' ) {
        $topmenu.= " <a $jmp class='jump$active$upd1'>$nazev</a>";
      }
    }
    elseif ( $typ_bloku=='hm' ) {
      list($elem)= explode(';',$elems1);
      list($typ,$ids)= explode('=',$elem.'=');
      # upřesnění cesty pro akce - z cookie nebo defaultu
      if ( $ref=='akce' ) {
        $cookie1= isset($_COOKIE[$ref]) && $_COOKIE[$ref] ? '!'.urldecode($_COOKIE[$ref]) : '';
        $cont= $cookie1 ?: $default1;
        $mref= str_replace('!','',$cont);
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
//     if ( $ref=='akce' && !$CMS ) {
//       $cookie1= isset($_COOKIE[$ref]) && $_COOKIE[$ref] ? '!'.urldecode($_COOKIE[$ref]) : '';
//       $cont= $cookie1 ?: $default1;
//       $cont= $mref= str_replace('!','',$cont);
//                                                 display("Cookie[$ref]=$cookie1 ... $default1 ... $cont");
//     }
//     else {
//       if ( $CMS || substr($mref,0,1)=='-' ) {
//         $cont= $ref.($next ? "!$next" : '');
//         $cookie= isset($_COOKIE[$ref]) ? '!'.urldecode($_COOKIE[$ref]) : '';
//                                                 display("Cookie? $ref=$cookie");
//         $cont.= $default1 ? ($cookie ?: '!'.$default1) : '';
//       }
//       else {
//         $cont= $mref;
//       }
//     }
      $active= $path[0]==$ref ? ' active' : '';
      if ( $active ) {
        if ( $title1 ) $web_title= $title1;
        if ( $a_ref ) $a_ref.= "_active";
        if ( $mref=='akce' ) $web_akce= 'akce';
        $page= $ref;
        $elems= $elems1;
        $mid= $mid1;
        $href0.= $ref;
        $web_banner= "web_$ref";
//       $page_mref= $cont;
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
          $next2= str_replace(' ','',$next2);
          $default2= str_replace(' ','',$default2);
          $elems2= str_replace(' ','',$elems2);
          $active2= $path[1]==$ref2 ? ' active' : '';
          # doplnění defaultní cesty
          $cont2= $ref2;
          if ( $active2 ) {
            if ( $title2 ) $web_title= $title2;
            if ( $a_ref2 ) $a_ref2.= "_active";
            $page_ok= true;
            $page= $ref2;
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
          // přidání kontextového menu pro přidávání aj.
          if ( $context2 ) {
            list($ctyp,$pgid)= explode(',',$context2);
//                                                                 display("> $ref $ref2");
            $on_plus= isset($def_menu[$ref2]) ? $def_menu[$ref2] : '';
            $on= " oncontextmenu=\"Ezer.fce.contextmenu([
              ['{$def_on[$ctyp]}',function(el){ vytvorit('$ctyp','$pgid','$mid2'); }]
              $on_plus
            ],arguments[0]);return false;\"";
          }
          $submenu.= $CMS || substr($mref2,0,1)=='-'
              ? " <a onclick='go(arguments[0],\"$href{$path[0]}!$cont2\",\"/$mref2\",$input,1);' "
              . "class='jump$active2$a_ref2$upd2'$on>$nazev2</a>"
              : " <a href='/$mref2' class='jump$active2$upd2'>$nazev2</a>";
        }
        array_shift($path);
      }
      elseif ( $active ) $page_ok= true;
      // přidání kontextového menu pro přidávání
      $on= " oncontextmenu='return false;'";
      if ( $context ) {
        list($ctyp,$pgid)= explode(',',$context);
        $on_plus= isset($def_menu[$ref]) ? $def_menu[$ref] : '';
        $on= " oncontextmenu=\"Ezer.fce.contextmenu([
          ['{$def_on[$ctyp]}',function(el){ vytvorit('$ctyp','$pgid','$mid1'); }]
          $on_plus
        ],arguments[0]);return false;\"";
      }
      if ( $cont ) {
        $menuitem = $CMS || substr($mref,0,1)=='-'
            ? " <a onclick='go(arguments[0],\"$href$cont\",\"/$mref\",$input,1);' "
            . "class='jump$active$a_ref$upd1'$on>$nazev</a>"
            : " <a href='/$mref' class='jump$active$upd1' onclick='go(arguments[0],\"$href$cont\",\" / $mref\",$input,1);'>$nazev</a>";
        if ($do_menu2) {
          $mainmenu2 .= $menuitem;
        } else {
          $mainmenu1 .= $menuitem;
        }
      }
      if ($active) {
        //once active, put the rest of the menu into second variable
        $do_menu2 = true;
      }
    }
  }
//                                                         display("page=$page, elems=$elems, mid=$mid, ok=$page_ok");
# zobrazíme stránku $page podle jeho $elems a $pars
  $body= '';
  $par= array_shift($path);
//                                                         debug($path,"po menu, par=$par");
//                                                         display("par=$par");
# projdeme elementy
  $vyber= $vyber_rok= '';
  $rok= '';
  foreach (explode(';',$elems) as $elem) {
    list($typ,$ids)= explode('=',$elem.'=');
    $typ= str_replace(' ','',$typ);
//                                                         display("elem:$typ=$ids, vyber=$vyber");
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

      case 'knihy': # ------------------------------------------------ . knihy
        # seznam autorů
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
        $body.= "<div class='content'><h2>Z vybraných článků</h2></div>";
        $body.= clanky('',$id,$mid);
        break;

      case 'clanek': # ------------------------------------------------ . clanek
        # článek zadaný názvem nebo uid
        $x= clanek($ids);
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
        $body.= vlakno($ids,'clanek','',false);
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
        $body.= home();
        $body.= facebook();
        break;

      case 'team':    # ----------------------------------------------- . team
        $id= array_shift($path);
        list($id)= explode('#',$id);
        $body.= "týmové informace";
        break;

      case 'foto':    # --------------------------------------------==> . foto
        # seznam akcí s fotkami
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        $body.= "<div class='content'><h1>Fotogalerie</h1></div>";
        $id= array_shift($path);
        $body.= akce('foto',$ids,$id);
        break;

      case 'search': # ------------------------------------------------ . search
        # seznam nalezených abstraktů článků nebo akcí
        # může následovat ident jednoho z článků (vznikne kliknutím na abstrakt)
        $body.= "<div class='content'><h1>Výsledky hledání &nbsp;<b>$search</b></h1></div>";
        $id= array_shift($path);
        list($id)= explode('#',$id);
//                                                 display("page_mref/s=$page_mref");
        $body.= akce('hledej',$ids,$id,'',$search);
        $body.= facebook();
        break;

      case 'akce':   # ------------------------------------------------ . akce
        # seznam akcí podle proměnné $vyber
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
          $body.= "<div class='content'><h1>YMCA Setkání - naše akce</h1>";
          $body .= akce_kalendar();
          $body .= "</div>";
          $body.= akce_prehled($vyber_rok,$rok,$id);
        }
        elseif ( $ids=='aprehled' ) { // proběhlé akce v Domě setkání
                                                 debug($path,"path= $id,...");
          $body .= "<div class='content'><h1>Archiv akcí v domě</h1></div>";
          $rok= $id?:date('Y');
          $id= array_shift($path);
          list($page_mref,$roks)= explode('/',$page_mref);
          $body.= akce_prehled('dum',$rok,$id);
        }
        else {
          $kdy= $ids=='bude' ? $ids : '';
          $body.= akce($vyber,$ids,$id);
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
        $proc_kdo= function ($x,$level,$kdy='') { trace();
          global $def_pars,$href0, $kernel;
          display("kdo($x) - $href0 - $kernel");
          $html= "<div id='vyber' class='x'><div class='content'>";
          $html.= "<span>&emsp; &emsp; prostor pro &emsp;</span>";
          foreach($def_pars['komu'] as $id=>$nazev_i) {
            list($nazev,$i)= explode(':',$nazev_i);
            $alberice= $i==6 ? " style='display:none'" : '';
            $nazev= $i==6 ? '' : $nazev;
            if ( $i==6 ) continue;
            $checked= strpos($x,$id)!==false ? ' checked' : '';
//         if ( $kdy ) {
            $on= $kernel=='ezer3.1'
                ? " onchange='jQuery(this).parent().toggleClass(\"checked\");history_push(\"$href0\",\"komu\",$level,\"$kdy\");'"
                : " onchange='this.parentNode.toggleClass(\"checked\");history_push(\"$href0\",\"komu\",$level,\"$kdy\");'";
            $html.= "<label class='$checked'>$nazev
                     <input name='komu' data-value='$i' value='$id' type='checkbox'$checked$on$alberice>
                   </label>";
//         }
//         else {
//           $on= " onclick='history_push(\"$href0\",\"komu,bylo\",$level);'";
//           $html.= "<label>$nazev<input name='komu' data-value='$i' value='$id' type='checkbox'$checked$on$alberice></label>";
//         }

//         if ( $kdy ) {
//           $on= " onclick='history_push(\"$href0\",\"komu\",$level,\"$kdy\");'";
//           $html.= "<input name='komu' data-value='$i' value='$id' type='checkbox'$checked$on$alberice>"
//                 . "<label>$nazev</label>";
//         }
//         else {
//           $on= " onclick='history_push(\"$href0\",\"komu,bylo\",$level);'";
//           $html.= "<input name='komu' data-value='$i' value='$id' type='checkbox'$checked$on$alberice>"
//                 . "<label>$nazev</label>";
//         }
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
            $vyber= array_shift($path);
            $submenu_komu.= $proc_kdo($vyber,1);
            break;

          case 'plan_rok':  # ------------------------------------------- . proc plan = komu
            $vyber= array_shift($path);
            $vyb= explode(',',$vyber);
            $rok= '';
            foreach ( $vyb as $i=>$x) {
              if ( is_numeric($x) || $x=='nove' ) {
                $rok= $x;
                unset($vyb[$i]);
              }
            }
            $vyber_rok= implode(',',$vyb);
            display("plan_rok:$vyber_rok+$rok");
            $submenu_komu.= $proc_kdo($vyber_rok,1,$rok);
            break;

          case 'aplan':  # ---------------------------------------------- . proc aplan = alberice,bude
            $body .= "<div class='content'><h1>Plánované akce v domě</h1></div>";
            $vyber= "alberice,".array_shift($path);
//       $submenu_komu.= $proc_kdo($vyber,2);
            break;

//     case 'zpet':  # ----------------------------------------------- . proc zpet = komu,bylo
//       $vyber= array_shift($path);
//       $submenu_komu.= $proc_kdo($vyber,1);
//       $submenu_komu.= $proc_kdy($vyber,1);
//       break;

//     case 'azpet':  # ---------------------------------------------- . proc azpet = alberice,bylo
//       $vyber= "alberice,".array_shift($path);
//       $submenu_komu.= $proc_kdy($vyber,2);
//       break;

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
      "cms/img/web_local.png","cms/img/web_local.png")[$ezer_server];
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
      <div id='order' $attrs>
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
      "http://xxx.setkani.org",
      "http://setkani4.doma",
      "http://setkani4.bean:8080",
      "http://setkani4m.bean:8080"
  );
  $base= $base[$ezer_server];
  $web_title= trim($web_title);
  global $kernel;
  $onLoad= $kernel=='ezer3.1'
      ? "jQuery(document).ready( function() { fe_init(); });"
      : "window.addEvent('load', function() { fe_init(); });";
// Google Analytics
  $GoogleAnalytics= in_array($ezer_server,array(0,1,2,3,4)) ? '' : <<<__EOD
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-99235788-1', 'auto');
    ga('send', 'pageview');
__EOD;
  $head=  <<<__EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=9" />
  <meta name="viewport" content="width=device-width,user-scalable=yes,initial-scale=1" />
  <base href="$base" />
  <title>$web_title</title>
  <link rel="shortcut icon" href="$icon" />
  
  $eb_link
  <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap&subset=latin-ext" rel="stylesheet">  <!--font-family: 'Open Sans', sans-serif;-->
  <script src="https://kit.fontawesome.com/5e96e07517.js" crossorigin="anonymous"></script> <!-- Font Awesome Icons -->
   
  <link rel="stylesheet" href="cms/web.css" type="text/css" media="screen" charset="utf-8">
  <link rel="stylesheet" href="cms/web_edit.css" type="text/css" media="screen" charset="utf-8">
  <script>
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
  $navrcholu= in_array($ezer_server,array(0,1,2,3,4)) ? '' : <<<__EOD
  <a href="http://navrcholu.cz/Statistika/75293/" class="mereni" target='statistika'>
    <img src="https://s1.navrcholu.cz/hit?site=75293;t=o14;ref=;jss=0"
      width="14" height="14" alt="NAVRCHOLU.cz" style="border:none" />
  </a>
__EOD;

// menu pro změnu vzhledu, přihlášení ...
  $choice_js= "bar_menu(arguments[0],'menu_on'); return true;";
  $loginout= ($fe_user || $be_user)
      ? "<span onclick=\"be_logout('$currpage');\" class='separator'>
       <i class='fa fa-power-off'></i> odhlásit se</span>"
      : "<span onclick=\"bar_menu(arguments[0],'fe_login');\" class='separator'>
       <i class='fa fa-user'></i> přihlásit se</span>"
      . ( ip_watch($ip,0)
          ? "<span onclick=\"bar_menu(arguments[0],'be_login');\">
           <i class='fa fa-user-plus'></i> přihlásit se k editaci</span>" : ''
      )
      . ( 1 // ip_watch($ip,0)
          ? "<span onclick=\"bar_menu(arguments[0],'me_login');\">
           <i class='fa fa-user-secret'></i> přihlásit se emailem</span>" : ''
      );
  $menu= "
  <span id='bar_menu' data-mode='$mode[1]' onclick=\"$choice_js\" oncontextmenu=\"$choice_js\">
    <i class='fa fa-bars' id='bar_image'></i>
  
    <div id='bar_items'>
       <div id='mobile_menu'>
       $mainmenu1
       <div id='mobile_submenu'>
           $submenu
       </div>
       $mainmenu2
    </div>
      <span onclick=\"bar_menu(arguments[0],'new1');\"><img src='cms/img/new.png'> změny za den</span>
      <span onclick=\"bar_menu(arguments[0],'new7');\"><img src='cms/img/new.png'> změny za týden</span>
      <span onclick=\"bar_menu(arguments[0],'new30');\"><img src='cms/img/new.png'> změny za měsíc</span>
      <span onclick=\"bar_menu(arguments[0],'grid');\" class='separator'><i class='fa fa-th'></i> akce jako mřížka</span>
      <span onclick=\"bar_menu(arguments[0],'rows');\"><i class='fa fa-bars'></i> akce jako řádky</span>
      $loginout
    </div>
  </span>";

// body
  $footer = footer();

  $body= <<<__EOD
  <div id='page'>
    $body
   
    <div id='user_login' style="display:$fe_user_display">
      <span>Přihlášení uživatele</span>
      <br><input name='username' placeholder='Uživatelské jméno' id='name' value='' type='text'>
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
  </div>

  $footer 
    
  <div id="page_footer_bar" class="container footer" style="background:black">
    <div class="content white">
      $navrcholu            
      Vaše dary na účet <img src="cms/img/bank.png" class="footerIcon">  <b>2400465447/2010</b> nám pomáhají uskutečňovat naše programy pro vás i vaše blízké
      <span id='site_signature'> YMCA setkání, 2019&emsp;</div>
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

// upozornění na testovací verzi
  $demo= '';
//if ( $ezer_server==2 ) {
//  $click= "jQuery('#DEMO').fadeOut(1000).delay(2000).fadeIn(1000);";
//  $dstyle= "left:0; top:0; position:fixed; transform:rotate(320deg) translate(-128px,-20px); "
//      . "width:500px;height:100px;background:orange; color:white; font-weight: bolder; "
//      . "text-align: center; font-size: 40px; line-height: 96px; z-index: 16; opacity: .5;";
//  $demo= "<div id='DEMO' onclick=\"$click\" style='$dstyle'><u>ostrá</u> verze</div>";
//}

  $gallery = gallery();

//submenu obsahuje submenu kromě výběru proc_kdo
  if ($submenu) $submenu = "<div id='page_sm' class='mobile_nodisplay'><div class='content'>$submenu</div></div>";

// dokončení stránky
//                                                         display("**:web_banner='$web_banner'");
  if ( $echo ) {
    $info= "$page_kb KB, $page_ms ms";
    echo <<<__EOD
$head 
<body>
  <div id='web-shadow'></div>

  $gallery 
  $demo
    <a href="/" alt="domů"><img id='logo_ymca' src='cms/img/ymca_zakladni.png' alt='YMCA'></a>
    <a href="/" alt="domů"><img id='logo_setkani' src='cms/img/husy_bile.png' onclick="change_info();"  alt='YMCA Setkání'></a>
    <div id='menu'>
      <div class='content'>
        <div id='page_tm' class='x'>
          $menu
          
           $topmenu
          <form id='search_form' class='inline' onsubmit="searchByQuery()">   
            <input id='search' size=10 value='$search' title='hledané slovo' placeholder='Hledat...'  onchange="$search_go" /> 
            <!-- input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" /-->
          </form>
          
        </div>
        <div id='page_hm' class='x mobile_nodisplay'>
          $mainmenu1 $mainmenu2
        </div>
      </div>
      <div class='clear'></div>
       $submenu
  </div>
  <div id='web' class='$web_banner container'>
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
          $menu
          $topmenu
          <form id='search_form' class='inline' onsubmit="searchByQuery()">   
            <input id='search' size=10 value='$search' title='hledané slovo' placeholder='Hledat...'  onchange="$search_go" /> 
            <!-- input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" /-->
          </form>
        </div>
        <div id='page_hm' class='x mobile_nodisplay'>
          $mainmenu1 $mainmenu2
        </div>
      </div>
      <div class='clear'></div>
      $submenu
    </div>
    <div id='web' class='$web_banner'>
      $submenu_komu
      <div id='info' onclick='change_info();'><br>$ms</div>
      $body
    </div>
__EOD;
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
  $cms_root= $kernel=='ezer3.1' ? 'ezer3.1' : 'ezer3';
  $k3= $kernel=='ezer3.1' ? '3' : '';
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
    <script src="cms/cms{$k3}.js" type="text/javascript" charset="utf-8"></script>
    <script src="cms/cms{$k3}_fe.js" type="text/javascript" charset="utf-8"></script>
    <script src="cms/modernizr-custom.js" type="text/javascript" charset="utf-8"></script>
    $fotorama
    <link rel="stylesheet" href="./$kernel/client/licensed/font-awesome/css/font-awesome.min.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="$cms_root/client/ezer_cms3.css" type="text/css" media="screen" charset="utf-8">
    <script src="$cms_root/client/ezer_cms3.js" type="text/javascript" charset="utf-8"></script>
    <script src="cms/custom.js" type="text/javascript" charset="utf-8"></script>
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
# ============================================================================================> GALLERY
# nageneruje fotky do záhlaví
# zatím samostantná tabulka, neví kde má brát fotky
function gallery() {
  trace();
  $directory = "fileadmin/index";
  $images = glob("$directory/*.{jpg,png,bmp}", GLOB_BRACE);

  $numOfImages = count($images);
  if ($numOfImages < 1) {
    return "";
  }

  $result = "<div id='header_gallery' class='mobile_nodisplay'>";
  $result .= "<img alt='YMCA setkání' src='$images[0]' class='act' />";
  for ($i = 1; $i < $numOfImages; $i++) {
    $result .= "<img alt='YMCA setkání' src='$images[$i]' style='display:none'/>";
  }
  return $result . "</div><div id='titler' class='mobile_nodisplay'></div><div id='gallery_shadow' class='mobile_nodisplay'></div>";
}

function facebook() {
  return <<<__EOD
    <div id='pr_bar' class='container inner_container' style='background:#bacddc'>
    <div class='content' id='facebook_content'>
      <div id="fb-root"></div>
      <script async defer crossorigin="anonymous" src="https://connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v5.0"></script>
      <div id="fb-root"></div>
      <script async defer crossorigin="anonymous" src="https://connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v5.0"></script>
      <div class="fb-page" data-href="https://www.facebook.com/dum.setkani.org/" data-tabs="timeline" data-width="300px" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false">
        <blockquote cite="https://www.facebook.com/dum.setkani.org/" class="fb-xfbml-parse-ignore">
          <a href="https://www.facebook.com/dum.setkani.org/">Dům setkání Albeřice, YMCA</a>
        </blockquote>
      </div><div class="fb-page" data-href="https://www.facebook.com/manzelska.setkani.org/" data-tabs="timeline" data-width="300px" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false">
        <blockquote cite="https://www.facebook.com/manzelska.setkani.org/" class="fb-xfbml-parse-ignore">
          <a href="https://www.facebook.com/manzelska.setkani.org/">Manželská Setkání YMCA</a>
        </blockquote>
      </div>
    </div> 
  </div>
__EOD;
}
# ============================================================================================> TIMELINE
# nageneruje fotky do záhlaví
# zatím samostantná tabulka, neví kde má brát fotky
# ============================================================================================> TIMELINE
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
        SELECT p.uid, c.uid, p.title, p.text, fromday, untilday, c.program, p.id_akce
        FROM setkani4.tx_gncase AS c
        JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
        WHERE !c.deleted AND !c.hidden AND !p.hidden AND !p.deleted
          $groups
          AND LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)
          AND tags='A'
        ORDER BY fromday, untilday
      ";
  $cr = mysql_qry($qry);
  $max_date = 0;
  while ($cr && (list($p_uid, $cid, $title, $text, $uod, $udo, $program, $ida) = mysql_fetch_row($cr))) {
    $text = x_shorting($text);
    $max_date = max($max_date, $udo);
    $xx[$cid] = (object)array('ident' => $p_uid, 'od' => $uod, 'do' => $udo, 'nadpis' => $title,
        'text' => $text, 'program' => $program, 'ida' => $ida);
  }

  $h = "<br><br><br><h2 class='float-left' style='margin-top: 0px;'>Chystáme</h2><div class='float-right legend'>akce pro&emsp;";
  foreach ($def_pars['komu'] as $ki) {
      list($k, $i) = explode(':', $ki);
      if ($i==6) $k="ostatní";
      $bgcolor = barva_programu_z_cisla($i);
      $h .= "<span class='timeline_legend' style='background: $bgcolor;'>$k</span>&emsp;";
  }
  $h.= "</div><div class='relative clear'><div class='horizontal_scroll'><div class='relative'><ul id='timeline_header'>";
  $day = 24 * 3600;
  $day_size = 7;
  $month_size = $day_size * 30;

  $to_end_month = 30 - date('j', time());
  $time = time() + ($to_end_month + 1/*sichr*/) * $day;
  $month_gap = $to_end_month *$day_size;
  $counter = date('n', $time);
  $month_name = czechMonth($counter);
  $h .= "<li class='timeline_month' style='left: ${month_gap}px'>$month_name</li>";

  do {
    $time += (($counter % 2 == 1) ? 31 : 30) * $day; //approximate month length +- few days will do
    $month_gap += $month_size;
    $month_name = czechMonth(date('n', ($time + 12*$day)));
    $h .= "<li class='timeline_month' style='left: ${month_gap}px'>$month_name</li>";
    $counter++;
  } while ($time < $max_date);

  $h .= "</div></ul><ul id='timeline'>";
  $n = 0;
  foreach ($xx as $cid => $x) {
    $n++;
    $dateCzech = datum_cesky($x->od, $x->do);
    $jmp = "href='/akce/nove/$x->ident'/";
    $in_time = $x->od - time();
    if ($in_time <= 0)  {
      $in_time = 0;
    }
    $ends_in = $x->do - time();
    $duration = $ends_in - $in_time;
    $width = ($duration / $day) * $day_size;
    $gap = ($in_time / $day) * $day_size;
    //
    $color = barva_programu($x->program);
    $date = datum_cesky($x->od, $x->do);
    $h .= "<li>
                <input class='timeline_radio' id='akce$n' name='akce' type='radio'>
                <label  for='akce$n'  class='timeline_circle' onclick='(function(){
                    var radio = document.getElementById(\"akce$n\");  
                    radio.checked = !radio.checked; 
                })();return false;'
                 style='margin-left: ${gap}px;'>  
                  <label class='timeline_title' for='akce$n'>$x->nadpis</label>
                  <span class='timeline_date'>$date</span>
                  <div class='timeline_text'>
                    <div class='timeline_text_style'>
                      <span class='post_date'>$dateCzech</span>
                      <b style='float:left;'><a $jmp>$x->nadpis</a></b><br>
                      <p class='clear'><a $jmp>$x->text</a></p>
                    </div>
                  </div>
                </label> 
                <div class='timeline_bar' 
                    style='width:${width}px;margin-left: ${gap}px; background:${color}'></div>
            </li>";
  }
  $h .= "</ul></div></div>";
  return $h;
}

# ============================================================================================> FOOTER
# footer ze své vlastní tabulky
function footer() {
  trace();
  $contacts = '';
  $organizations = '';
  $query = mysql_qry("
    SELECT title, text, part
    FROM setkani4.footer
    ORDER BY part, sorting DESC
  ");

  while ( $query && (list($title, $text, $part) = mysql_fetch_row($query)) ) {
    if ($part == 'C') {
      $contacts .= "<div class='tile'><h3>$title</h3>$text</div>";
    } else if ($part == 'O') {
      $organizations .= ($title) ? "<h3>$title</h3>" : "" . $text;
    }
  }
  return "<div id='page_footer_info' class='container footer'>
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
  $xx= array();
  $vite= array();       // pole kandidátů na obsazení pole "Víte, že ..." - zobrazí náhodně seřazené
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $cr= mysql_qry("
    SELECT p.pid, p.uid, p.cid, c.mid, m.ref, m.mref, c.type, p.homepage, p.title, p.text, p.abstract,
      c.fromday, c.untilday, c.program, p.id_akce,
      IF(c.tstamp>$news_time,IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),''),
      IF(LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10),'nove',YEAR(FROM_UNIXTIME(fromday)))
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    LEFT JOIN setkani4.tx_gnmenu AS m USING (mid)
    WHERE tags='A' AND !c.deleted AND !c.hidden $p_show
      AND (p.pid=100 OR (p.homepage>0 AND p.homepage NOT IN (5)))
      AND fe_groups IN ($usergroups)
    ORDER BY IF(p.pid=100,2,IF(homepage=6,0,1)),
    CASE
      WHEN homepage IN (2) THEN untilday
      WHEN homepage IN (1,6,7,8) THEN -c.sorting
      ELSE 0
    END,
    untilday DESC
  ");
  while ($cr && (list($page,$uid,$cid,$mid,$ref,$mref,$type,$home,$title,$text,
          $abstract,$uod,$udo,$program,$ida,$upd,$rok)= mysql_fetch_row($cr))) {
    $kdy= '';
    $text= web_text($text);

    if ( $page!=100 ) {
      $text= $abstract && mb_strlen($abstract)>10
          ? web_text($abstract)
          : x_shorting($text);
    }
    if ( $type==2 ) {
      $kdy= "<span class='post_date'>". datum_cesky($uod,$udo) . "</span> ";
    }
    $xx[$cid]= (object)array('uid'=>$uid,'type'=>$type,'page'=>$page,'mid'=>$mid,'ref'=>$ref,'mref'=>$mref,'ida'=>$ida,
        'nadpis'=>$title,'abstract'=>$abstract, 'text'=>$text,'home'=>$home,'program'=>$program,'kdy'=>$kdy,'upd'=>$upd, 'rok'=>$rok);
  }
  $telo= $akce= $aktual= $cist= '';

  // články obsahují odkazy, takže nemůže být použito zanoření do <a>..</a>
  foreach($xx as $cid=>$x) {
    $code= cid_pid($cid,$x->uid);
    //todo ugly, consider "main page" category
    if ( $x->page==100 ) { // ---------------------------------------- hlavní strana - úvodní článek & timeline
      $telo.= vlakno($cid,'clanek','home',false);
      $telo .= timeline();
    }
    elseif ( $x->home==2 || $x->home==6 ) { // ----------------------- abstrakt na home | nahoru
      $prihlaska= $x->ida ? cms_form_ref("on-line přihláška") : '';
      $data = query2menu($x->uid, $cid, $x->mid, $x->ref, $x->mref,$x->type,$x->program, $x->rok);
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $telo.= "<br><br>$code
           <div class='abstrakt x$x->upd' $jmp>
             $prihlaska 
             $x->text
             $clear
           </div>";
    }
    elseif ( $x->home==7 ) { // --------------------------------------- přečtěte si
      $data = query2menu($x->uid, $cid, $x->mid, $x->ref, $x->mref,$x->type,$x->program, $x->rok);
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $cist.= "$code
           <div class='abstrakt short_post x $x->upd' $jmp>
             $x->kdy<span class='post_title'>$x->nadpis</span>
             <div class='clear'></div>". masonry_text($x->text)."</div>";
    }
    elseif ( $x->home==8 ) { // --------------------------------------- víte, že
      $vite[]= vlakno($cid,'clanek','home',false);
    }
  }

  $cist= ($cist ? $cist : 'Bohužel zde zatím není žádný článek.');

  $h= <<<__EOD
  <div class='content'>
  <div id='home_telo' class='x'>$telo</div>
  
  <h2>Přečtěte si</h2>
  <div class='masonry_container x'>$cist</div>
  </div>
__EOD;
  return $h;
}
#funkce pro extrakci vhodných obrázků pro přečtete si ==> todo pouze pro text, abstrakt bývá prázdný??
                                                          #todo podobná funkce x_first_img()
function masonry_text($text) {
  $ret = '';
  $found = preg_match_all('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $text, $images);
  for ($i = 0; $i < $found; $i++) {
    if (masonry_suitable_image($images[1][$i])) {
      $ret = '<img src='.$images[1][$i].' alt="Obrázek k abstraktu"/>';
      break;
    }
  }
  $ret .= preg_replace("/<img[^>]+\>/i", "", $text);
  return $ret;
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
  $cr= mysql_qry($cq);
  while ( $cr && (list($p_uid,$cid,$fe_group,$tags,$type,$title,$text,$abstr,$del,$hid,$upd,$ds,$fs)
          = mysql_fetch_row($cr)) ) {
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
  $fokus= false;        // první vlákno nebude mít fokus aby bylo vidět menu
  foreach($xx as $cid=>$x) {
//                                                       display("články {$x->ident} ? $uid");
    $flags= '';
    $flags.= strpos($x->flags,'D')!==false
        ? " <i class='fa fa-exchange'></i> " : '';
    $flags.= strpos($x->flags,'F')!==false
        ? " <i class='fa fa-camera-retro'></i> " : '';
    $flags.= strpos($xx[$cid]->tags,'6')!==false
        ? " <i class='fa fa-key' style='color:red'></i> " : '';
    $ex= strpos($xx[$cid]->tags,'d')!==false ? ' abstrakt_deleted' : (
    strpos($xx[$cid]->tags,'h')!==false ? ' abstrakt_hidden' : '');
    $code= cid_pid($cid,$x->ident);
    if ( $back ) {
      $jmp= str_replace('*', $x->ident, $back);
    }
    else {
      $jmp_code= "go(arguments[0],'$href0!$x->ident#vlakno','$page_mref$roks/$x->ident#anchor$x->ident');";
      $jmp= $CMS ? "onclick=\"$jmp_code\""
          : "href='$page_mref$roks/$x->ident#anchor$x->ident'";
    }
    $menu= '';
    $typ= 'clanek';
    $ident= $x->ident;
    if ( $CMS )
      $menu= $x->type==3
          ? " oncontextmenu=\"
            Ezer.fce.contextmenu([
              ['otevřít knihu',function(el){ go(null,'$href0!$x->ident#vlakno','$page_mref$roks/$x->ident'); }],
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
            ? vlakno($cid,'clanek','',$fokus)
            : "<div class='$abstr x'$menu>
             $code
             <a id='abstr$ident' class='abstrakt $ex x$css $x->upd' $jmp>
               $flags <b>$x->nadpis:</b> $x->abstract
               <hr style='clear:both;border:none'>
             </a>
           </div>"
        );
    $fokus= true;
  }
  $h.= "</div>";
  return $h;
}
# ---------------------------------------------------------------------------------==> . load clanek
# přenos článku do editoru
function load_clanek($uid) { trace();
  $x= (object)array();
  list($x->uid,$x->mid,$x->tags,$x->autor,$x->nadpis,$x->obsah,$x->abstract,$psano,$od,$do,
      $x->program,$x->homepage,$x->cruser_id,$x->ctype,$x->sorting,$x->kapitola,$x->pro,
      $x->id_akce)=
      select(
          "p.uid,c.mid,tags,author,title,text,abstract,FROM_UNIXTIME(p.date),FROM_UNIXTIME(fromday),
       FROM_UNIXTIME(untilday),program,homepage,p.cruser_id,type,c.sorting,p.kapitola,c.fe_groups,
       id_akce",
          "setkani4.tx_gncase_part AS p JOIN setkani4.tx_gncase AS c ON cid=c.uid",
          "p.uid='$uid'");
  $x->od= sql_date1($od);
  $x->do= sql_date1($do);
  $x->psano= sql_date1($psano);
  debug($x,"akce=$x->nadpis");
  return $x;
}
# ---------------------------------------------------------------------------------==> . save clanek
function save_clanek($x,$uid) { trace(); //debug($x,"save_clanek");
  // konec pokud nebyla změna
  if ( !$x ) { goto end; }
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
      case 'autor':       $part[]= "author='".mysql_real_escape_string($val)."'"; break;
      case 'nadpis':      $part[]= "title='".mysql_real_escape_string($val)."'"; break;
      case 'obsah':       $part[]= "text='".mysql_real_escape_string($val)."'"; break;
      case 'abstract':    $part[]= "$elem='".mysql_real_escape_string($val)."'"; break;
      case 'id_akce':     $part[]= "id_akce='$val'"; break;
      case 'od':          $case[]= "fromday=UNIX_TIMESTAMP('".sql_date1($val,1)."')"; break;
      case 'do':          $case[]= "untilday=UNIX_TIMESTAMP('".sql_date1($val,1)."')"; break;
      case 'program':     $case[]= "$elem='".implode(',',(array)$val)."'"; break;
      case 'psano':       $sql_psano= "UNIX_TIMESTAMP('".sql_date1($val,1)."')";
        $part[]= "date=$sql_psano"; break;
      // nepodstatné pro klienty
      case 'ctype':       $upd= 0; $case[]= "type='$val'"; $type=$val; break;
      case 'cruser_id':   $upd= 0; $part[]= "$elem='$val'"; break;
      case 'id_akce':     $upd= 0; $part[]= "$elem='$val'"; break;
      case 'homepage':    $upd= 0; $part[]= "$elem='$val'"; break;
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
  // zápis o opravě
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['we']['fe_user']}','Update','$mid','$cid','$uid')");

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
function create_clanek($x) { //$pid,$autor,$nadpis,$obsah,$psano) { trace();
  debug($x,'create_clanek');
  $pid= $x->pid;
  $mid= $x->mid;
  $type=$x->ctype;
  $cruser_id= $x->cruser_id;
  $autor= mysql_real_escape_string($x->autor);
  $nadpis= mysql_real_escape_string(web_text($x->nadpis));
  $obsah= mysql_real_escape_string(web_text($x->obsah));
  $abstract= mysql_real_escape_string(web_text($x->abstract));
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
  $cid= mysql_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,kapitola,date,
      tstamp,cruser_id) VALUES ($pid,$cid,'A','$autor','$nadpis','$obsah','$abstract','$kapitola',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= mysql_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid')");
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
  $uid= mysql_insert_id();
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
  $autor= mysql_real_escape_string($x->autor);
  $nadpis= mysql_real_escape_string(web_text($x->nadpis));
  $obsah= mysql_real_escape_string(web_text($x->fname));
  $abstract= mysql_real_escape_string(web_text($x->obsah));
  $psano= sql_date1($x->psano,1);
  $program= implode(',',(array)$x->program);
  $sorting= $x->sorting;
  query("INSERT INTO setkani4.tx_gncase (pid,mid,crdate,program,cruser_id,
           type,sorting,fe_groups)
         VALUES($pid,$mid,UNIX_TIMESTAMP('$psano'),'$program','$cruser_id',
          '$type','$sorting','$pro')");
  $cid= mysql_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,date,tstamp,
      cruser_id) VALUES ($pid,$cid,'C','$autor','$nadpis','$obsah','$abstract',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= mysql_insert_id();
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
  $autor= mysql_real_escape_string($x->autor);
  $nadpis= mysql_real_escape_string(web_text($x->nadpis));
  $obsah= mysql_real_escape_string(web_text($x->obsah));
  $abstract= mysql_real_escape_string(web_text($x->abstract));
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
  $cid= mysql_insert_id();
  query("INSERT INTO setkani4.tx_gncase_part (pid,cid,tags,author,title,text,abstract,date,tstamp,
      cruser_id) VALUES ($pid,$cid,'C','$autor','$nadpis','$obsah','$abstract',
      UNIX_TIMESTAMP('$psano'),UNIX_TIMESTAMP(),$cruser_id)");
  $uid= mysql_insert_id();
  // zápis o vložení
  $date= date('YmdHis',time());
  query("INSERT INTO gn_log (datetime,fe_user,action,uid_page,uid_menu,uid_case,uid_part) VALUES
       ('$date','{$_SESSION['web']['fe_user']}','Insert','$pid','$mid','$cid','$uid')");
  return $uid;
}
# ============================================================================================> akce
# kalenář akce akce je v databázi poznačen  todo fill in
function akce_kalendar($typ='') { trace();
  global $CMS;
  global $news_time;
  $h = '';
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $cr= mysql_qry("
      SELECT p.uid, p.cid,c.type,p.text,p.author,FROM_UNIXTIME(date),p.tags,
       p.deleted,p.hidden,fromday,untilday,FROM_UNIXTIME(fromday),id_akce,
       IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),'')
      FROM setkani4.tx_gncase AS c
      JOIN (SELECT * FROM setkani4.tx_gncase_part WHERE cid='1586') AS p ON c.uid=p.cid 
      WHERE !p.hidden AND !p.deleted
      ORDER BY fromday DESC LIMIT 1
    ");
  #--todo fixme type=kalendar

  while ( $cr && (
      list($uid,$cid,$type,$text,$autor,$psano,$tags,$del,$hid,$uod,$udo,$od,$ida,$upd)
          = mysql_fetch_row($cr)) ) {

    $year_od = date("Y", $uod);
    $year_do = date("Y", $udo);
    $h.= "<div class='timeline_schedule'><h2 class='clickable' onclick='showOrHide()'><i class=\"fas fa-calendar-week\">&emsp;
              </i>Kalendář akcí $year_od - $year_do
          </h2><div id='vlakno-kalendar' class='x'>";
    $kdy= $ex= '';
    $ex.= $del ? 'd' : '';
    $ex.= $hid ? 'h' : '';
    $obsah= web_text($text);
    if ( $type==2 && $tags=='A' ) {
      $kdy= datum_akce($uod,$udo);
    }
    //todo delete?
//    $psano = sql_date1($psano);
//    $podpis= "<div class='podpis'>";
//    $podpis.= ($kdy) ? "<i class='far fa-calendar-alt'></i>&nbsp;$kdy&emsp;" : '';
//    $podpis.= "<i class='fas fa-user'></i>&nbsp;$autor,&nbsp;$psano</div>";
    $menu = '';
    $code= cid_pid($cid,$uid);
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
      $h.= "<div id='list'  class='x'>
              $code
             <div id='clanek$uid' class='clanek x $upd'$menu>
              <div class='text'>
                <!--todo include? $ podpis -->
                $obsah
              </div>
            </div></div></div></div>";
  }
  return $h . "<script>function showOrHide() {
    var kalendar = jQuery('#vlakno-kalendar');
    kalendar.slideToggle();
    kalendar.toggleClass('nodisplay');
}</script>";
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
  $h= "";
  $fokus= false;        // první rok nebude mít fokus aby bylo vidět menu
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
    $rkomu= $_vyber= array();
    foreach(explode(',',$vyber) as $kdo) {
      $ki= $def_pars['komu'][$kdo];
      if ( $ki ) {
        list($k,$i)= explode(':',$ki);
        $_vyber[]= $k;
        if ( !in_array($i,$rkomu) )
          $rkomu[]= $i;
      }
    }
    $_vyber= implode(', ',$_vyber);
    $c_komu= "0";
    $c_kdy= 1;
    if ( $rkomu ) {
      $komu= implode('|',$rkomu);
      $i= array_search('6',$rkomu);
      if ( $i===false ) {
        $c_komu= "program REGEXP '$komu'";
      }
      else {
        unset($rkomu[$i]);
        $c_komu= ($rkomu ? "program REGEXP '$komu' AND" : '')." program REGEXP '6'";
      }
    }
    if ( !$kdy ) $kdy= 'nove';
  }
  // konec překladu
  $novych= 0;
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  if ( $vyber ) {
    // výběr roků podle cílové skupiny
    $cr= mysql_qry("
      SELECT
        IF(LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10),'nove',YEAR(FROM_UNIXTIME(fromday))) AS _rok,
        SUM(IF(tags='A',1,0)),
        IF(MAX(c.tstamp)>$news_time,IF(TO_DAYS(MAX(FROM_UNIXTIME(c.tstamp)))>TO_DAYS(MAX(FROM_UNIXTIME(c.crdate))),' upd',' new'),'')
      FROM setkani4.tx_gncase AS c
      JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
      -- JOIN setkani.pages AS g ON c.pid=g.uid
      WHERE !c.deleted AND !c.hidden $p_show
        $groups AND $c_komu AND $c_kdy
      GROUP BY _rok
      ORDER BY _rok DESC  ");

    $counter = 1;
    while ( $cr && (list($rok,$pocet,$upd)= mysql_fetch_row($cr)) ) {
      $mark= $rok=='nove' ? 'nove' : "rok$rok";
      $novych+= $rok=='nove' ? $pocet : 0;
      $rok_display = $rok=='nove' ? '+' : $rok;
      if ( $kdy==$rok ) {
        // otevřený archiv
        $akci= kolik_1_2_5($pocet,"akci,akce,akcí");
        $akce= kolik_1_2_5($pocet,"akce,akcí,akcí");
        if ( $rok=='nove' ) {
          $zacatek= "Zveme vás na $akci:";
          $konec= "";
        }
        else {
          $zacatek= "Archiv $akce z roku $rok ...";
          $konec= "... konec archivu roku $rok";
        }
        $back= "onclick=\"go(arguments[0],'$href0!$vyber#$mark','');\"";
        $back= '';
        $h.= "<div id='$mark' class='timeline_bg' $back><div class='content'>
              <div class='kniha_timeline_text_open_front $upd'><span class='kniha_timeline_date_open'>$rok_display</span>$zacatek</div>";
        $h.= akce($vyber,$kdy,$id,$fotogalerie,$hledej,$chlapi,$backref);
        $h.= "<div class='kniha_timeline_text_open_back'><span class='kniha_timeline_date_open'>$rok_display</span>$konec</div></div></div>";
      }
      else {
        // uzavřený archiv
        if ( $backref ) {
          $next= str_replace('*', "$rok", $backref);
        }
        elseif ( $CMS || $typ=='hledej' ) {
          $next= $typ=='hledej'
              ? "{$href0}hledej!$rok$hledej#$mark" : ( $vyber=='dum'
                  ? "{$href0}!$rok#$mark"
                  : "{$href0}!$vyber,$rok#$mark" );
          $next= "onclick=\"go(arguments[0],'$next','$page_mref/$rok');\"";
        }
        else {
          $next= "href='$page_mref/$rok'";
        }
        $akce= kolik_1_2_5($pocet,"akce,akcí,akcí");
        $nadpis= $rok=='nove'
            ? "Připravujeme pro vás $akce - klikněte sem pro jejich seznam"
            : "Archiv $akce roku $rok";
        if ( $chlapi_online ) {
          $h.= "<div class='kniha_bg' >
                  <a class='jump' $next>$nadpis</a>
                </div>";
        }
        else {
          $h.= "<a class='kniha_bg kniha_timeline content' id='$mark' $next>
                  <div class='kniha_timeline_text $upd'><span class='kniha_timeline_date'>$rok_display</span>$nadpis</div>
                </a>";
        }
      }
      $counter++;
    }
    if ( !$novych && $vyber!='dum' && $vyber!='chlapi.online' ) {
      // nejsou nové
      $hn= "<div class='kniha_bg'>";
      $hn.= "<div class='kniha_br'><b>Pro $_vyber zatím nic dalšího nepřipravujeme</b></div>";
      $hn.= "</div>";
      $h= $hn.$h;
    }
  }
  else {
    // bez výběru
    $h.= "<br><div class='kniha_bg'>";
    $h.= "<div class='kniha_br'><b>Zvolte prosím skupinu účastníků (rodiny, manželé, ...)
            pomocí zaškrtávacích políček nad tímto textem</b></div>";
    $h.= "</div>";
  }
  $h.= "</div>";
  // navrácení textu
  $h.= $chlapi_online ? '' : $c_komu;
  return $h;
}
# --------------------------------------------------------------------------------------------- akce
# id=pid nebo název menu
# $fotogalerie je první abstrakt pro stránku
function akce($vyber,$kdy,$id=0,$fotogalerie='',$hledej='',$chlapi='',$backref='') { trace();
  global $CMS, $href0, $def_pars, $mode;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $page_mref, $news_time;
  global $ezer_path_root, $FREE;
//                                                         display("a page_mref = $page_mref");
  $vyber0= $vyber;
  list($id,$tag)= explode('#',$id.'#');
  $rok= $tag= $chlapi_url= '';
  $typ= 'akce';
  $hledej= trim($hledej);
  $xx= $xx_tags= $xx_foto= $xx_img= array();
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
  }
  elseif ( $vyber=='hledej' ) {
    $typ= 'hledej';
    $vyber= 'hledej';
    $c_komu= " 1";
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
    // překlad $komu na regexpr
    $rkomu= array();
    foreach(explode(',',$vyber) as $kdo) {
      $ki= $def_pars['komu'][$kdo];
      if ( $ki ) {
        list($k,$i)= explode(':',$ki);
        if ( !in_array($i,$rkomu) )
          $rkomu[]= $i;
      }
    }
    $c_komu= "0";
    if ( $rkomu ) {
      $komu= implode('|',$rkomu);
      $i= array_search('6',$rkomu);
      if ( $i===false ) {
        $c_komu= "program REGEXP '$komu'";
      }
      else {
//                                                           debug($rkomu);
        unset($rkomu[$i]);
        $c_komu= ($rkomu ? "program REGEXP '$komu' AND" : '')." program REGEXP '6'";
      }
    }
//                                                          display("komu=$c_komu ... $i ... {$rkomu[$i]}");
    // překlad $kdy na podmínku
    $c_kdy= 0;
    if ( is_numeric($kdy) || $kdy=='nove' ) {
      $rok= $kdy;
      $tag= $kdy=='nove' ? "#nove" : "#rok$kdy";
      $c_kdy= $kdy=='nove'
          ? " LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)"
          : " YEAR(FROM_UNIXTIME(untilday))=$kdy AND LEFT(FROM_UNIXTIME(untilday),10)<LEFT(NOW(),10)";
      $ORDER= $kdy=='nove' ? "ASC" : "DESC";
    }
    elseif ( $kdy=='bude' || $kdy=='bude_alberice' ) {
      $c_kdy= "LEFT(FROM_UNIXTIME(untilday),10)>=LEFT(NOW(),10)";
      $ORDER= "ASC";
    }
    else {
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
    SELECT p.uid, c.uid, fe_groups, tags, p.title, text,p.deleted,p.hidden,fromday,untilday,id_akce,
      IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),'')
      -- DATEDIFF(FROM_UNIXTIME(untilday),FROM_UNIXTIME(fromday))+1 AS _dnu,
      -- FROM_UNIXTIME(fromday) AS _od, FROM_UNIXTIME(untilday) AS _do
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE !c.deleted AND !c.hidden $p_show
      $groups
      AND $c_komu AND $c_kdy
      AND IF(tags='F',LENGTH(text)>3,1)
    ORDER BY fromday $ORDER
    -- LIMIT 6,2
  ";
  $cr= mysql_qry($qry);
  while ( $cr && (list($p_uid,$cid,$fe_group,$tags,$title,$text,$del,$hid,$uod,$udo,$ida,$upd)=
          mysql_fetch_row($cr)) ) {
    if ( $ida && !in_array($kdy,array('nove','bude','bude_alberice')) )
      $ida= 0;
    $xx_tags[$cid].= $del ? 'd' : '';
    $xx_tags[$cid].= $hid ? 'h' : '';
    if ( $tags=='F' ) {
      $xx_tags[$cid].= $tags;
      list($foto)= explode(',',$text);
      $xx_foto[$cid]= "fileadmin/photo/$p_uid/..$foto";
//       $path= "$ezer_path_root/fileadmin/photo/$p_uid/..$foto";
//       $xx_foto[$cid]= file_exists($path) ? "./fileadmin/photo/$p_uid/..$foto" : '';
    }
    else {
      $text= web_text($text);
      if ( $tags=='A' ) {
        $akdy= datum_akce($uod,$udo);
//         $datum= sql_date1($od);
//         $dnu= $dnu==1 ? '' : ($dnu<5 ? " - $dnu dny" : " - $dnu dnů");
//         $dnu= $dnu ? "$datum $dnu" : $datum;
        if ( $p_uid!=$id || 1 ) { //todo always true -- delete?
          $text= xi_shorting($text,$img);
          if ( $img ) {
            if ($typ=='foto') { //todo temporary solution, remove the html garbage around source --> create raw function
              preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $img, $image);
              $img = $image['src'];
            }
            $xx_img[$cid]= $img;
          }
        }
        $xx[$cid]= (object)array('ident'=>$p_uid,'kdy'=>$akdy, 'rok'=>date("Y", $uod), 'nadpis'=>$title,
            'abstract'=>$text,'upd'=>$upd,'ida'=>$ida);
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
  $found= count($xx)." akcí" . ($spec ? " ($spec)" : '');
//                                                         debug($xx);
  // případné doplnění helpu na začátek
  $info= akce_info($typ,count($xx));
  // generování stránky
  $rok_ted = '';
  $h= "<div class='content'> $info";
  $abstr= $mode[1] ? 'abstr' : 'abstr-line';
  $n= 0; // pořadí akce v roce
  // první vlákno setkani.org bez fokusu aby bylo vidět menu
  $fokus= $vyber0=='chlapi.online' || $vyber0=='chlapi.online-free' ? true : false;
  foreach($xx as $cid=>$x) {
    $n++;
//     $tagn= $tag ? "#n$n" : '';
    $tagn= "#n$n";
    $flags= $mini= ''; // $xx_tags[$cid];
    $foto= strpos($xx_tags[$cid],'F')!==false;
    if ( $foto ) {
      // překlad na globální odkazy pro ty lokální (pro servant.php)
      $http= $FREE && preg_match("/fileadmin/",$xx_foto[$cid]) ? "https://www.setkani.org/" : '';
      $mini = $xx_foto[$cid] ?
          ($typ=='foto' ?
              "style='background-image:url($http{$xx_foto[$cid]})'"
              : "<div class='mini' style='background-image:url($http{$xx_foto[$cid]})'></div>") : '';
      $flags.= "<i class='fa fa-camera-retro'></i>";
    }
    $flags.= strpos($xx_tags[$cid],'6')!==false
        ? " <i class='fa fa-key' style='color:red'></i> " : '';
    $flags.= strpos($xx_tags[$cid],'D')!==false
        ? " <i class='fa fa-exchange'></i> " : '';
    if ( $typ=='foto' && !$flags ) continue;
    if ( strpos($xx_tags[$cid],'T')!==false ) {
      $flags.= " <i class='fa fa-table'></i> ";
    }
    if ( $backref ) {
      $jmp= str_replace('*', "$rok,$x->ident", $backref);
    }
    elseif ( $FREE ) {
      $jmp= "href='$chlapi_url&id=$x->ident'";
    }
    else {
      $roks= $rok ? "/$rok" : '';
      $jmp= $CMS ? "onclick=\"go(arguments[0],'$href0{$vyber}!$x->ident$hledej#vlakno','$page_mref$roks/$x->ident#anchor$x->ident');\""
          : "href='$page_mref$roks/$x->ident#anchor$x->ident'";
      $back= $CMS ? $href0.($hledej?"$vyber!$hledej":"$vyber$tagn")
          : "$page_mref$roks";
    }
    $img= $mini ? $mini : $xx_img[$cid];
    $ex= strpos($xx_tags[$cid],'d')!==false ? ' abstrakt_deleted' : (
    strpos($xx_tags[$cid],'h')!==false ? ' abstrakt_hidden' : '');
    $code= cid_pid($cid,$x->ident);
//     $back= $foto ? "#foto$cid" : '';
    $prihlaska= $x->ida ? cms_form_ref("on-line přihláška") : '';
//    $prihlaska= cms_form_ref("on-line přihláška");

    if ($typ=='foto') {
      if ($rok_ted != $x->rok) {
        $rok_ted = $x->rok;
        $h .= "<h2>$rok_ted</h2>";
      }
      $h.= $x->ident==$id
          ? vlakno($cid,$typ,$back,$fokus)
          : "<a class='abstr-fotogalerie' id='n$n' $jmp>
               $code 
               <div class='fbg'></div>
               <div class='fimg' $img></div>
               <div class='ftitle'>$x->kdy $flags&nbsp;<b>$x->nadpis:</b></div>
               <div class='ftext'>$x->abstract</div>
         </a>";
    } else {
      $h.= $x->ident==$id
          ? vlakno($cid,$typ,$back,$fokus)
          : "<div class='$abstr' id='n$n'>
           $code 
           <a class='abstrakt$ex{$x->upd}' $jmp>
             $prihlaska
             <span class='akce_datum'>$x->kdy $flags</span>  <b>$x->nadpis:</b><div class='clear'></div>$img 
               <p>$x->abstract</p>
           </a>
         </div>";
    }
    $fokus= true;
  }
  $h.= "</div>";
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
function vlakno($cid,$typ='',$back_href='',$fokus=true) { trace();
  global $CMS, $href0;
  global $usergroups, $found; // skupiny, počet nalezených článků
  global $show_deleted, $show_hidden, $news_time;
  if ( !$news_time ) $news_time= time() - 1 * 24*60*60;
  $dnes= date('Y-m-d');
  $xx= array();
  $uid_a= 0;    // uid A-záznamu
  $spec= 0;     // vlákna s chráněným přístupem
  $p_show= ($show_hidden ?  '' : " AND !p.hidden").($show_deleted ? '' : " AND !p.deleted");
  $groups= $usergroups ? "AND fe_groups IN ($usergroups)" : 'AND fe_groups=0';
  $cr= mysql_qry("
    SELECT p.uid,fe_groups,c.type,p.title,p.text,p.author,FROM_UNIXTIME(date),p.tags,
      p.deleted,p.hidden,fromday,untilday,FROM_UNIXTIME(fromday),id_akce,
      IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),'')
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON c.uid=p.cid
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE cid='$cid' $p_show $groups
    ORDER BY tags,date
  ");
  while ( $cr && (
      list($uid,$fe_group,$type,$title,$text,$autor,$psano,$tags,$del,$hid,$uod,$udo,$od,$ida,$upd)
          = mysql_fetch_row($cr)) ) {
    $kdy= $ex= '';
    $ex.= $del ? 'd' : '';
    $ex.= $hid ? 'h' : '';
    $text= web_text($text);
    if ( $type==2 && $tags=='A' ) {
      $kdy= datum_akce($uod,$udo);
//       $kdy= "<span class='datum'>"
//           . sql_date1($od) . ($dnu==1 ? '' : ($dnu<5 ? " - $dnu dny" : " - $dnu dnů"))
//           . "</span> ";
    }
    if ( $tags=='A' ) $uid_a= $uid;
    if ( $tags=='A' && $fe_group ) $spec++;
    $xx[]= (object)array('uid'=>$uid,'nadpis'=>$title,'obsah'=>$text,'tags'=>$tags,'ex'=>$ex,
        'kdy'=>$kdy,'od'=>$od,'autor'=>$autor,'psano'=>sql_date1($psano),'ida'=>$ida,'upd'=>$upd);
  }
//                                                         debug($x,"vlákno=$cid");
  $found= count($xx)." článků" . ($spec ? " ($spec)" : '');
//   $back_href= "$href0$go_back";
//  $back= $back_href ? "onclick=\"go(arguments[0],'$back_href','');\"" : '';
  $h= "<div id='vlakno' class='x'>";
  foreach($xx as $x) {
//                                                         debug($x,"$i");
    $style= '';
    $uid= $x->uid;
    $obsah= $x->obsah;
    $podpis= "<div class='podpis'>";
    $podpis.= ($x->kdy) ? "<i class='far fa-calendar-alt'></i>&nbsp;$x->kdy&emsp;" : '';
    $podpis.= "<i class='fas fa-user'></i>&nbsp;$x->autor,&nbsp;$x->psano</div>";
    $menu= '';
    $event= '';//***$back;
    $code= cid_pid($cid,$uid);
    $id_focus= $fokus ? "id='fokus_case'" : '';
    if ( ($x->tags=='A' || $x->tags=='D') && ($typ=='clanek' || $typ=='hledej')) {
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
      $h.= "<div id='list'  class='x' $event><span class='anchor' id='anchor$uid'></span>
            $code
           <div id='clanek$uid' class='clanek x$x->upd'$menu$style>
            <div class='text'>
              <h1>$x->nadpis</h1>$podpis
              $obsah
            </div>
          </div></div>";
    }
    elseif ( ($x->tags=='A' || $x->tags=='D') && $typ=='akce') {
      $menu= '';
      if ( $CMS ) {
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
              ['-odstranit embeded img',function(el){ opravit('img','$uid','$cid'); }]
            ],arguments[0],'clanek$uid');return false;\"
            ";
      }
      $prihlaska= '';
//      if ( $x->ida && isset($_SESSION['web']['try']) && $_SESSION['web']['try']=='prihlasky') {
      if ( $x->ida ) {
        $nazev_akce= trim(select('nazev','akce',"id_duakce=$x->ida",'ezer_db2'));
        $prihlaska= cms_form_ref("on-line přihláška",'akce',$x->ida,$nazev_akce);
      }
      $h.= "<div $id_focus class='x' $event><span class='anchor' id='anchor$uid'></span>
            $code
            <div id='clanek$uid' class='clanek x$x->upd'$menu$style>
              $prihlaska
              <div class='text'>
                <h2>$x->nadpis</h2>$podpis
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
      $jmp= "onclick=\"go(arguments[0],'$data->page','$data->direct_url');\"";
      $h .= "<div $id_focus class='abstr_line'><span class='anchor' id='anchor$uid'></span>
         <h2>$x->nadpis</h2>
         $code
         <div class='abstrakt_foto$ex' $jmp>
            <span class='datum'>$x->kdy</span> $img $abstract 
         </div></div>";
    }
    elseif ( $x->tags=='F' ) {
      $galery= show_fotky2($uid,$obsah,"$back_href!$uid_a#vlakno");
      $podpis= "<div class='podpis'><i class='fas fa-user'></i>&nbsp;$x->autor, $x->psano</div>";
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
        <div id='clanek$uid' class='galerie$x->upd'$menu><span class='anchor' id='anchor$uid'></span>
          <div class='text'>
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
      if ( $CMS && $x->od>=$dnes ) $menu="
              ['upravit tabulku',function(el){ opravit('table',0,'$cid'); }],
      ";
      $event= ' onclick="return false;"';
      $menu= $menu ? " oncontextmenu=\"
              Ezer.fce.contextmenu([
                $menu
              ],arguments[0],'clanek$uid');return false;\"" : '';
      $h.= "<div class='x' $event>
           <div id='clanek$uid' class='clanek x$x->upd'$menu$style><span class='anchor' id='anchor$uid'></span>
            $code
            <div class='text'>
              <h2>$x->nadpis</h2>$podpis
                 $obsah
            </div>
          </div></div>";
    }
//     $code= cid_pid($cid,$uid);
//     $h.= "<div $attrs class='x' $event>
//            <div id='clanek$uid' class='clanek x'$menu$style>
//             $code
//             <div class='text'>
//               <h1>$x->kdy $x->nadpis</h1>$obsah
//               $podpis
//             </div>
//           </div></div>";
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
  $cr= mysql_qry("
    SELECT p.uid, c.uid, fe_groups, tags, author, p.title, text, kapitola, type
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON p.cid=c.uid
    -- JOIN setkani.pages AS g ON c.pid=g.uid
    WHERE !c.deleted AND !c.hidden $AND $AND2 AND IF(c.uid='$cid0',1,tags='C')
    ORDER BY c.sorting DESC,c.uid,p.kapitola,tags
  ");
  while ( $cr && (list($pid,$cid,$fe_group,$tags,$autor,$title,$text,$kapitola,$type)
          = mysql_fetch_row($cr)) ) {
    $text= web_text($text);
    if ( $chlapi_online ? $pid0_kapitola!=$kapitola : $pid!=$pid0 ) {
      $text= x_shorting($text);
    }
    else {
      $pid0_kapitola= $kapitola;
    }
    $n= $tags=='C' ? "$autor: $title" : "$title";
    if ( $tags=='C' && $fe_group )
      $n.= " <i class='fa fa-key' style='color:red'></i> ";
    $t= $tags=='C' ? "<br>$text" : $text;
    $xx[$cid][]= (object)array('pid'=>"$pid",'nadpis'=>$n,'text'=>$t,
        'tags'=>$tags,'kapitola'=>$kapitola,'type'=>$type);
  }
  $found= count($xx)." knih";
//                                                         debug($xx);
  // generování stránky
  $h= "<div class='content'>";
  foreach($xx as $cid=>$xs) {
    $nadpis_cid= $xs[0]->nadpis;
    $back0= ''; //"onclick=\"go(arguments[0],'$href0');\"";
    $back= '';  //"onclick=\"go(arguments[0],'$href0!$cid');\"";
    $h.= $cid==$cid0 //&& $n>1
        ? ( $cid->type!=7
            ? "<span class='anchor' id='anchor$cid'></span><div class='kniha_bg' $back0>
            <hr class='hr-text' data-content='Začátek knihy $nadpis_cid'/>"
            : "<span class='anchor' id='anchor$cid'></span><div class='kniha_bg'  $back0>
            <hr class='hr-text' data-content='Začátek prezentace $nadpis_cid'/>"
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
            <div id='clanek$uid' class='galerie$x->upd'$menu>
              <div class='text'>
                <h2>$x->kdy $x->nadpis $note</h2>
                $galery
                $podpis
              </div>
            </div>";
        }
      }
      else {
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
          $jmp= $CMS ? "onclick=\"go(arguments[0],'$href0!$cid,$pid#clanek','$page_mref/$cid,$pid#anchor$cid');\""
              : ($chlapi_online ? "href='$page_mref!$x->kapitola'" : "href='$page_mref/$cid,$pid#anchor$cid'");
          //                  : "href='$href0!$cid,$pid#clanek'";
        }
        $tit= $x->type!=7 ? "<b>$x->nadpis</b>" : '';
        $txt= $x->type!=7 ? $x->text : "###$x->text###";
        $css= $x->type!=7 ? "clanek" : 'prezentace';
        $h.= $cid==$cid0
            ? ( ($chlapi_online ? $pid0_kapitola==$x->kapitola : $pid==$pid0)
                ? "<div id='vlakno' class='x'$menu><div id='clanek' class='$css x'$back>
                 $code
                 <h2>$tit</h2>
                 <div class='text'>$txt</div>
               </div></div>"
                : "<div class='$abstr'$menu>
                 $code
                 <a id='kap$pid' class='abstrakt x' $jmp>
                   <h3>$x->nadpis</h3>$x->text
                   <hr style='clear:both;border:none'>
               </a></div>"
            )
            : "<div class='$abstr'>
               $code 
               <a class='abstrakt x' $jmp>
                 <b>$x->nadpis</b>$x->text
                 <hr style='clear:both;border:none'>
             </a></div>"
        ;

      }
    }
    $h.= $cid==$cid0 // && $n>1
        ? ( $cid->type!=7
            ? "<hr class='hr-text' data-content='Konec knihy $nadpis_cid'/></div>"
            : "<hr class='hr-text' data-content='Konec prezentace  $nadpis_cid'/></div>"
        ) : '';
  }
  return $h . "</div>";
}
?>
