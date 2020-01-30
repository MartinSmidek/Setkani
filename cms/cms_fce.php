<?php

// ---------------------------------------------------------------------------------------------- //
// funkce aplikace Ezer/CMS společné pro FE (nepřihlášené) a BE (přihlášené)                      //
//                                                                                                //
// CMS/Ezer                                             (c) 2016 Martin Šmídek <martin@smidek.eu> //
// ---------------------------------------------------------------------------------------------- //

/** ===========================================================================================> GIT */
# --------------------------------------------------------------------------------------- img oprava
# provede git par.cmd>.git.log a zobrazí jej
function git_make($par) {
  $cmd= $par->cmd;
  $msg= "";
  // proveď operaci
  switch ($par->op) {
  case 'cmd':
    $state= 0;
    // zruš starý obsah .git.log
    $f= @fopen("filename.txt", "r+");
    if ($f !== false) {
        ftruncate($f, 0);
        fclose($f);
    }
//    system("git {$par->cmd}>.git.log",$state);
    exec("git {$par->cmd}>.git.log",$state);
  case 'show':
    $msg= file_get_contents(".git.log");
    $msg= nl2br($msg);
    break;
  }
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
# ------------------------------------------------------------------------------------------ session
# getter a setter pro _SESSION
function session($is,$value=null) {
  $i= explode(',',$is);
  if ( is_null($value) ) {
    // getter
    switch (count($i)) {
    case 1: $value= $_SESSION[$i[0]]; break;
    case 2: $value= $_SESSION[$i[0]][$i[1]]; break;
    case 3: $value= $_SESSION[$i[0]][$i[1]][$i[2]]; break;
    }
  }
  else {
    // setter
    switch (count($i)) {
    case 1: $_SESSION[$i[0]]= $value; break;
    case 2: $_SESSION[$i[0]][$i[1]]= $value; break;
    case 3: $_SESSION[$i[0]][$i[1]][$i[2]]= $value; break;
    }
    $value= 1;
  }
  return $value;
}
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
  $tr= mysql_qry("
    SELECT COUNT(*),skupina,MAX(poradi) FROM gnucast WHERE datum='$den' GROUP BY skupina");
  while ( $tr && (list($pocet,$nazev,$maxim)= mysql_fetch_row($tr)) ) {
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
  $tr= mysql_qry("
    SELECT skupina,MAX(poradi) FROM gnucast WHERE timestamp=$stamp GROUP BY skupina");
  while ( $tr && (list($nazev,$maxim)= mysql_fetch_row($tr)) ) {
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
  $pid= mysql_insert_id();
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
