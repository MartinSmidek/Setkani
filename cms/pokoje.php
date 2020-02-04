<?php
# ------------------------------------------------------------------------------------==> dum_server
# AJAX
# zpracování objednávek Domu setkání, viz funkce cms.js/objednavka
# operace volané ze seznamu: form,
# operace volané z formuláře: get,delete,store
# op: form
function dum_server($x) {
  global $y;
  global $CMS;
  $CMS= count($_SESSION['cms']);
  global $trace, $totrace;
  $totrace= $x->totrace;
//                                                         debug($x,"dum_server");
//                                                         display("dum_server({op:{$x->dum},...})");
  $y= $x;
  $order= $x->order;
  switch ($x->dum) {

  case 'wanted': // orders:uid,uid,...
//                                                         display(". orders:{$x->orders}");
    $flds= $vals= $del= "";
    if ( !$x->order ) list($x->order)= explode(',',$x->orders);
    dum_form($x);
    break;

  case 'create':
    $now= time();
    $flds= 'crdate,tstamp';
    $vals= "$now,$now";
    // uprav default
    if ( !$x->form->untilday )  $x->form->untilday= $x->form->fromday;
    // v x.form jsou předána všechna pole
    foreach($x->form as $fld=>$val) {
      $val= mysql_real_escape_string($val);
      if ( in_array($fld,array('fromday','untilday')) ) {
        list($d,$m,$r)= explode('.',$val);
        $val= mktime(0, 0, 0, $m, $d, $r);
      }
      $flds.= ",$fld";
      $vals.= ",'$val'";
    }
    $y->ok= query("INSERT INTO tx_gnalberice_order ($flds) VALUES ($vals)");
//                                                         display("insert=$y->ok");
    $y->order= mysql_insert_id();
    break;

  case 'delete':
    // zjisti, zda je akce prázdná
    $osob= select('COUNT(*)','ds_osoba',"id_order=$order");
    if ( $osob ) {
      $y->msg= "nelze smazat objednávku s již přiřazenými $osob osobami, napřed je odhlas v Answeru";
      $y->ok= 0;
    }
    else {
      $res= query("UPDATE tx_gnalberice_order SET deleted=1 WHERE uid=$order");
      $n= pdo_affected_rows($res);
      $y->msg= $n==1 
          ? "objednávka $order byla smazána - je třeba restartovat Answer" 
          : "objednávku $order nešlo smazat";
      $y->ok= 1;
    }
    break;

  case 'update':
    $flds= $vals= $del= "";
    // v x.form jsou předána změněná pole
    if ( $x->form ) {
      foreach($x->form as $fld=>$val) {
        $val= mysql_real_escape_string($val);
        if ( in_array($fld,array('fromday','untilday')) ) {
          list($d,$m,$r)= explode('.',$val);
          $val= mktime(0, 0, 0, $m, $d, $r);
        }
        $flds.= "$del$fld='$val'";
        $del= ', ';
      }
      $y->ok= query("UPDATE tx_gnalberice_order SET $flds WHERE uid=$order");
//                                                         display("update=$y->ok");
    }
    else {
      $y->msg= "nebyla změněna žádná hodnota";
    }
    $y->order= $order;
    break;

  case 'form':
    dum_form($x);
    break;

  default:
    $y->error= "DUM: neznama operace '{$x->dum}'";
    break;
  }
  // návrat
end:
//   if ( $trace && strpos($x->totrace,'u')!==false )
    $y->trace= $trace;
//                                         $y->trace.= "\ntotrace={$x->totrace}";
  if ( $warning ) $y->warning= $warning;
}
function dum_form($x) {
  global $y,$dum_data,$dum_data_open;
  global $trace, $totrace;
//                                                         debug($x,"dum_form");
  $ord= $x->order;
  $user= $_SESSION['web']['fe_user'];
  $spravce= $user ? access_get(1) : 0;
  $dum_data_open= 0;
  if ( $ord ) {                                                             // !!! pak jen správce
    $dum_data_open= 0; //$spravce;
    $dum_data= select("*","setkani4.tx_gnalberice_order","uid=$ord");
    $dum_data->fromday= date("j.n.Y",$dum_data->fromday);
    $dum_data->untilday= date("j.n.Y",$dum_data->untilday);
  }
  elseif ( $ord==0 ) {
    $dum_data_open= 1;
    $dum_data= (object)array();
    $dum_data->fromday= sql_date1($x->den);
    // upravíme default: odjezd=příjezd+2, adults=2
    list($r,$m,$d)= explode('-',$x->den);
    $dum_data->untilday= date("j.n.Y",mktime(0, 0, 0, $m, $d+2, $r));
    $dum_data->adults= 2;
    if ( $user ) {
      list($dum_data->name,$dum_data->telephone,$dum_data->email,
           $dum_data->address,$dum_data->zip,$dum_data->city)
        = select("CONCAT(firstname,' ',name),telephone,email,address,zip,city","fe_users","uid=$user");
    }
  }
  $y->html=
    "<table><tr><td style='padding-right: 12px'>"
  . inp("objednávka","uid",3,0)."&nbsp;&nbsp;&nbsp;"
  . sel("stav objednávky","state",
        "1:zájem o pobyt,2:závazná objednávka,3:akce YMCA,4:nelze pronajmout",$ord)."<br>"
  . inp("poznámka k objednávce","note",35)."<br>"
  . inp("příjezd",            "fromday",       8, $ord)
  . inp("odjezd",             "untilday",      8)
  . inp("pokoje",             "rooms1",       14)."<br>"
  . inp("dospělých",          "adults",        3)
  . inp("děti 10-15",         "kids_10_15",    3)
  . inp("děti 3-9",           "kids_3_9",      3)
  . inp("děti do 3 let",      "kids_3",        3)."<br>"
  . sel("typ stravy","board","1:penze,2:polopenze,3:bez stravy")
  . "</td><td>"
  . inp("jméno a příjmení",   "name",         22)
  . ( $dum_data_open || $spravce ? (
      inp("telefon",            "telephone",    14,1)."<br>"
    . inp("email",              "email",        30,1)."<br>"
    . inp("ulice",              "address",      30,1)."<br>"
    . inp("psč",                "zip",          10,1)
    . inp("obec",               "city",         16,1)."<br><br>"
  ) : '<br><br>(Osobní údaje jsou přístupné pouze pro správce Domu setkání)')
  . ( $ord && $spravce ? (
        but("Opravit","block_enable('order',1,'uid')")
      . but("Uložit opravu","objednavka(0,'update',{order:'$ord'});",0,'order_save')
      . but("Smazat","objednavka(0,'delete',{order:'$ord'});")
      . but("Zpět","block_display('order',0);", 0, '')
      ) : (
      $ord ? (
        "<br>".but("Zavřít","block_display('order',0);",1)
      ) : (
        but("Přidat objednávku","objednavka(0,'create');")
      . but("Zrušit","block_display('order',0,'uid');")
      )
    ))
  . "</td></tr></table>";
__EOD;
}
function inp($label,$name,$size,$enabled=1) { //trace();
  global $dum_data, $dum_data_open, $kernel;
  $disabled= $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : ' ');
  $onchange=  $kernel=='ezer3.1' 
      ? "onchange='jQuery(this).addClass(\"changed\");'"
      : "onchange='this.addClass(\"changed\");'";
  $html= " <label>$label<input name='$name' type='text' size='$size' $disabled $onchange
        value='{$dum_data->$name}'></label>";
  return $html;
}
function sel($label,$name,$options,$enabled=1) { //trace();
  global $dum_data, $dum_data_open, $kernel;
  $opt= "";
  foreach (explode(",",$options) as $o) {
    list($num,$id)= explode(':',$o);
    $selected= $num==$dum_data->$name ? 'selected' : '';
    $opt.= "<option value='$num' $selected>$id</option>";
  }
  $disabled= !$enabled || !$dum_data_open ? 'disabled' : ' ';
  $onchange=  $kernel=='ezer3.1' 
      ? "onchange='jQuery(this).addClass(\"changed\");'"
      : "onchange='this.addClass(\"changed\");'";
  return " <label>$label<select name='$name' type='input' $disabled $onchange>$opt</select></label>";
}
function but($label,$js,$even=0,$id_and_hide='') {
  global $dum_data, $dum_data_open;
  $id= $id_and_hide ? " id='$id_and_hide' name='$id_and_hide' hidden" : '';
  $html= " <label class='form_button'><input $id class='form_button' type='submit' value='$label' onclick=\"$js\"></label>";
//   $html= $dum_data_open || $even
//        ? " <label><input $id type='submit' value='$label' onclick=\"$js\"></label>" : '';
  return $html;
}
# -----------------------------------------------------------------------------------------==> mesic
function mesic($ym,$from,$until,$mesic,$path) {  //trace();
  global $CMS, $href0;
  $id= array_shift($path);
                                                        display("id=$id");
  popup("Objednávka","","$href0!$ym",'order');
  $obsah= gn_makeDaysList(0,0,$ym,$from,$until);
  if ( $id ) {
    if ( is_numeric($id) ) {
      $text= select("text","setkani4.tx_gncase_part","cid=$id");
      popup("Pokoje",$text,"$href0!$ym#anchor$ym");
    }
  }
  $back= "onclick=\"go(arguments[0],'$href0','');\"";
  $h= "<div id='pokoje' class='x'><span class='anchor' id='anchor$ym'></span>
         <div class='x'><div id='mesic$ym' class='pokoje x'>
           <div class='text'><h2 class='float-left' $back>$mesic</h2>$obsah</div>
         </div>
       </div></div>";
  return $h;
}
# ----------------------------------------------------------------------------------------==> mesice
# 1=zájem o pobyt, 2=závazná objednávka, 3=akce YMCA, 4=nelze pronajmout );
function mesice($path) {  trace();
  global $CMS, $href0;
  $user= $_SESSION['web']['fe_user'];
  $spravce= $user ? access_get(1) : 0;
  // počet všech pokojů - kvůli *
  $pokoju_celkem= select("COUNT(*)","tx_gnalberice_room","version=1 AND deleted=''");
  $ym0= array_shift($path);
  $month= array( 1=> "leden", "únor", "březen", "duben", "květen", "červen",
    "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

  // suma měsíců
  $mesicu= 17 + ($spravce ? 12 : 0);
  $xx= array();
  $d= mktime(0, 0, 0, date("n")-3, 2, date("Y"));

  for ($i= 0; $i <= $mesicu; $i++ ) {
    $m= date("n",$d);
    $y= date("Y",$d);
    $from= mktime(0, 0, 0, $m, 1, $y);
    $until= mktime(0, 0, 0, $m, date("t",$from), $y);
    $objednavky= array();
    // zjisti obsazenost a jaké jsou v daném měsíci objednávky
    $xx["$y-$m"]= (object)array('from'=>$from,'until'=>$until,'obj'=>'');
    $cr= mysql_qry(" /*$y-$m*/
      SELECT state,TRIM(rooms1),IF(untilday<fromday,'?',ROUND((untilday-fromday)/86400)),uid
      FROM tx_gnalberice_order
      WHERE NOT deleted AND NOT hidden AND untilday>=$from AND $until>=fromday");
    while ( $cr && (list($state,$kolik,$dnu,$obj)= mysql_fetch_row($cr)) ) {
      if ( $state==1 ) {
        if ( !in_array($obj,$objednavky) )
          $objednavky[]= $obj;
      }
      elseif ( $dnu=='?' ) {
        // přidat hlášení chyby
      } 
      else {
        $kolik= ($kolik=='*' ? $pokoju_celkem : (substr_count($kolik,',')+1))*$dnu;
        $xx["$y-$m"]->obj[$state]+= $kolik;
      }
    }
    $prehled= '';
    if ($xx["$y-$m"]->obj) foreach ($xx["$y-$m"]->obj as $state=>$kolik) {
      $prehled.= " $kolik &times; ".pokoj_ikona_popis($state) . "<br>";
    }
    $co= count($objednavky);
    $xx["$y-$m"]->obj= $prehled
      . ($co ? " <b>&nbsp; $co &times; ".pokoj_ikona_popis(-2)."</b><br>" : '');
    $d+= date("t",$from)*86400;
  }
//                                         debug($xx);
  // zobrazení měsíců
  $h= "<div id='objednavky' class='x content'><h1>Objednávky Domu setkání</h1>";
  $year = '';
  foreach($xx as $ym=>$x) {
    list($y,$m)= explode('-',$ym);
    if ($y != $year) {
      $h .= "<h2>Rok $y</h2>";
      $year = $y;
    }
    if (!$xx["$y-$m"]->obj) {
      $xx["$y-$m"]->obj = 'Zatím nejsou k dispozici žádné údaje.';
    }

    $mesic= $month[$m];
    $sum= $x->obj;
    $jmp= $CMS ? "onclick=\"go(arguments[0],'$href0!$ym#anchor$ym','');\""
               : "href='$href0!$ym#anchor$ym'";
    $h.= $ym==$ym0
      ? mesic($ym,$x->from,$x->until,$mesic,$path)
      : "<div class='abstr x'>
           <a class='abstrakt x' style='height: 10em' $jmp>
             <h4>$mesic</h4> $sum
         </a></div>"
      ;
  }
  return $h."</div><br><br><br>";
}
# ---------------------------------------------------------------------------------- pokoj_ikona
# vrátí ikonu symbolizující
# 4>=$state>=0  obsazenost pokoje
# $state=-1     vyvolání žádosti
# $state=-2     poslanou poptávku
function pokoj_ikona($state) {
  switch ($state) {
//   case '-2': $i= "<img border=0 src='fileadmin/icons/mailicon.gif'>"; break;
  case '-2': $i= "<i class='fa fa-envelope-o'></i>"; break;
//   case '-1': $i= "<img border=0 src='fileadmin/icons/newmail.gif'>"; break;
  case '-1': $i= "<i class='fa fa-pencil-square-o'></i>"; break;
  case  '1': $i= "<i class='fa fa-$ico'></i>"; break;
  case  '2': $i= "<i class='fa fa-user'></i>"; break;
  case  '3': $i= "<i class='fa fa-futbol-o'></i>"; break;
  case  '4': $i= "<i class='far fa-times-circle'></i>"; break;
  }
  return $i;
}
function pokoj_ikona_popis($state) {
  switch ($state) {
//   case '-2': $i= "<img border=0 src='fileadmin/icons/mailicon.gif'>"; break;
    case '-2': $i= "<i class='fa fa-envelope-o'></i>&nbsp;žádost o pobyt"; break;
//   case '-1': $i= "<img border=0 src='fileadmin/icons/newmail.gif'>"; break;
    case '-1': $i= "<i class='fa fa-pencil-square-o'></i>&nbsp;volných míst"; break;
    case  '1': $i= "<i class='fa fa-$ico'></i>&nbsp;zájmů o pobyt"; break;
    case  '2': $i= "<i class='fa fa-user'></i>&nbsp;závazných objednávek"; break;
    case  '3': $i= "<i class='fa fa-futbol-o'></i>&nbsp;pro akce YMCA"; break;
    case  '4': $i= "<i class='far fa-times-circle'></i>&nbsp;nelze pronajmout"; break;
  }
  return $i;
}
function ikona_legenda() {
  $h = "<div class='legend float-right' style='width: fit-content; padding: 13px'>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(-2) . "&nbsp;Žádost o pobyt podána</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(-1) . "&nbsp;Volné místo</div>";
  //$h .= "<div class='icons_legend'>" . pokoj_ikona(1) . "&nbsp;Zájem o pobyt</div>";  //todo invalid icon, equals to "zájem"
  $h .= "<div class='icons_legend'>" . pokoj_ikona(2) . "&nbsp;Závazná objednávka</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(3) . "&nbsp;Probíhá akce YMCA</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(4) . "&nbsp;Nelze pronajmout</div>";
  return $h . "</div>";
}
function barvy_legenda() {
  $h = "<div class='legend float-right' style='width: fit-content; padding: 13px'>";
  $h .= "<div class='barva_legend' style='background: #c5dcf8'>víkend</div>";
  $h .= "<div class='barva_legend' style='background: #a9d49d'>dům je volný</div>";
  $h .= "<div class='barva_legend' style='background: #f5e933'>část pokojů volná</div>";
  $h .= "<div class='barva_legend' style='background: #da9088'>pokoje jsou plné</div>";
  return $h . "</div>";
}
# ---------------------------------------------------------------------------------- gn_makeDaysList
function gn_makeDaysList($pid,$pid_goal,$ym,$od,$do) { trace();
  global $CMS, $href0;
  //  Zobrazení tabulky obsazenosti
  $content= ikona_legenda() . barvy_legenda();
  # ukazani obsazenosti v obdobi $od $do
  # projiti pokoju - zobrazeni hlavicky
  $version= substr($ym,0,4)==2014 ? '' : 1; //todo delete? already past
  $version0= $version ?: 0;
  $res= mysql_qry("SELECT * FROM tx_gnalberice_room
                   WHERE NOT deleted AND NOT hidden AND version=$version0 ORDER BY number");
  $pokoje=array();
  $rooms_all= array();
  while($row = mysql_fetch_assoc($res))   {
    $pokoje[]= $row;
    $rooms_all[]= $row['number'];
  }
//                                                         debug($pokoje);
  $c_obsazenych= $a_obsazenych= $p_obsazenych= 0;
  // pole pro informace z nabídek
  $wnames= array ();
  $wuids= array ();     // obsazenost
  $wuidss= array ();   // nevyřízené objednávky
  $wstates= array ();
  $wnotes= array ();   // poznámky
  $worders= array ();
  $xx= array();
  $qry= "SELECT uid,state,rooms$version AS rooms,fromday,untilday,name,note FROM tx_gnalberice_order
    WHERE NOT deleted AND NOT hidden AND fromday<=$do AND untilday>=$od ORDER BY fromday";
  $res= mysql_qry($qry);
  $rows= mysql_num_rows($res);
  while($row = mysql_fetch_assoc($res)) {
    #$gn->gn_debug($row);
    if ( $row['state'] > 1 ) {
      // projdeme závazné objednávky
      for ( $d= $row['fromday']; $d<=$row['untilday']; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
        $dmy= date('d.m.y',$d);
        $wnotes[$dmy].= $row['note'] ? (($wnotes[$dmy] ? " + " : "") . $row['note'] ) : "";
        $rooms= $row["rooms"];
        $rooms= $rooms=='*' ? $rooms_all : explode(',',$rooms);
        foreach ($rooms as $n) {
          $dmyn= "$dmy/$n";
          $wnames[$dmyn]= ($wnames[$dmy] ? " + " : "") . ($row['name'] ? $row['name'] : $row['note']);
          $wuids[$dmyn]= $row['uid'];
          $wstates[$dmyn]= $row['state'];
        }
      }
    }
    else {
      // projdeme nezávazné objednávky
      for ( $d= $row['fromday']; $d<=$row['untilday']; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
        $dmy= date('d.m.y',$d);
        $worders[$dmy].= ($worders[$dmy] ? " + " : "") . $row['name'];
        $wuidss[$dmy].= ($wuidss[$dmy]?',':'') . $row['uid'];
      }
    }
  }
  // zobrazeni obsazenosti
  $pokoju= count($pokoje);
  // projdi zvolený časový interval
  $odd_counter = 0;
  for ( $d= $od; $d<=$do; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
    $dnu++;
    $radek= '';
    $obsazenych= 0;
    $odd = odd($odd_counter) ? " odd" : "";
    foreach ($pokoje as $pokoj) {
      $n= $pokoj["number"];
      $dmy= date('d.m.y',$d);
      $dmyn= "$dmy/$n";
      $p= $wnames[$dmyn];
      if ( $p ) {
        $u= $wuids[$dmyn];
        $s= $wstates[$dmyn];
        if ( $s != 4 ) {
          $obsazenych++;
          $c_obsazenych++;
          $a_obsazenych+= $s==3 ? 1 : 0;
          $p_obsazenych+= $s==2 ? 1 : 0;
        }
        if ( $s > 1 ) {
          $goal= pokoj_ikona($s);
          $form= "onclick=\"objednavka(arguments[0],'form',{order:'$u'});return false;\" ";
          $radek.= "<td class='obsazen$odd' style='border-right: 1px solid' title='$p' $form>$goal</td>";
//           $goal= "<img border=0 src='fileadmin/icons/".($s==2?"smile":($s==3?"sun_smile":"stop")).".gif' title='$p'>";
//           $radek.= "<td class='nic $odd'><a href={$gn->index}?id=$pid_goal&from=$od&until=$do&show=$u>$goal</a></td>";
        }
        else {
          $radek.= "<td class='nic $odd' style='border-right: 1px solid'></td>";
        }
      }
      else {
        $radek.= "<td class='nic $odd' style='border-right: 1px solid'>&nbsp;</td>";
      }
    }
    // styly zobrazení
    $styl= $obsazenych ? ($obsazenych==$pokoju ? "datum_plno" : "datum_poloplno") : "datum_prazdno";
    $datum= date("j. n.",$d);
    $weekend= date("w",$d);
    $weekend= ($weekend==0 || $weekend==6);
    $free= $weekend ? "_weekend" : "";
    $content.= "<tr>";
    // sloupec pro datum
    $content.= "<td class='datum$free$odd'>$datum</td>";
    // sloupec pro novou objednavku
    if ( $obsazenych==$pokoju  || $d<time() )
      $content.= "<td class='$styl$odd'></td>";
    else {
      $den= date("Y-m-d",$d);
      $form= "onclick=\"objednavka(arguments[0],'form',{den:'$den'});return false;\" ";
      $goal= pokoj_ikona(-1);
      $content.= "<td class='sent $styl$odd' title='chci objednat pobyt' $form>$goal</td>";
    }
//     $goal= "<img border=0 src='fileadmin/icons/newmail.gif' title='chci objednat pobyt'>";
//     $novy= "<a href={$gn->index}?id=$pid_goal&from=$od&until=$do&anew=1&date=$d>$goal</a>";
//     $content.= "<td class=$styl>" . ($obsazenych==$pokoju || $d<time() ? "" : $novy) . "</td>";
    // sloupec pro čekající objednávky
    $show= "";
    if ( $wuidss[$dmy] ) {
      # zobrazeni objednavek
      $tit= "objednávka pobytu pro {$worders[$dmy]}";
      $goal= pokoj_ikona(-2);
      $form= "onclick=\"objednavka(arguments[0],'wanted',{orders:'{$wuidss[$dmy]}'});\" ";
      $content.= "<td  title='$tit' class='sent $styl$odd' $form>$goal</td>";
//       $goal= "<img border=0 src='fileadmin/icons/mailicon.gif' title='{$worders[$dmy]}'>";
//       $show= "<a href={$gn->index}?id=$pid_goal&from=$od&until=$do&show={$wuidss[$dmy]}>$goal</a>";
    }
    else {
      $content.= "<td class='$styl$odd'></td>";
    }
    $content.= "$radek";
    // sloupec poznámek
    $note= $wnotes[$dmy];
    if ( mb_strlen($note)>50 ) {
      $note= x_shorting($note,50) . ' ...';
    }
    $content.= "<td class='pozn$free$odd' title='$wnotes[$dmy]'>$note</td>";
    $content.= "</tr>";
    $odd_counter++;
  }
//                                                 display("<table>$content</table>");

//   $c_obsazenost= $gn->userlevel>=SUPER ? ("obsazenost = ".number_format(100 * $c_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost
//   $a_obsazenost= $gn->userlevel>=SUPER ? ("akce = ".number_format(100 * $a_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost akcemi
//   $p_obsazenost= $gn->userlevel>=SUPER ? ("pobyty = ".number_format(100 * $p_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost pobytem
  $content.= "\n</tr>";
  // hlavička tabulky
  $h_patra= $version0==0 ? "
    <tr class='header1' ><td class='bold' colspan=3>&nbsp;</td>
    <td class='bold' style='border-right: 1px solid' colspan=7>2.patro</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>1+</td>
    <td class='bold' style='border-right: 1px solid' colspan=3>1.patro</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>P+</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>příz.</td>" : "
    <tr class='header1'><td class='bold' colspan=3>&nbsp;</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>příz.</td>
    <td class='bold' style='border-right: 1px solid' colspan=3>1.patro</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>1-</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>1+</td>
    <td class='bold' style='border-right: 1px solid' colspan=9>2.patro</td>";
  $h_patra_backwards = $h_patra . "</tr>";
  $h_patra .= "<td class='bold' rowspan='4' style='border-left:2px solid'>poznámky</td></tr>";
  $h_pokoje= "<tr><td class='bold' colspan=3>pokoj:</td>";
  // pokoje
  foreach ($pokoje as $pokoj) {
    $etage= $pokoj['etage']+254;     // viz popisy poschodí
    $pokoj_str= $pokoj['number'];
    $pokoj_str= $pokoj_str<10 ? "&nbsp;$pokoj_str&nbsp;" : $pokoj_str;
    $au= "go(arguments[0],'$href0!$ym!$etage#anchor$ym','');"; //todo does not work, the "show room popup" will send user to the main page :(

    $h_pokoje.= "<td class='room' onclick=\"$au\" title='{$pokoj['note']}'>$pokoj_str</td>";
//     $h_pokoje.= "<td class=room><a href={$gn->index}?id=pokoje&case=$etage title='{$pokoj['note']}'>$pokoj_str</a></td>";
  }
  $h_pokoje.= "</tr>";
  // počty postelí
  $h_postele= '<tr><td class="bold" colspan=3>postelí:</td>';
  foreach ($pokoje as $pokoj) {
    $h_postele.= "<td class='bold' style='border-right: 1px solid'>{$pokoj['beds']}</td>";
  }
  $h_postele.= "</tr>";
  // počty přistýlek
  $h_pristylky= '<tr class="header2"><td class="bold" colspan=3>přistýlek:</td>';
  foreach ($pokoje as $pokoj) {
    $addbeds= $pokoj['addbeds'] ? $pokoj['addbeds'] : '-';
    $h_pristylky.= "<td class='bold' style='border-right: 1px solid'>$addbeds</td>";
  }
  $h_pristylky_backwards = $h_pristylky . "<td rowspan='4' class=\"bold\" style='border-left:2px solid; 
           border-top: 3px solid; border-bottom: 3px solid'>poznámky</td></tr>";
  $h_pristylky.= '</tr>';
  // připojení hlavičky
  $html= "<div style='overflow-x: scroll;
    width: 100%;'><table id='dum' class=dum>";
  $html.= "$h_patra$h_pokoje$h_postele$h_pristylky";
  $html.= $content;
  $html.= "$h_pristylky_backwards$h_postele$h_pokoje$h_patra_backwards";
  $html.= "</table></div>\n";
end:
  return $html;
}

function odd($number) {
  return $number % 2 == 1;
}
?>
