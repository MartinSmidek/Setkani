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
    if ( !$x->order ) list($x->order)= explode(',',$x->orders);
    dum_form($x);
    break;

  case 'create':
    $now= time();
    $flds= 'crdate,tstamp';
    $vals= "$now,$now";

    if ( !$x->form->untilday )  $x->form->untilday= $x->form->fromday;
    // v x.form jsou předána všechna pole
    foreach($x->form as $fld=>$val) {
      if ($fld === 'uid') continue;
      $val= trim(mysql_real_escape_string($val));
      if ($val=='') continue;
      if ( $fld=='rooms1' && $val=='*' ) {
        $val= select1("GROUP_CONCAT(number ORDER BY number SEPARATOR ',')",'tx_gnalberice_room',
            "NOT deleted AND NOT hidden AND version=1");
      }
      $flds.= ",$fld";
      $vals.= ",'$val'";
    }
    $y->ok= query("INSERT INTO tx_gnalberice_order ($flds) VALUES ($vals)");
//                                                         display("insert=$y->ok");
    $y->order= mysql_insert_id();
    if (!$y->ok) {
      if ($y->error) $y->error = ''; //delete, function _objednavky() is not called otherwise
      $y->msg = "Objednávku se nepodařilo dokončit.";
      break;
    }
    $email = trim($x->form->email);
    $forward_to = "dum@setkani.org";
    $snd_copy = "ivana.zivnustkova@seznam.cz";
    $snd_copy_name = "Živnůstkovi";
    if (!$email || $email === '' || !strpos($email, "@")) {
      mail_send_cc($forward_to, $forward_to, "Objednávka pobytu v Domě Setkání", new_order_mail_from_form($x->form), $snd_copy, $snd_copy_name);
      $y->completion = "Objednávka neobsahuje emailovou adresu. Objednávka <b>byla úspěšně podána</b>, ale nedojde vám potvrzovací email. Údaje se objeví v kalendáři po aktualizaci této stránky.";
    } else {
      mail_send_cc($forward_to, $email, "Objednávka pobytu v Domě Setkání", new_order_mail_from_form($x->form), $forward_to, "Dům Setkání", $snd_copy, $snd_copy_name);
      $y->completion = "Objednávka byla úspěšně zaslána. Na email vám brzy přijde její shrnutí. Údaje se objeví v kalendáři po aktualizaci této stránky.";
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
        $val= trim(mysql_real_escape_string($val));
        if ( $fld=='rooms1' && $val=='*' ) {
          $val= select1("GROUP_CONCAT(number ORDER BY number SEPARATOR ',')",'tx_gnalberice_room',
              "NOT deleted AND NOT hidden AND version=1");
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

  case 'check_rooms':
    $y->free_rooms = get_free_rooms($x->fromday, $x->untilday);
    break;

  case 'get_room':
    $y->room_data = get_room($x->room_number);
    break;

  case 'get_price':
    require_once("duplicate.php");
    $y->price = ds_order_price_for($x->data->days, $x->data->adults, $x->data->kids_10_15,
            $x->data->kids_3_9, $x->data->kids_3, $x->data->board, $x->data->rooms1, $x->freeRooms, $x->inclOrder);
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

  $rooms_nums = array();
  $res= mysql_qry("SELECT number FROM tx_gnalberice_room WHERE NOT deleted AND NOT hidden AND version=1");
  while($row = mysql_fetch_assoc($res)) $rooms_nums[]= $row["number"];
  $pokoje = implode("|", $rooms_nums);

  $dum_data_open = 0;
  $all_house_checkbox_enabled = -1;
  $is_new = $ord == 0 || !$ord;
  $checkbox_checked = true;
  if ( $ord > 0 ) {  // existující objednávka !!! pak jen správce
    $dum_data= select("*","setkani4.tx_gnalberice_order","uid=$ord");
    if (!$dum_data || !count($dum_data)) {
      $y->html = "<h4>Objednávka číslo $ord neexistuje.</h4>" .f_button("Zavřít","block_display('order',0);",1);
      return;
    }
    $dum_data->fromday= date("Y-m-d",$dum_data->fromday);
    $dum_data->untilday= date("Y-m-d",$dum_data->untilday);
    $dum_data->new = false;
    $pokoje_title =  "objednané pokoje";
    if ($spravce) {
      $all_house_checkbox_enabled = 1;
    }
    //!!! valid code, because 0 -> found asterisk and !0 == true --> use if (value == false)
    if (strpos($dum_data->rooms1, "*") === false) { //no asterisk, check all numbers
      $booked = explode(",", $dum_data->rooms1);
      foreach ($rooms_nums as $_key => $value) {
        $checkbox_checked = $checkbox_checked && in_array($value, $booked);
      }
    }
  } elseif ( $is_new ) {
    $dum_data_open= 1;
    $dum_data= (object)array();
    $dum_data->new = true;
    $dum_data->fromday= $x->den;
    // upravíme default: odjezd=příjezd+2, adults=2
    list($r,$m,$d)= explode('-',$x->den);
    $dum_data->untilday= date("Y-m-d",mktime(0, 0, 0, $m, $d+2, $r));
    $dum_data->adults= 2;

    $pokoje_data_array = get_free_rooms(strtotime($dum_data->fromday), strtotime($dum_data->untilday));
    $pokoje_title = count($pokoje_data_array["content"]) > 0 ?
        ("volné pokoje: " . implode(", ", $pokoje_data_array["content"])) : "žádné volné pokoje";
    if ($pokoje_data_array["all_free"]) $all_house_checkbox_enabled = 1;
    $checkbox_checked = false;
    if ( $user ) {
      list($dum_data->name,$dum_data->telephone,$dum_data->email,
           $dum_data->address,$dum_data->zip,$dum_data->city)
        = select("CONCAT(firstname,' ',name),telephone,email,address,zip,city","fe_users","uid=$user");
    }
  } else {
    $y->html = "<h4>Číslo $ord není korektním číslem objednávky.</h4>" .f_button("Zavřít","block_display('order',0);",1);
    return;
  }

  $y->html= "<table style='margin: 0 auto;'><tr><td class='order_left_td'>"
  . f_input("objednávka číslo","uid",2,0)."&nbsp;&nbsp;"
  . f_select("stav objednávky","state", "1:zájem o pobyt,2:závazná objednávka,3:akce YMCA,4:nelze pronajmout",$ord, 'max-width: 190px') . "<br>"
  . f_date("příjezd",            "fromday",       8, 1, 'getRoomsForTimespan(true, this);', "fromday_input")."&nbsp;&nbsp;"
  . f_date("odjezd",             "untilday",      8, 1, 'getRoomsForTimespan(false, this);', "untilday_input") . "<br>" . f_error_msg("ord_date_error")
  . f_celydum_checkbox($all_house_checkbox_enabled, $checkbox_checked)
  . f_select("typ stravy","board","1:penze,2:polopenze,3:bez stravy", 1, 'max-width: 170px') . "<br>"
  . f_input($pokoje_title,"rooms1",40,1, 'text', '', "objednejte pokoje jejich čísly oddělenými čárkou", "rooms_label")."<br>" . f_error_msg("ord_rooms_error")
  . f_input("dospělých",          "adults",        3, 1, 'number', 'max-width: 80px', '', '', " min='0'")
  . f_input("děti 10-15",         "kids_10_15",    3, 1, 'number', 'max-width: 80px', '', '', " min='0'")
  . f_input("děti 3-9",           "kids_3_9",      3, 1, 'number', 'max-width: 80px', '', '', " min='0'")
  . f_input("děti do 3 let",      "kids_3",        3, 1, 'number', 'max-width: 80px', '', '', " min='0'")."<br>" . f_error_msg("ord_adults_error")
  . f_input("poznámka k objednávce","note",35)."<br>"
  . ($is_new ? "<h4 id='approx_price' style='margin-bottom: 0;' title='Dolní odhad ceny pobytu, reálná cena může být vyšší.'>Orientační cena: <span id='order_final'><span style='color: gray'>musíte vybrat pokoje</span></span></h4>
          <div id='error_price'></div><div id='info_price' style='font-size: 10pt;'></div></td><td class='order_right_td'>"
          : "</td><td class='order_right_td'>")
  . f_input("jméno a příjmení",   "name",         18) . "<br>" . f_error_msg("ord_name_error")
  . ( $dum_data_open || $spravce ? (
      f_input("telefon",          "telephone",    10)."<br>" . f_error_msg("ord_phone_error")
    . f_input("email",            "email",        25)."<br>" . f_error_msg("ord_email_error")
    . f_input("ulice",            "address",      25)."<br>"
    . f_input("psč",              "zip",          10)
    . f_input("obec",             "city",         16)."<br><br>"
  ) : '<br><br>(Osobní údaje jsou přístupné pouze pro správce Domu setkání)')
  . ( !$is_new && $spravce ? (
        f_button("Opravit","block_enable('order',1,'uid'); jQuery('#order_save').attr('hidden', false); jQuery(this).attr('hidden', true);") . f_button_sep()
      . f_button("Uložit","objednavka(0,'update',{order:'$ord',rooms:'$pokoje'});",0,'order_save') . f_button_sep()
      . f_button("Smazat","objednavka(0,'delete',{order:'$ord'});") . f_button_sep()
      . f_button("Zpět","block_display('order',0);", 0, '')
      ) : (
      !$is_new ? (
        "<br>".f_button("Zavřít","block_display('order',0);",1)
      ) : (
          f_button("Přidat objednávku","objednavka(0,'create',{rooms:'$pokoje'},this);") . f_button_sep()
      . f_button("Zrušit","block_display('order',0,'uid');")
      )
    ))
  . "</td></tr></table>";
}
function f_input($label,$name,$size,$enabled=1,$type='text',$css='',$hint='', $labelid='', $attrs='') { //trace();
  global $dum_data, $dum_data_open, $kernel;
  $disabled= $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : ' ');
  $price_calc = $dum_data->new ? " runOrderCounter();" : "";
  $onchange=  $kernel=='ezer3.1' 
      ? "onchange='jQuery(this).addClass(\"changed\");$price_calc'"
      : "onchange='this.addClass(\"changed\");$price_calc'";
  $inputid = $labelid ? "id='input_$labelid'" : "";
  $labelid = $labelid ? "id='$labelid'" : "";
  $html= " <label style='$css'><span $labelid content='$label' style='font-size: 10pt'>$label</span>
    <input $inputid name='$name' type='$type' placeholder='$hint' size='$size' $disabled $onchange $attrs value='{$dum_data->$name}'></label>";
  return $html;
}
function f_date($label,$name,$size,$enabled,$js,$id) {
  global $dum_data, $dum_data_open, $kernel;
  $disabled= $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : ' ');
  //price calculation run as a part of getAvailableRooms (js variable content)
  $onchange=  $kernel=='ezer3.1'
      ? "onchange='jQuery(this).addClass(\"changed\");$js'"
      : "onchange='this.addClass(\"changed\");$js'";
  $html= " <label id='label_$id'>$label<input id='$id' name='$name' type='date' size='$size' $disabled $onchange 
    value='{$dum_data->$name}'></label>";
  return $html;
}
function f_celydum_checkbox($enabled=1,$checked=false) {
  //!! this is tied to jquery functionality in custom.js and "pokoje" input field - think twice before modifying
  global $dum_data_open, $kernel;
  $attrs = $enabled==-1 ? 'disabled' : (!$enabled || !$dum_data_open ? 'disabled' : '');
  $not_allowed = (!$checked && $attrs) ? " (nelze)" : "";
  if ($checked) $attrs .= " checked ";
  $onchange=  $kernel=='ezer3.1'
      ? "onchange='if(jQuery(this).prop( \"checked\" )) {setRoomsAllBooked(); jQuery(\"#approx_price\").addClass(\"nodisplay\");} else {unsetRoomsAllBooked(getLastRoomsTitle(), true); jQuery(\"#approx_price\").removeClass(\"nodisplay\");}'"
      : ""; //not implemented as kernel will probably never fall down to older version
  $html= " <label style='margin: 8px 77px 0 0;'><input name='obj_cely_dum' id='obj_cely_dum_check' type='checkbox' $attrs $onchange>
     <span style='font-size: 10pt; display: inline-block' id='obj_cely_dum_text'>zájem o celý<br> dům$not_allowed</span></label>";
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
  $price_calc = $dum_data->new ? " runOrderCounter();" : "";
  $onchange=  $kernel=='ezer3.1' 
      ? "onchange='jQuery(this).addClass(\"changed\");$price_calc'"
      : "onchange='this.addClass(\"changed\");$price_calc'";
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
function f_error_msg($id) {
  return "<span id='$id' class='nodisplay order_error_msg'></span>";
}
function new_order_mail_from_form($form) {
  return "<p>Dobrý den $form->name, <br>
            děkujeme za objednávku pobytu v Domě Setkání. Zasíláme shrnutí Vaší objednávky.</p>
            <p>Pokud si přejete zaplatit předem (bankovní převod), napište nám a podrobnosti vyřešíme přes email. V opačném případě se platby řeší přímo na místě se správcem.</p>"
            . form_to_mail_string($form) .
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
function get_free_rooms($from, $until) {
  $data = get_days_data($from, $until);
  $rooms = $rooms_n_orders = array();
  foreach ($data as $tstamp => $days_data) {
    if ($tstamp < 100) continue;
    foreach ($days_data->pokoje as $room => $room_data) $rooms[$room] = 0;
    break;
  }

  $days = 0;
  foreach ($data as $tstamp => $days_data) {
    if ($tstamp < 100) continue;
    foreach ($days_data->pokoje as $room => $room_data) {
      if (!$room_data->pset) {
        $rooms[$room]++;
        if ($room_data->p) $rooms_n_orders[$room]++;
      }
    }
    $days++;
  }
  $content = $and_orders = array();
  $pokoju = 0;
  foreach ($rooms as $room => $count) {
    if ($count == $days) {
      $content[]= $room;
      $pokoju++;
    }
  }

  foreach ($rooms_n_orders as $room => $count) {
    if ($count) $and_orders[]= $room;
  }
  return array("content" => $content, "all_free" => $pokoju==$data["1"], "incl_orders" => $and_orders);
}
function escape_nostring($value, $esc="-") {
  if (!$value) return $esc;
  return $value;
}
# ----------------------------------------------------------------------------------------==> mesice
# 1=zájem o pobyt, 2=závazná objednávka, 3=akce YMCA, 4=nelze pronajmout );
function mesice($path) {  trace();
  global $CMS;
  popup("Objednávka","","",'order');

  $user= $_SESSION['web']['fe_user'];
  $spravce= $user ? access_get(1) : 0;
  $month= array( 1=> "leden", "únor", "březen", "duben", "květen", "červen",
    "červenec", "srpen", "září", "říjen", "listopad", "prosinec");

  // suma měsíců
  $mesicu= 17;
  if ($spravce) $mesicu += 12;

  $xx= array();
  $d= mktime(0, 0, 0, date("n") + ($CMS ? -3 : -1), 2, date("Y"));

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
          //$s= $wstates[$dmyn];
          //if ( $s != 4 ) we want to include also blocked rooms
          $obsazenych++;
        }
      }
      // kalendář na úvodku
      $day = date('j',$dd);
      $styl= $obsazenych ? ($obsazenych==$pokoju_celkem ? "obsazeno" : "konflikt") : "volno";
      $calendar .= "<td class='month_day odd clickable $styl' content='$dd' onclick=\"getDaysData(this, '$y-$m', '$y')\">$day</td>";
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
      if ($state < 2) {
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
        $u = $wuids[$dmyn];
        $s = $wstates[$dmyn];
        if ($s < 2) {
          $pset = false;
        } else {
          $pset = true;
          $obsazenych++;
        }
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
# 4>=$state>=0  obsazenost pokoje (1 - zájem, 0 - volné)

# not in DB:
# $state=-1     vyvolání žádosti
# $state=-2     poslanou poptávku
# $state=-3     used as "unable to order icon"
function pokoj_ikona($state) {
  switch ($state) {
    case  '-3': return "<i class='fa fa-times'></i>";
    case '-2': return "<i class='fa fa-envelope-o'></i>";
    case '-1': return "<i class='fa fa-pencil-square-o'></i>";
    case  '1': return ""; //zájem o pobyt
    case  '2': return "<i class='fa fa-user'></i>";
    case  '3': return "<i class='fa fa-futbol-o'></i>";
    case  '4': return "<i class='fa fa-times-circle'></i>";
    //case  '5': return "<i class='fa fa-home'></i>";
    default: return "";
  }
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
  //$h .= "<div class='icons_legend'>" . pokoj_ikona(5) . "&nbsp;Zájem o celý dům</div>";
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
  $result = "<div class='commentary_div'>
    Na akce YMCA se přihlašujete dle instrukcí v článku dané akce, neobjednáváte si pobyt v domě samostatně.
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
        <table class='dum'><tbody>
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
    <br><br>Každý pokoj má svoji ikonu podle stavu objednávky. Pokud pokoj není závazně objednán, je pole prázdné. Na ikony lze kliknout a zobrazit tak informace k objednávce na daný pokoj. Například, v ukázce jsou pokoje 1, 2, 11, 12 zarezervovány, nelze objednat pokoje 13-16, ale 17+ ano. Navíc je tento den součástí jedné nebo více žádostí o pobyt (ikona obálky).
    <br>Žádost o všechny pokoje, tedy celý dům, je brána jako žádost o pobyt, nově ale při schválení <i>může být zaznamenána</i> jako <b>Zájem o dům</b>. Takto poznačená objednávka <b>je závazná</b>, slouží jako předčasná blokace domu pro skupiny zájemců, u kterých často nejsou ještě známi všichni účastníci.
    <br>$barvy_hint
    <br>Jednotlivé dny se barevně liší - víkendy jsou vyznačeny modře a stav dne vyjádřen stylem semaforu - zelená: úplně volný, červená: úplně obsazený. V ukázce je dům částečně obsazen.</p>
    <p>Objednávací formulář obsahuje kalkulačku ceny <b>která ovšem nemusí být konečná.</b> Záleží pak na konkrétní domluvě se správcem (například když část zákazníků přijede později).
    Nezávazná žádost o objednávku musí být schválena správcem - pak se objednávka stává závaznou a dané pokoje jsou obsazeny ikonou, která po kliknutí zobrazí informace k objednávce.</p>
    </div>
    <hr class='dark'>";
  return $result . "</div>";
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
?>
