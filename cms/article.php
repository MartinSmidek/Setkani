<?php




class Article {
    /**
     * Article constructor.
     * @param int $cid - ID from case table
     * @param string $typ -
     * @param string $back_href
     * @param bool $h1
     */
    function __construct($cid, $typ='', $back_href='', $h1 = false)
    {
    }

    function temp() {

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
        $cr= pdo_qry("
    SELECT p.uid,fe_groups,c.type,p.title,p.text,p.author,FROM_UNIXTIME(date),p.tags,
      p.deleted,p.hidden,fromday,untilday,FROM_UNIXTIME(fromday),id_akce,a.web_prihlasky,status,
      IF(c.tstamp>$news_time, IF(TO_DAYS(FROM_UNIXTIME(c.tstamp))>TO_DAYS(FROM_UNIXTIME(c.crdate)),' upd',' new'),'')
    FROM setkani4.tx_gncase AS c
    JOIN setkani4.tx_gncase_part AS p ON c.uid=p.cid
    LEFT OUTER JOIN ezer_db2.akce AS a ON a.id_duakce=p.id_akce
    -- JOIN setkani.pages AS g ON c.pid=g.uid
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
        $h= "<div id='vlakno' class='x'>";
        foreach($xx as $x) {
//                                                         debug($x,"$i");
            $style= '';
            $uid= $x->uid;
            $obsah= $x->obsah;
            $podpis= "<div class='podpis'>";
            $podpis.= ($x->kdy) ? "<i class='fa fa-calendar-alt'></i>&nbsp;$x->kdy&emsp;" : '';
            $podpis.= "<i class='fa fa-user'></i>&nbsp;$x->autor,&nbsp;$x->psano</div>";
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
                $titleh2 = $h1 ? "" : "<h2>$x->nadpis</h2>";
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
                if ( $x->ida && $x->prihaska) {
                    $nazev_akce= trim(select('nazev','akce',"id_duakce=$x->ida",'ezer_db2'));
                    $prihlaska= cms_form_ref("ONLINE PŘIHLÁŠKA",'akce',$x->ida,$nazev_akce);
                }
                $titleh1 = $h1 ? "<h1>$x->nadpis</h1>" : "";
                $titleh2 = $h1 ? "" : "<h2>$x->nadpis</h2>";
                $h.= "<div class='x relative' $event><span class='anchor' id='anchor$uid'></span>
            $titleh1 $code
            <div id='clanek$uid' class='akce_prehled clanek x$x->upd'$menu$style>
              $prihlaska
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
         <h2>$x->nadpis</h2>
         $code
         <div class='abstrakt_foto$ex' $jmp>
            <span class='datum'>$x->kdy</span> $img $abstract 
         </div></div>";
            }
            elseif ( $x->tags=='F' ) {
                $galery= show_fotky2($uid,$obsah,"$back_href!$uid_a#vlakno");
                $podpis= "<div class='podpis'><i class='fa fa-user'></i>&nbsp;$x->autor, $x->psano</div>";
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
              <h2>$x->nadpis</h2>$podpis
                 $obsah
            </div>
          </div></div>";
            }
        }
        $h.= "</div>";
        return $h;
    }

}
