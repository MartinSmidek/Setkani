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
  $pokoje= select1("GROUP_CONCAT(number ORDER BY number SEPARATOR ',')",'tx_gnalberice_room',
                   "NOT deleted AND NOT hidden AND version=1");
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
      if ($fld === 'uid') continue;
      $val= mysql_real_escape_string($val);
      if (trim($val)==='') {
        continue;
      }
//      if ( in_array($fld,array('fromday','untilday')) ) {
//        list($d,$m,$r)= explode('-',$val);
//        $val= mktime(0, 0, 0, $m, $d, $r);
//      }
      if ( $fld=='rooms1' && $val=='*' ) {
        $val= $pokoje;
      }
      $flds.= ",$fld";
      $vals.= ",'$val'";
    }
    $flds.= ",rooms";
    $vals.= ",''";
    $flds.= ",prog_cely";
    $vals.= ",'0'";
    $flds.= ",prog_polo";
    $vals.= ",'0'";

    $flds.= ",firstname";
    $vals.= ",''";
    $flds.= ",org";
    $vals.= ",''";
    $flds.= ",skoleni";
    $vals.= ",'0'";

    $y->ok= query("INSERT INTO tx_gnalberice_order ($flds) VALUES ($vals)");
//                                                         display("insert=$y->ok");
    $y->order= mysql_insert_id();
    if (!$y->ok) {
      $y->msg = "Objednávku se nepodařilo dokončit.";
      break;
    }
    $email = $x->form->email;
    $forward_to = "dum@setkani.org";
    if (!$email || $email === '' || !strpos($email, "@")) {
      //todo uncomment once ready
      //mail_send($forward_to, $forward_to, "Objednávka pobytu v Domě Setkání", new_order_mail_from_form($x->form));
      $y->completion = "Objednávka neobsahuje emailovou adresu. Objednávka <b>byla úspěšně podána</b>, ale nedojde vám potvrzovací email. Údaje se objeví v kalendáři po aktualizaci této stránky.";
    } else {
      //todo uncomment once ready
        //mail_send_cc($forward_to, $email, "Objednávka pobytu v Domě Setkání", new_order_mail_from_form($x->form), $forward_to, "Dům Setkání");
        //$y->completion = "Objednávka byla úspěšně zaslána. Na email vám brzy přijde její shrnutí. Údaje se objeví v kalendáři po aktualizaci této stránky.";
        $y->completion = "Objednávka byla úspěšně zaslána. Údaje se objeví v kalendáři po aktualizaci této stránky.";
    }
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
//        if ( in_array($fld,array('fromday','untilday')) ) {
//          list($d,$m,$r)= explode('-',$val);
//          $val= mktime(0, 0, 0, $m, $d, $r);
//        }
        if ( $fld=='rooms1' && $val=='*' ) {
          $val= $pokoje;
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

  case 'get_days':
    $y->days_data = get_days_data($x->fromday, $x->untilday);
    break;

  case 'get_room':
    $y->room_data = get_room($x->room_number);
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
}
function dum_form($x) {
  global $y,$dum_data,$dum_data_open;
//  global $trace, $totrace;
//                                                         debug($x,"dum_form");
  $ord= $x->order;
  $user= $_SESSION['web']['fe_user'];
  $spravce= $user ? access_get(1) : 0;
  $pokoje= select1("GROUP_CONCAT(number ORDER BY number SEPARATOR '|')",'tx_gnalberice_room',
                   "NOT deleted AND NOT hidden AND version=1");
  $dum_data_open= 0;
  $show_first_two = true;
  if ( $ord ) {                                                             // !!! pak jen správce
    $dum_data_open= 0; //$spravce;
    $dum_data= select("*","setkani4.tx_gnalberice_order","uid=$ord");
    $dum_data->fromday= date("Y-m-d",$dum_data->fromday);
    $dum_data->untilday= date("Y-m-d",$dum_data->untilday);
  }
  elseif ( $ord==0 ) {
    $dum_data_open= 1;
    $dum_data= (object)array();
    $dum_data->fromday= $x->den;
    // upravíme default: odjezd=příjezd+2, adults=2
    list($r,$m,$d)= explode('-',$x->den);
    $dum_data->untilday= date("Y-m-d",mktime(0, 0, 0, $m, $d+2, $r));
    $dum_data->adults= 2;
    if ( $user ) {
      list($dum_data->name,$dum_data->telephone,$dum_data->email,
           $dum_data->address,$dum_data->zip,$dum_data->city)
        = select("CONCAT(firstname,' ',name),telephone,email,address,zip,city","fe_users","uid=$user");
    } else {
      $show_first_two = !$ord;
    }
  }
  $y->html= "<table><tr><td style='padding-right: 12px; max-width: 775px;'>";
  if ($show_first_two) {
    $y->html .= f_input("objednávka","uid",3,0)."&nbsp;&nbsp;&nbsp;"
        . f_select("stav objednávky","state", "1:zájem o pobyt,2:závazná objednávka,3:akce YMCA,4:nelze pronajmout",$ord, 'max-width: 200px')."<br>";
  }
  $y->html .= f_input("poznámka k objednávce","note",35)."<br>"
  . f_date("příjezd",            "fromday",       8, $ord, 'getRoomsForTimespan(true, this);', "fromday_input")
  . f_date("odjezd",             "untilday",      8, 1, 'getRoomsForTimespan(false, this);', "untilday_input")
  . f_input(get_free_rooms(strtotime($dum_data->fromday), strtotime($dum_data->untilday)),"rooms1",40,1, 'text', '', "objednejte pokoje jejich čísly oddělenými čárkou", "rooms_label")."<br>"
  . f_input("dospělých",          "adults",        3, 1, 'number', 'max-width: 80px')
  . f_input("děti 10-15",         "kids_10_15",    3, 1, 'number', 'max-width: 80px')
  . f_input("děti 3-9",           "kids_3_9",      3, 1, 'number', 'max-width: 80px')
  . f_input("děti do 3 let",      "kids_3",        3, 1, 'number', 'max-width: 80px')."<br>"
  . f_select("typ stravy","board","1:penze,2:polopenze,3:bez stravy", 1, 'max-width: 200px')
  . "<!-- TODO uncomment once implemented <h4>Cena dle ceníku: <span id='order_final'>zatím nefunkční.</span></h4>--></td><td>"
  . f_input("jméno a příjmení",   "name",         22)
  . ( $dum_data_open || $spravce ? (
      f_input("telefon",          "telephone",    14,1)."<br>"
    . f_input("email",            "email",        30,1)."<br>"
    . f_input("ulice",            "address",      30,1)."<br>"
    . f_input("psč",              "zip",          10,1)
    . f_input("obec",             "city",         16,1)."<br><br>"
  ) : '<br><br>(Osobní údaje jsou přístupné pouze pro správce Domu setkání)')
  . ( $ord && $spravce ? (
        f_button("Opravit","block_enable('order',1,'uid')") . f_button_sep()
      . f_button("Uložit opravu","objednavka(0,'update',{order:'$ord',rooms:'$pokoje'});",0,'order_save') . f_button_sep()
      . f_button("Smazat","objednavka(0,'delete',{order:'$ord'});") . f_button_sep()
      . f_button("Zpět","block_display('order',0);", 0, '')
      ) : (
      $ord ? (
        "<br>".f_button("Zavřít","block_display('order',0);",1)
      ) : (
          f_button("Přidat objednávku","objednavka(0,'create',{rooms:'$pokoje'},this);") . f_button_sep()
      . f_button("Zrušit","block_display('order',0,'uid');")
      )
    ))
  . "</td></tr></table>";
}
function f_input($label,$name,$size,$enabled=1,$type='text',$css='',$hint='', $labelid='') { //trace();
  global $dum_data, $dum_data_open, $kernel;
  $disabled= $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : ' ');
  $onchange=  $kernel=='ezer3.1' 
      ? "onchange='jQuery(this).addClass(\"changed\");'"
      : "onchange='this.addClass(\"changed\");'";
  $labelid = $labelid ? "id='$labelid'" : "";
  $html= " <label style='$css'><span $labelid style='font-size: 10pt'>$label</span><input name='$name' type='$type' placeholder='$hint' size='$size' 
    $disabled $onchange value='{$dum_data->$name}'></label>";
  return $html;
}
function f_date($label,$name,$size,$enabled,$js,$id) {
  global $dum_data, $dum_data_open, $kernel;
  $disabled= $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : ' ');
  $onchange=  $kernel=='ezer3.1'
      ? "onchange='jQuery(this).addClass(\"changed\");$js'"
      : "onchange='this.addClass(\"changed\");$js'";
  $html= " <label>$label<input id='$id' name='$name' type='date' size='$size' $disabled $onchange 
    value='{$dum_data->$name}'></label>";
  return $html;
}
function f_select($label,$name,$options,$enabled=1,$css='') { //trace();
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
  return " <label style='$css'>$label<select name='$name' type='input' $disabled $onchange>$opt</select></label>";
}
function f_button($label,$js,$even=0,$id_and_hide='') {
  $id= $id_and_hide ? " id='$id_and_hide' name='$id_and_hide' hidden" : '';
  $html= " <label class='form_button'><input $id class='form_button' type='submit' value='$label' onclick=\"$js\"></label>";
  return $html;
}
function f_button_sep() {
  return "<label class='form_button'>&nbsp;</label>";
}
function new_order_mail_from_form($form) {
  return "<p>Dobrý den $form->name, <br>
            děkujeme za objednávku pobytu v Domě Setkání. Zasíláme shrnutí Vaší objednávky, potvrzovací email a zálohovou fakturu obdržíte během týdne.</p>" .
      form_to_mail_string($form) .
          "<br><p>Těšíme se na Vás!</p>Dům Setkání YMCA";
}
function form_to_mail_string($form) {
  $from = date("j. m. Y", $form->fromday);
  $until = date("j. m. Y", $form->untilday);
  $adults = escape_nostring($form->adults);
  $kids12 = escape_nostring($form->kids_10_15);
  $kids9 = escape_nostring($form->kids_3_9);
  $kids3 = escape_nostring($form->kids_3);
  $strava = strava($form->board);
  return "
            <h3>Souhrn:</h3>
            <table style='padding: 8px;' cellpadding='0' cellspacing='0'>
             <tr>
                <td style='border-bottom: 2px solid; padding: 2px 9px;font-weight: bold;border-right: 1px solid;'>doba pobytu</td>
                <td style='border-bottom: 2px solid; padding: 2px 9px;' colspan='2'>$from až $until</td>
             </tr>
             <tr>
                <td style='border-bottom: 2px solid; padding: 2px 9px;font-weight: bold;border-right: 1px solid;'>objednané pokoje</td>
                <td style='border-bottom: 2px solid; padding: 2px 9px;text-align:right;' colspan='2'>$form->rooms1</td>
             </tr>
             <tr>
                <td style='border-bottom: 2px solid; padding: 2px 9px;font-weight: bold;border-right: 1px solid;' rowspan='4'>účastníci</td>
                <td style='border-bottom: 1px solid; padding: 2px 9px;'>dospělí</td>
                <td style='text-align:right; border-bottom: 1px solid; padding: 2px 9px;'>$adults</td>
             </tr>
             <tr>
                <td style='border-bottom: 1px solid; padding: 2px 9px;'>děti 10-15 let</td>
                <td style='text-align:right; border-bottom: 1px solid; padding: 2px 9px;'>$kids12</td>
             </tr>
             <tr>
                <td style='border-bottom: 1px solid; padding: 2px 9px;'>děti 3-9 let</td>
                <td style='border-bottom: 1px solid; padding: 2px 9px;text-align:right;'>$kids9</td>
             </tr>
             <tr>
                <td style='border-bottom: 2px solid; padding: 2px 9px;'>děti do 3 let</td>
                <td style='text-align:right;border-bottom: 2px solid; padding: 2px 9px;'>$kids3</td>
             </tr>
             <tr>
                <td style='padding: 2px 9px;font-weight: bold;border-right: 1px solid;'>strava</td>
                <td style='padding: 2px 9px;' colspan='2'>$strava</td>
             </tr>
            </table>";
}
function strava($num) {
  switch ($num) {
    case 1: return "penze";
    case 2: return "polopenze";
    case 3: return "žádná";
  }
  return '';
}
function get_free_rooms($from, $until) { //also implemented in javascript in custom.js (todo maybe call as ajax directly this function and remove the JS version)
  $data = get_days_data($from, $until);
  $rooms = array();
  foreach ($data as $tstamp => $days_data) {
    if ($tstamp < 100) continue;
    foreach ($days_data->pokoje as $room => $room_data) $rooms[$room] = 0;
    break;
  }

  $days = 0;
  foreach ($data as $tstamp => $days_data) {
    if ($tstamp < 100) continue;
    foreach ($days_data->pokoje as $room => $room_data) {
      if (!$room_data->pset) $rooms[$room]++;
    }
    $days++;
  }
  $content = '';
  foreach ($rooms as $room => $count) {
    if ($count == $days) $content .= "$room, ";
  }
  if ($content == '') $content = "žádné volné pokoje";
  else $content = "volné pokoje: $content";
  return $content;
}
function escape_nostring($value, $esc="-") {
  if (!$value) return $esc;
  return $value;
}
# ----------------------------------------------------------------------------------------==> mesice
# 1=zájem o pobyt, 2=závazná objednávka, 3=akce YMCA, 4=nelze pronajmout );
function mesice($path) {  trace();
  popup("Objednávka","","",'order');

  $user= $_SESSION['web']['fe_user'];
  $spravce= $user ? access_get(1) : 0;
  $month= array( 1=> "leden", "únor", "březen", "duben", "květen", "červen",
    "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

  // suma měsíců
  $mesicu= 17;
  if ($spravce) $mesicu += 12;

  $xx= array();
  $d= mktime(0, 0, 0, date("n")-3, 2, date("Y"));

  // pole pro informace z nabídek
  $wnames= array ();
  $wstates= array ();

  //pokoje
  $res= mysql_qry("SELECT * FROM tx_gnalberice_room WHERE NOT deleted AND NOT hidden AND version=1 ORDER BY number");
  $pokoje=array();
  $rooms_all= array();
  while($row = mysql_fetch_assoc($res))   {
    $pokoje[]= $row;
    $rooms_all[]= $row['number'];
  }
  $pokoju_celkem = count($pokoje);

  for ($i= 0; $i <= $mesicu; $i++ ) {
    $m= date("n",$d);
    $y= date("Y",$d);
    $from= mktime(0, 0, 0, $m, 1, $y);
    $until= mktime(0, 0, 0, $m, date("t",$from), $y);
    //$objednavky= array();
    // zjisti obsazenost a jaké jsou v daném měsíci objednávky
    $xx["$y-$m"]= (object)array('from'=>$from,'until'=>$until,'obj'=>'');
    $cr= mysql_qry("SELECT state,rooms1,fromday,untilday,name
      FROM tx_gnalberice_order
      WHERE NOT deleted AND NOT hidden AND fromday<=$until AND untilday>=$from ORDER BY fromday");
    while ( $cr && (list($state,$kolik,$fromday,$untilday,$name)= mysql_fetch_row($cr)) ) {
      if ( $state > 1 ) { //jen závazné objednávky
        for ( $dd= $fromday; $dd<=$untilday; $dd= mktime(0,0,0,date("m",$dd),date("d",$dd)+1,date("Y",$dd)) ) {
          $dmy= date('d.m.y',$dd);
          $rooms= $kolik;
          $rooms= $rooms=='*' ? $rooms_all : explode(',',$rooms);
          foreach ($rooms as $n) {
            $dmyn= "$dmy/$n";
            $wnames[$dmyn]= ($wnames[$dmy] ? " + " : "") . ($name ? $name : 'note');
            $wstates[$dmyn]= $state;
          }
        }
      }
    }

    $calendar= '<table class="orders-month"><tr><th>Po</th><th>Út</th><th>St</th><th>Čt</th><th>Pá</th><th>So</th><th>Ne</th></tr><tr>';
    $currentDay = 1;
    for ( $dd= $from; $dd<=$until; $dd= mktime(0,0,0,date("m",$dd),date("d",$dd)+1,date("Y",$dd))) {
      $dayOfWeek = date('N', $dd);
      while($currentDay < $dayOfWeek) {
        $calendar .= "<td></td>";
        $currentDay++;
      }
      $obsazenych= 0;
      $dmy= date('d.m.y',$dd);
      foreach ($pokoje as $pokoj) {
        $n= $pokoj["number"];
        $dmyn= "$dmy/$n";
        $p= $wnames[$dmyn];
        if ( $p ) {
          $s= $wstates[$dmyn];
          //if ( $s != 4 ) we want to include blocked rooms
          $obsazenych++;
        }
      }
      // kalendář na úvodku
      $day = date('j',$dd);
      $styl= $obsazenych ? ($obsazenych==$pokoju_celkem ? "obsazeno" : "konflikt") : "volno";
      $calendar .= "<td class='odd clickable $styl' content='$dd' onclick=\"getDaysData(this, '$y-$m', '$y')\">$day</td>";
      $currentDay++;
      if ($currentDay > 7) {
        $calendar .= "</tr><tr>";
        $currentDay = 1;
      }
    }
    $calendar .= "</table>";
    $xx["$y-$m"]->obj= $calendar;
    $d+= date("t",$from)*86400;
  }

  $h = "<div id='objednavky' class='x content'><h1>Objednávky Domu setkání</h1>" . order_message() . order_tutorial($pokoje) . "<div>";
  $year = '';
  foreach($xx as $ym=>$x) {
    list($y,$m)= explode('-',$ym);
    if ($y != $year) {
      $h .= "</div><br><br><h2 id='rok-$y' style='float: left; margin-top: 5px;'>Rok $y</h2>
        <div class='float-right legend'><span class='timeline_legend' style='background: #a9d49d; margin-right: 48px'>volný&nbsp;dům</span>&emsp;
        <span class='timeline_legend' style='background: #f5e933;'>poloplno&emsp;</span>&emsp;
        <span class='timeline_legend' style='background: #da9088;'>obsazeno&emsp;</span>&emsp;</div>";
      $year = $y;
      $h .= "<span class='float-right' style='cursor:pointer; line-height: 46px; margin-right: 7px;' onclick=\"
            jQuery('#objednavky-content-$y').addClass('nodisplay');
            jQuery('.ordersSelectedDay').each(function(i, obj) {
              jQuery(obj).removeClass('ordersSelectedDay');
              jQuery(obj).removeClass('ordersSelectedDay$y');
           });
            \"><i class='fa fa-eraser'></i>smazat výběr</span>
        <div id='pokoje' class='x'><span class='anchor' id='anchor$ym'></span>
         <div class='x'><div id='mesic$ym' class='pokoje x'>
           <div class='nodisplay' id='objednavky-content-$y'></div>
         </div></div>
       </div><div class='months-row'>";
    }
    if (!$xx["$y-$m"]->obj) {
      $xx["$y-$m"]->obj = 'Zatím nejsou k dispozici žádné údaje.';
    }
    $mesic= $month[$m];
    $sum= $x->obj;
    $h.= "<div style='margin: 0 10px;'><h4>$mesic</h4> $sum</a></div>";
  }
  return "$h</div><br><br></div>";
}

# -----------------------------------------------------------------------------------
# called by ajax to give a data for certain time span
function get_room($number) {
  if (!is_numeric($number)) return null;
  $res= mysql_qry("SELECT * FROM tx_gnalberice_room WHERE number=$number AND NOT deleted AND NOT hidden AND version=1 LIMIT 1");
  $pokoje=array();
  $rooms_all= array();
  while($row = mysql_fetch_assoc($res))   {
    $pokoje[]= $row;
    $rooms_all[]= $row['number'];
    $etage= $row['etage']+254;
    return select("text","setkani4.tx_gncase_part","cid=$etage");
  }
  return "Nepodařilo se najít pokoj  číslo $number.";
}
function get_days_data($from, $until) {
  $wnames= array ();
  $wuids= array ();     // obsazenost
  $wuidss= array ();   // nevyřízené objednávky
  $wstates= array ();
  $wnotes= array ();   // poznámky
  $worders= array ();

  //pokoje
  $res= mysql_qry("SELECT * FROM tx_gnalberice_room WHERE NOT deleted AND NOT hidden AND version=1 ORDER BY number");
  $pokoje=array();
  $rooms_all= array();
  while($row = mysql_fetch_assoc($res))   {
    $pokoje[]= $row;
    $rooms_all[]= $row['number'];
  }
  $pokoju_celkem = count($pokoje);

  $cr= mysql_qry("
      SELECT state,rooms1,uid,fromday,untilday,name,note
      FROM tx_gnalberice_order
      WHERE NOT deleted AND NOT hidden AND untilday>=$from AND $until>=fromday ORDER BY fromday");
  while ( $cr && (list($state,$kolik,$obj,$fromday,$untilday,$name,$note)= mysql_fetch_row($cr)) ) {
    if ( $state > 1 ) { //jen závazné objednávky
      for ( $dd= $fromday; $dd<=$untilday; $dd= mktime(0,0,0,date("m",$dd),date("d",$dd)+1,date("Y",$dd)) ) {
        $dmy= date('d.m.y',$dd);
        $wnotes[$dmy].= $note ? (($wnotes[$dmy] ? " + " : "") . $note ) : "";
        $rooms= $kolik;
        $rooms= $rooms=='*' ? $rooms_all : explode(',',$rooms);
        foreach ($rooms as $n) {
          $dmyn= "$dmy/$n";
          $wnames[$dmyn]= ($wnames[$dmy] ? " + " : "") . ($name ? $name : 'note');
          $wuids[$dmyn]= $obj;
          $wstates[$dmyn]= $state;
        }
      }
    } else {
      // projdeme nezávazné objednávky
      for ( $dd = $fromday; $dd<=$untilday; $dd= mktime(0,0,0,date("m",$dd),date("d",$dd)+1,date("Y",$dd)) ) {
        $dmy= date('d.m.y',$dd);
        $worders[$dmy].= ($worders[$dmy] ? " + " : "") . $name;
        $wuidss[$dmy].= ($wuidss[$dmy]?',':'') . $obj;
      }
    }
  }
  $ret = array();
  for ( $dd= $from; $dd<=$until; $dd= mktime(0,0,0,date("m",$dd),date("d",$dd)+1,date("Y",$dd))) {
    $dmy= date('d.m.y',$dd);
    $pokoje_day = array();
    $obsazenych = 0;
    foreach ($pokoje as $pokoj) {
      $n= $pokoj["number"];
      $dmyn= "$dmy/$n";
      $p= $wnames[$dmyn];
      if ( $p ) {
        $pset = true;
        $u = $wuids[$dmyn];
        $s = $wstates[$dmyn];
        //if ( $s != 4 )
        $obsazenych++;
      } else {
        $pset = false;
      }
      $pokoje_day[$n] = (object)array("pset"=>$pset, "u"=>$u, "s"=>$s, "p"=>$p);
    }

    if ( $obsazenych==$pokoju_celkem  || $dd<time() ) {
      $goal1 = -3;
    } else {
      $goal1= -1;
    }

    if ( $wuidss[$dmy] ) {
      $title = $worders[$dmy];
      $goal2= -2;
      $ord = $wuidss[$dmy];
    } else {
      $goal2 = 0;
      $title = '';
      $ord = '';
    }
    $note= $wnotes[$dmy];
    if ( mb_strlen($note)>50 ) {
      $note= x_shorting($note,50) . ' ...';
    }
    $ret["$dd"]= (object)array("pokoje"=>$pokoje_day, "obsazenych"=>$obsazenych, "who"=>$title, "note"=>$note,
        "ico1"=>$goal1, "ico2"=>$goal2, "order"=>$ord);
  }
  //key is timestamp - use low numbers as data
  $ret['0'] = bookingsTableHtml($pokoje);
  $ret['1'] = $pokoju_celkem;
  return $ret;
}

# ---------------------------------------------------------------------------------- pokoj_ikona
# vrátí ikonu symbolizující
# 4>=$state>=0  obsazenost pokoje
# $state=-1     vyvolání žádosti
# $state=-2     poslanou poptávku
# $state=-3     not in DB, used as "unable to order icon"
function pokoj_ikona($state) {
  switch ($state) {
    case  '-3': $i= "<i class='fa fa-times'></i>"; break;
    case '-2': $i= "<i class='fa fa-envelope-o'></i>"; break;
    case '-1': $i= "<i class='fa fa-pencil-square-o'></i>"; break;
    case  '1': $i= "<i class='fa fa-$ico'></i>"; break;
    case  '2': $i= "<i class='fa fa-user'></i>"; break;
    case  '3': $i= "<i class='fa fa-futbol-o'></i>"; break;
    case  '4': $i= "<i class='fa fa-times-circle'></i>"; break;
  }
  return $i;
}
function ikona_objednat_legenda($custom_class = '') {
  $h = "<div class='legend $custom_class' style='width: fit-content; padding: 13px; margin: 10px'>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(-2) . "&nbsp;Žádost o pobyt podána</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(-1) . "&nbsp;Požádat o pobyt</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(-3) . "&nbsp;Nelze objednat</div>";
  return $h . "</div>";
}
function ikona_objednano_legenda($custom_class = '') {
  $h = "<div class='legend $custom_class' style='width: fit-content; padding: 13px; margin: 10px'>";
  //$h .= "<div class='icons_legend'>" . pokoj_ikona(1) . "&nbsp;Zájem o pobyt</div>";  //todo invalid icon, equals to "zájem"
  $h .= "<div class='icons_legend'>" . pokoj_ikona(2) . "&nbsp;Závazná objednávka</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(3) . "&nbsp;Probíhá akce YMCA</div>";
  $h .= "<div class='icons_legend'>" . pokoj_ikona(4) . "&nbsp;Nelze pronajmout</div>";
  return $h . "</div>";
}
function barvy_legenda($custom_class = '') {
  $h = "<div class='legend $custom_class' style='width: fit-content; padding: 13px; margin: 10px'>";
  $h .= "<div class='barva_legend' style='background: #c5dcf8'>víkend</div>";
  $h .= "<div class='barva_legend' style='background: #a9d49d'>dům je volný</div>";
  $h .= "<div class='barva_legend' style='background: #f5e933'>část pokojů volná</div>";
  $h .= "<div class='barva_legend' style='background: #da9088'>dům je obsazený</div>";
  return $h . "</div>";
}
# ---------------------------------------------------------------------------------- hint - tutorial below title
function order_tutorial($pokoje) {
  $objednat_hint = ikona_objednat_legenda('float-left');
  $objednano_hint = ikona_objednano_legenda('float-right');
  $barvy_hint =  barvy_legenda("float-left");
  $result = "<p>Na akce YMCA se přihlašujete dle instrukcí v článku dané akce, neobjednáváte si pobyt v domě samostatně. </p>
    <p>Pro zobrazení detailů či podání objednávky <b>klikněte na dny v kalendáři</b>: zobrazí se vám údaje všech dnů mezi těmito dny.
    Detaily ukazují jaké pokoje jsou v jakém dni objednány, či umožňují vlastní objednání. Čísla pokojů jsou klikatelná a ukazují
    mapku a umístění pokoje.</p>
    <span style='width: 100%;text-align: center; display: block; font-weight: bold; cursor:pointer;' onclick=\"
        var content = jQuery('#objednavky_hint');
        if (content.hasClass('nodisplay')) jQuery(this).html('Zavřít nápovědu');  
        else jQuery(this).html('Ukázat nápovědu');  
        content.toggleClass('nodisplay');
    \">Ukázat nápovědu</span>
    <div id='objednavky_hint' class='nodisplay'>
    <h3>Ukázka:</h3>
    <div style='overflow-x: auto'> 
        <table id='dum' class='dum'><tbody>
            <tr class='header1'><td class='bold' colspan='3'>umístění</td>
                <td class='bold' style='border-right: 1px solid' colspan='2'>příz.</td>
                <td class='bold' style='border-right: 1px solid' colspan='3'>1.patro</td>
                <td class='bold' style='border-right: 1px solid' colspan='2'>1-</td>
                <td class='bold' style='border-right: 1px solid' colspan='2'>1+</td>
                <td class='bold' style='border-right: 1px solid' colspan='8'>2.patro</td>
                <td>popisuje, kde se pokoj nachází. +/- znamená mezipatro</td>
            </tr>
            <tr>
              <td class='bold' colspan='3'>pokoj číslo</td>";
  foreach ($pokoje as $pokoj) {
      $pokoj_num= $pokoj['number'];
      $pokoj_str= $pokoj_num<10 ? "&nbsp;$pokoj_num&nbsp;" : $pokoj_num;
      $result.= "<td class='room' onclick=\"popupRoomView('Pokoje', '$pokoj_num');\" title='{$pokoj['note']}'>$pokoj_str</td>";
  }
  $result .=  "<td>sděluje číslo pokoje, kliknutím zobrazí mapku</td>
            </tr>
            <tr>
              <td class='bold' colspan='3'>počet postelí</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>3</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>6</td><td class='bold' style='border-right: 1px solid'>4</td>
              <td class='bold' style='border-right: 1px solid'>2</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>2</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>3</td><td class='bold' style='border-right: 1px solid'>3</td>
              <td class='bold' style='border-right: 1px solid'>4</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>2</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>6</td><td class='bold' style='border-right: 1px solid'>5</td>
              <td>počet postelí v pokoji</td></tr>
            <tr class='header2'>
              <td class='bold' colspan='3'>přistýlek</td><td class='bold' style='border-right: 1px solid'>1</td>
              <td class='bold' style='border-right: 1px solid'>-</td><td class='bold' style='border-right: 1px solid'>1</td>
              <td class='bold' style='border-right: 1px solid'>-</td><td class='bold' style='border-right: 1px solid'>2</td>
              <td class='bold' style='border-right: 1px solid'>1</td><td class='bold' style='border-right: 1px solid'>1</td>
              <td class='bold' style='border-right: 1px solid'>1</td><td class='bold' style='border-right: 1px solid'>-</td>
              <td class='bold' style='border-right: 1px solid'>1</td><td class='bold' style='border-right: 1px solid'>-</td>
              <td class='bold' style='border-right: 1px solid'>-</td><td class='bold' style='border-right: 1px solid'>-</td>
              <td class='bold' style='border-right: 1px solid'>1</td><td class='bold' style='border-right: 1px solid'>-</td>
              <td class='bold' style='border-right: 1px solid'>2</td><td class='bold' style='border-right: 1px solid'>1</td>
              <td>kolik lze zařídit přistýlek</td></tr>
            <tr><td class='datum'>12.12.</td><td class='datum_poloplno odd'><i class='fa fa-pencil-square-o'></i></td><td class='datum_poloplno odd'><i class='fa fa-envelope-o'></i></td><td class='nic' style='border-right: 1px solid'><i class='fa fa-user'></i></td><td class='nic' style='border-right: 1px solid'><i class='fa fa-user'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-user'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-user'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-times-circle'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-times-circle'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-times-circle'></i></td><td class='nic ' style='border-right: 1px solid'><i class='fa fa-times-circle'></i></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td class='nic ' style='border-right: 1px solid'></td><td>klikatelné odkazy, viz popis níže</td>
            </tr>
        </tbody></table>
    </div>
    <p></p>
    <p>
    $objednat_hint
    Vedle data dne se zobrazují ikony <b>Žádost o pobyt podána</b>, pokud
    existuje nezávazná žádost na pobyt toho dne, <b>Požádat o pobyt</b> pokud lze na daný den pobyt objednat, a nebo <b>Nelze objednat</b>. Například, v ukázce zjevně někdo podal objednávku na den 12.12., ale stále lze podávat další.
     $objednano_hint
    <br>Každý pokoj má svoji ikonu podle stavu objednávky. Pokud pokoj není závazně objednán, je pole prázdné. Na ikony lze kliknout a zobrazit tak informace k objednávce na daný pokoj. Například, v ukázce jsou pokoje 1, 2, 11, 12 zarezervovány, nelze objednat pokoje 13-16, ale 17+ ano.
    <br>$barvy_hint
    Jednotlivé dny se barevně liší - víkendy jsou vyznačeny modře a stav dne vyjádřen stylem semaforu - zelená: úplně volný, červená: úplně obsazený. V ukázce je dům částečně obsazen.</p>
    <p>Objednávací formulář obsahuje kalkulačku ceny <b>která ovšem nemusí být konečná.</b> Záleží pak na konkrétní domluvě se správcem (například když část zákazníků přijede později).
    Nezávazná žádost o objednávku musí být schválena správcem - pak se objednávka stává závaznou a dané pokoje jsou obsazeny ikonou, která po kliknutí zobrazí informace k objednávce.</p>
    </div>
    <hr class='dark'>";
  return $result;
}
function order_message() {
  return "<div id='order_completion' class='nodisplay notice_style notice_order notice_info'></div>";
}
# ---------------------------------------------------------------------------------- bookings table header
function bookingsTableHtml($pokoje) {
  $h_patra= "<tr class='header1'><td class='bold' colspan=3>umístění</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>příz.</td>
    <td class='bold' style='border-right: 1px solid' colspan=3>1.patro</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>1-</td>
    <td class='bold' style='border-right: 1px solid' colspan=2>1+</td>
    <td class='bold' style='border-right: 1px solid' colspan=8>2.patro</td>
    <td class='bold' rowspan='4' style='border-left:2px solid'>poznámky</td></tr>";
  $h_pokoje= "<tr><td class='bold' colspan=3>pokoj číslo</td>";
  // pokoje
  foreach ($pokoje as $pokoj) {
    $pokoj_num= $pokoj['number'];
    $pokoj_str= $pokoj_num<10 ? "&nbsp;$pokoj_num&nbsp;" : $pokoj_num;
    $h_pokoje.= "<td class='room' onclick=\"popupRoomView('Pokoje', '$pokoj_num');\" title='{$pokoj['note']}'>$pokoj_str</td>";
  }
  
  $h_pokoje.= "</tr>";
  // počty postelí
  $h_postele= '<tr><td class="bold" colspan=3>počet postelí</td>';
  foreach ($pokoje as $pokoj) {
    $h_postele.= "<td class='bold' style='border-right: 1px solid'>{$pokoj['beds']}</td>";
  }
  $h_postele.= "</tr>";
  // počty přistýlek
  $h_pristylky= '<tr class="header2"><td class="bold" colspan=3>přistýlek</td>';
  foreach ($pokoje as $pokoj) {
    $addbeds= $pokoj['addbeds'] ? $pokoj['addbeds'] : '-';
    $h_pristylky.= "<td class='bold' style='border-right: 1px solid'>$addbeds</td>";
  }
  $h_pristylky.= '</tr>';
  return "$h_patra$h_pokoje$h_postele$h_pristylky";
}

function odd($number) {
  return $number % 2 == 1;
}



















//function mesice($path) {  trace();
//  global $CMS, $href0;
//  $user= $_SESSION['web']['fe_user'];
//  $spravce= $user ? access_get(1) : 0;
//  // počet všech pokojů - kvůli *
//  $pokoju_celkem= select("COUNT(*)","tx_gnalberice_room","version=1 AND deleted=''");
//  $ym0= array_shift($path);
//  $month= array( 1=> "leden", "únor", "březen", "duben", "květen", "červen",
//      "červenec", "srpen", "září", "říjen", "listopad", "prosinec");
//
//  // suma měsíců
//  $mesicu= 17 + ($spravce ? 12 : 0);
//  $xx= array();
//  $d= mktime(0, 0, 0, date("n")-3, 2, date("Y"));
//
//  for ($i= 0; $i <= $mesicu; $i++ ) {
//    $m= date("n",$d);
//    $y= date("Y",$d);
//    $from= mktime(0, 0, 0, $m, 1, $y);
//    $until= mktime(0, 0, 0, $m, date("t",$from), $y);
//    $objednavky= array();
//    // zjisti obsazenost a jaké jsou v daném měsíci objednávky
//    $xx["$y-$m"]= (object)array('from'=>$from,'until'=>$until,'obj'=>'');
//    $cr= mysql_qry(" /*$y-$m*/
//      SELECT state,TRIM(rooms1),IF(untilday<fromday,'?',ROUND((untilday-fromday)/86400)),uid
//      FROM tx_gnalberice_order
//      WHERE NOT deleted AND NOT hidden AND untilday>=$from AND $until>=fromday");
//    while ( $cr && (list($state,$kolik,$dnu,$obj)= mysql_fetch_row($cr)) ) {
//      if ( $state==1 ) {
//        if ( !in_array($obj,$objednavky) )
//          $objednavky[]= $obj;
//      }
//      elseif ( $dnu=='?' ) {
//        // přidat hlášení chyby
//      }
//      else {
//        $kolik= ($kolik=='*' ? $pokoju_celkem : (substr_count($kolik,',')+1))*$dnu;
//        $xx["$y-$m"]->obj[$state]+= $kolik;
//      }
//    }
//    $prehled= '';
//    if ($xx["$y-$m"]->obj) foreach ($xx["$y-$m"]->obj as $state=>$kolik) {
//      $prehled.= " $kolik &times; ".pokoj_ikona_popis($state) . "<br>";
//    }
//    $co= count($objednavky);
//    $xx["$y-$m"]->obj= $prehled
//        . ($co ? " <b>&nbsp; $co &times; ".pokoj_ikona_popis(-2)."</b><br>" : '');
//    $d+= date("t",$from)*86400;
//  }
////                                         debug($xx);
//  // zobrazení měsíců
//  $h= "<div id='objednavky' class='x content'><h1>Objednávky Domu setkání</h1>";
//  $year = '';
//  foreach($xx as $ym=>$x) {
//    list($y,$m)= explode('-',$ym);
//    if ($y != $year) {
//      $h .= "<h2>Rok $y</h2>";
//      $year = $y;
//    }
//    if (!$xx["$y-$m"]->obj) {
//      $xx["$y-$m"]->obj = 'Zatím nejsou k dispozici žádné údaje.';
//    }
//
//    $mesic= $month[$m];
//    $sum= $x->obj;
//    $jmp= $CMS ? "onclick=\"go(arguments[0],'$href0!$ym#anchor$ym','');\""
//        : "href='$href0!$ym#anchor$ym'";
//    $h.= $ym==$ym0
//        ? mesic($ym,$x->from,$x->until,$mesic,$path)
//        : "<div class='abstr x'>
//           <a class='abstrakt x' style='height: 10em' $jmp>
//             <h4>$mesic</h4> $sum
//         </a></div>"
//    ;
//  }
//  return $h."</div><br><br><br>";
//}
//# ---------------------------------------------------------------------------------- pokoj_ikona
//
//function pokoj_ikona_popis($state) {
//  switch ($state) {
////   case '-2': $i= "<img border=0 src='fileadmin/icons/mailicon.gif'>"; break;
//    case '-2': $i= "<i class='fa fa-envelope-o'></i>&nbsp;žádost o pobyt"; break;
////   case '-1': $i= "<img border=0 src='fileadmin/icons/newmail.gif'>"; break;
//    case '-1': $i= "<i class='fa fa-pencil-square-o'></i>&nbsp;volných míst"; break;
//    case  '1': $i= "<i class='fa fa-$ico'></i>&nbsp;zájmů o pobyt"; break;
//    case  '2': $i= "<i class='fa fa-user'></i>&nbsp;závazných objednávek"; break;
//    case  '3': $i= "<i class='fa fa-futbol-o'></i>&nbsp;pro akce YMCA"; break;
//    case  '4': $i= "<i class='fa fa-times-circle'></i>&nbsp;nelze pronajmout"; break;
//  }
//  return $i;
//}
//function ikona_legenda() {
//  $h = "<div class='legend float-right' style='width: fit-content; padding: 13px'>";
//  $h .= "<div class='icons_legend'>" . pokoj_ikona(-2) . "&nbsp;Žádost o pobyt podána</div>";
//  $h .= "<div class='icons_legend'>" . pokoj_ikona(-1) . "&nbsp;Požádat o pobyt</div>";
//  //$h .= "<div class='icons_legend'>" . pokoj_ikona(1) . "&nbsp;Zájem o pobyt</div>";  //todo invalid icon, equals to "zájem"
//  $h .= "<div class='icons_legend'>" . pokoj_ikona(2) . "&nbsp;Závazná objednávka</div>";
//  $h .= "<div class='icons_legend'>" . pokoj_ikona(3) . "&nbsp;Probíhá akce YMCA</div>";
//  $h .= "<div class='icons_legend'>" . pokoj_ikona(4) . "&nbsp;Nelze pronajmout</div>";
//  return $h . "</div>";
//}
//
//# ---------------------------------------------------------------------------------- gn_makeDaysList
//function gn_makeDaysList($pid,$pid_goal,$ym,$od,$do) { trace();
//  global $CMS, $href0;
//  //  Zobrazení tabulky obsazenosti
//  $content= ikona_legenda() . barvy_legenda();
//  # ukazani obsazenosti v obdobi $od $do
//  # projiti pokoju - zobrazeni hlavicky
//  $version= substr($ym,0,4)==2014 ? '' : 1; //todo delete? already past
//  $version0= $version ?: 0;
//  $res= mysql_qry("SELECT * FROM tx_gnalberice_room
//                   WHERE NOT deleted AND NOT hidden AND version=$version0 ORDER BY number");
//  $pokoje=array();
//  $rooms_all= array();
//  while($row = mysql_fetch_assoc($res))   {
//    $pokoje[]= $row;
//    $rooms_all[]= $row['number'];
//  }
////                                                         debug($pokoje);
//  $c_obsazenych= $a_obsazenych= $p_obsazenych= 0;
//  // pole pro informace z nabídek
//  $wnames= array ();
//  $wuids= array ();     // obsazenost
//  $wuidss= array ();   // nevyřízené objednávky
//  $wstates= array ();
//  $wnotes= array ();   // poznámky
//  $worders= array ();
//  $qry= "SELECT uid,state,rooms$version AS rooms,fromday,untilday,name,note FROM tx_gnalberice_order
//    WHERE NOT deleted AND NOT hidden AND fromday<=$do AND untilday>=$od ORDER BY fromday";
//  $res= mysql_qry($qry);
//  while($row = mysql_fetch_assoc($res)) {
//    #$gn->gn_debug($row);
//    if ( $row['state'] > 1 ) {
//      // projdeme závazné objednávky
//      for ( $d= $row['fromday']; $d<=$row['untilday']; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
//        $dmy= date('d.m.y',$d);
//        $wnotes[$dmy].= $row['note'] ? (($wnotes[$dmy] ? " + " : "") . $row['note'] ) : "";
//        $rooms= $row["rooms"];
//        $rooms= $rooms=='*' ? $rooms_all : explode(',',$rooms);
//        foreach ($rooms as $n) {
//          $dmyn= "$dmy/$n";
//          $wnames[$dmyn]= ($wnames[$dmy] ? " + " : "") . ($row['name'] ? $row['name'] : $row['note']);
//          $wuids[$dmyn]= $row['uid'];
//          $wstates[$dmyn]= $row['state'];
//        }
//      }
//    }
//    else {
//      // projdeme nezávazné objednávky
//      for ( $d= $row['fromday']; $d<=$row['untilday']; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
//        $dmy= date('d.m.y',$d);
//        $worders[$dmy].= ($worders[$dmy] ? " + " : "") . $row['name'];
//        $wuidss[$dmy].= ($wuidss[$dmy]?',':'') . $row['uid'];
//      }
//    }
//  }
//  // zobrazeni obsazenosti
//  $pokoju= count($pokoje);
//  // projdi zvolený časový interval
//  $odd_counter = 0;
//  for ( $d= $od; $d<=$do; $d= mktime(0,0,0,date("m",$d),date("d",$d)+1,date("Y",$d)) ) {
//    $radek= '';
//    $obsazenych= 0;
//    $odd = odd($odd_counter) ? " odd" : "";
//    foreach ($pokoje as $pokoj) {
//      $n= $pokoj["number"];
//      $dmy= date('d.m.y',$d);
//      $dmyn= "$dmy/$n";
//      $p= $wnames[$dmyn];
//      if ( $p ) {
//        $u= $wuids[$dmyn];
//        $s= $wstates[$dmyn];
//        if ( $s != 4 ) {
//          $obsazenych++;
//          $c_obsazenych++;
//          $a_obsazenych+= $s==3 ? 1 : 0;
//          $p_obsazenych+= $s==2 ? 1 : 0;
//        }
//        if ( $s > 1 ) {
//          $goal= pokoj_ikona($s);
//          $form= "onclick=\"objednavka(arguments[0],'form',{order:'$u'});return false;\" ";
//          $radek.= "<td class='obsazen$odd' style='border-right: 1px solid' title='$p' $form>$goal</td>";
////           $goal= "<img border=0 src='fileadmin/icons/".($s==2?"smile":($s==3?"sun_smile":"stop")).".gif' title='$p'>";
////           $radek.= "<td class='nic $odd'><a href={$gn->index}?id=$pid_goal&from=$od&until=$do&show=$u>$goal</a></td>";
//        }
//        else {
//          $radek.= "<td class='nic $odd' style='border-right: 1px solid'></td>";
//        }
//      }
//      else {
//        $radek.= "<td class='nic $odd' style='border-right: 1px solid'>&nbsp;</td>";
//      }
//    }
//    // styly zobrazení
//    $styl= $obsazenych ? ($obsazenych==$pokoju ? "datum_plno" : "datum_poloplno") : "datum_prazdno";
//    $datum= date("j. n.",$d);
//    $weekend= date("w",$d);
//    $weekend= ($weekend==0 || $weekend==6);
//    $free= $weekend ? "_weekend" : "";
//    $content.= "<tr>";
//    // sloupec pro datum
//    $content.= "<td class='datum$free$odd'>$datum</td>";
//    // sloupec pro novou objednavku
//    if ( $obsazenych==$pokoju  || $d<time() )
//      $content.= "<td class='$styl$odd'></td>";
//    else {
//      $den= date("Y-m-d",$d);
//      $form= "onclick=\"objednavka(arguments[0],'form',{den:'$den'});return false;\" ";
//      $goal= pokoj_ikona(-1);
//      $content.= "<td class='sent $styl$odd' title='chci objednat pobyt' $form>$goal</td>";
//    }
////     $goal= "<img border=0 src='fileadmin/icons/newmail.gif' title='chci objednat pobyt'>";
////     $novy= "<a href={$gn->index}?id=$pid_goal&from=$od&until=$do&anew=1&date=$d>$goal</a>";
////     $content.= "<td class=$styl>" . ($obsazenych==$pokoju || $d<time() ? "" : $novy) . "</td>";
//    // sloupec pro čekající objednávky
//    $show= "";
//    if ( $wuidss[$dmy] ) {
//      # zobrazeni objednavek
//      $tit= "objednávka pobytu pro {$worders[$dmy]}";
//      $goal= pokoj_ikona(-2);
//      $form= "onclick=\"objednavka(arguments[0],'wanted',{orders:'{$wuidss[$dmy]}'});\" ";
//      $content.= "<td  title='$tit' class='sent $styl$odd' $form>$goal</td>";
////       $goal= "<img border=0 src='fileadmin/icons/mailicon.gif' title='{$worders[$dmy]}'>";
////       $show= "<a href={$gn->index}?id=$pid_goal&from=$od&until=$do&show={$wuidss[$dmy]}>$goal</a>";
//    }
//    else {
//      $content.= "<td class='$styl$odd'></td>";
//    }
//    $content.= "$radek";
//    // sloupec poznámek
//    $note= $wnotes[$dmy];
//    if ( mb_strlen($note)>50 ) {
//      $note= x_shorting($note,50) . ' ...';
//    }
//    $content.= "<td class='pozn$free$odd' title='$wnotes[$dmy]'>$note</td>";
//    $content.= "</tr>";
//    $odd_counter++;
//  }
////                                                 display("<table>$content</table>");
//
////   $c_obsazenost= $gn->userlevel>=SUPER ? ("obsazenost = ".number_format(100 * $c_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost
////   $a_obsazenost= $gn->userlevel>=SUPER ? ("akce = ".number_format(100 * $a_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost akcemi
////   $p_obsazenost= $gn->userlevel>=SUPER ? ("pobyty = ".number_format(100 * $p_obsazenych/($pokoju*$dnu),1)." %") : '&nbsp;';      // obsazenost pobytem
//  $content.= "\n</tr>";
//  // hlavička tabulky
//  $h_patra= $version0==0 ? "
//    <tr class='header1' ><td class='bold' colspan=3>&nbsp;</td>
//    <td class='bold' style='border-right: 1px solid' colspan=7>2.patro</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>1+</td>
//    <td class='bold' style='border-right: 1px solid' colspan=3>1.patro</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>P+</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>příz.</td>" : "
//    <tr class='header1'><td class='bold' colspan=3>&nbsp;</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>příz.</td>
//    <td class='bold' style='border-right: 1px solid' colspan=3>1.patro</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>1-</td>
//    <td class='bold' style='border-right: 1px solid' colspan=2>1+</td>
//    <td class='bold' style='border-right: 1px solid' colspan=8>2.patro</td>";
//  $h_patra_backwards = $h_patra . "</tr>";
//  $h_patra .= "<td class='bold' rowspan='4' style='border-left:2px solid'>poznámky</td></tr>";
//  $h_pokoje= "<tr><td class='bold' colspan=3>pokoj:</td>";
//  // pokoje
//  foreach ($pokoje as $pokoj) {
//    $etage= $pokoj['etage']+254;     // viz popisy poschodí
//    $pokoj_str= $pokoj['number'];
//    $pokoj_str= $pokoj_str<10 ? "&nbsp;$pokoj_str&nbsp;" : $pokoj_str;
//    $au= "go(arguments[0],'$href0!$ym!$etage#anchor$ym','$href0!$ym!$etage#anchor$ym');";
//    $h_pokoje.= "<td class='room' onclick=\"$au\" title='{$pokoj['note']}'>$pokoj_str</td>";
////     $h_pokoje.= "<td class=room><a href={$gn->index}?id=pokoje&case=$etage title='{$pokoj['note']}'>$pokoj_str</a></td>";
//  }
//  $h_pokoje.= "</tr>";
//  // počty postelí
//  $h_postele= '<tr><td class="bold" colspan=3>postelí:</td>';
//  foreach ($pokoje as $pokoj) {
//    $h_postele.= "<td class='bold' style='border-right: 1px solid'>{$pokoj['beds']}</td>";
//  }
//  $h_postele.= "</tr>";
//  // počty přistýlek
//  $h_pristylky= '<tr class="header2"><td class="bold" colspan=3>přistýlek:</td>';
//  foreach ($pokoje as $pokoj) {
//    $addbeds= $pokoj['addbeds'] ? $pokoj['addbeds'] : '-';
//    $h_pristylky.= "<td class='bold' style='border-right: 1px solid'>$addbeds</td>";
//  }
//  $h_pristylky_backwards = $h_pristylky . "<td rowspan='4' class=\"bold\" style='border-left:2px solid;
//           border-top: 3px solid; border-bottom: 3px solid'>poznámky</td></tr>";
//  $h_pristylky.= '</tr>';
//  // připojení hlavičky
//  $html= "<div style='overflow-x: scroll;
//    width: 100%;'><table id='dum' class=dum>";
//  $html.= "$h_patra$h_pokoje$h_postele$h_pristylky";
//  $html.= $content;
//  $html.= "$h_pristylky_backwards$h_postele$h_pokoje$h_patra_backwards";
//  $html.= "</table></div>\n";
//  end:
//  return $html;
//}
//function mesic($ym,$from,$until,$mesic,$path) {  //trace();
//  global $CMS, $href0;
//  $id= array_shift($path);
//  display("id=$id");
//  popup("Objednávka","","$href0!$ym",'order');
//  $obsah= gn_makeDaysList(0,0,$ym,$from,$until);
//  if ( $id ) {
//    if ( is_numeric($id) ) {
//      $text= select("text","setkani4.tx_gncase_part","cid=$id");
//      popup("Pokoje",$text,"$href0!$ym#anchor$ym");
//    }
//  }
//  $back= "onclick=\"go(arguments[0],'$href0','');\"";
//  $h= "<div id='pokoje' class='x'><span class='anchor' id='anchor$ym'></span>
//         <div class='x'><div id='mesic$ym' class='pokoje x'>
//           <div class='text'><h2 class='float-left' $back>$mesic</h2>$obsah</div>
//         </div>
//       </div></div>";
//  return $h;
//}
?>
