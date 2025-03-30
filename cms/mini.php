<?php
// <editor-fold defaultstate="collapsed" desc="++++++++++++++++++++++++++ EZER functions">
/** ==========================================================================================> EZER */
function wu($x) { return $x; }
# ------------------------------------------------------------------------------------- ezer browser
# identifikace prohlížeče a operačního systému
function ezer_browser(&$abbr,&$version,&$platform,$agent=null ) {
  if ( !$agent ) $agent= $_SERVER['HTTP_USER_AGENT'];
  // identifikace prohlížeče
  if     ( preg_match('/Edge\/([\d\.])*/',   $agent,$m) ) { $abbr='EG'; $version= $m[0]; }
  elseif ( preg_match('/Trident|MSIE\/([\d\.])*/', $agent,$m) ) { $abbr='IE'; $version= $m[0]; }
  elseif ( preg_match('/Vivaldi\/([\d\.])*/',$agent,$m) ) { $abbr='VI'; $version= $m[0]; }
  elseif ( preg_match('/Opera\/([\d\.])*/',  $agent,$m) ) { $abbr='OP'; $version= $m[0]; }
  elseif ( preg_match('/Firefox\/([\d\.])*/',$agent,$m) ) { $abbr='FF'; $version= $m[0]; }
  elseif ( preg_match('/Chrome\/([\d\.])*/', $agent,$m) ) { $abbr='CH'; $version= $m[0]; }
  elseif ( preg_match('/Safari\/([\d\.])*/', $agent,$m) ) { $abbr='SF'; $version= $m[0]; }
  else { $abbr='?'; $version= '?/?'; }
  // identifikace platformy prohlížeče: Android => Ezer.client == 'A'
  $platform =          // x11 hlásí Chrome při vzdáleném ladění (chrome://inspect/#devices)
	preg_match('/Windows Phone|Windows Mobile/i',$agent)      ? 'P' : (
	preg_match('/Android/i',$agent)                           ? 'A' : (
	preg_match('/iPad|iPhone/i',$agent)                       ? 'I' : (
	preg_match('/linux/i',$agent)                             ? 'L' : (
	preg_match('/macintosh|Mac OS X|Power_PC|PPC/i',$agent)   ? 'M' : (
	preg_match('/Windows|win32|Windows NT/i',$agent)          ? 'W' : '?'
  )))));
}
# -------------------------------------------------------------------------------------- kolik_1_2_5
# výběr správného tvaru slova podle množství a tabulky tvarů pro 1,2-4,5 a více
# např. kolik_1_2_5(dosp,"dospělý,dospělí,dospělých")
function kolik_1_2_5($kolik,$tvary) { //trace();
  $tvar= explode(',',$tvary);
  return "$kolik ".($kolik>4 ? $tvar[2] : ($kolik>1 ? $tvar[1] : ($kolik>0 ? $tvar[0] : $tvar[2])));
}
# -------------------------------------------------------------------------------------------- trace
# $note je poznámka uvedená za trasovací informací
function trace($note='',$coding='') {
  global $trace, $totrace;
  if ( strpos($totrace,'u')===false ) return;
  $act= debug_backtrace();
  $x= call_stack($act,1).($note?" / $note":'');
  global $CMS;
  if ( $CMS ) {
    $time= date("H:i:s");
    $trace.= "$time $x<br>";
  }
  else {
    $rx= str_replace("'",'"',$x);
    $line= str_pad($act[1]['line'],4,' ',STR_PAD_LEFT);
    $trace.= "<script>console.log( ':  trace/$line: $rx' );</script>";
  }
}
function display($x) {
  global $trace, $totrace;
  if ( strpos($totrace,'u')===false ) return;
  global $CMS;
  if ( $CMS ) {
    $trace.= "$x<br>";
  }
  else {
    $rx= str_replace("'",'"',$x);
    $stack= debug_backtrace();
    $line= str_pad($stack[0]['line'],4,' ',STR_PAD_LEFT);
    $trace.= "<script>console.log( ':display/$line: $rx' );</script>";
  }
}
function debug($x,$label=false,$options=null) {
  global $trace, $totrace;
  if ( strpos($totrace,'u')===false ) return;
  global $CMS;
  if ( $CMS ) {
    debug1($x,$label,$options);
  }
  else {
//    $x= pdo_real_escape_string(var_export($x,true));
    $x= str_replace('"',"'",var_export($x,true));
    $x= str_replace("\n",'\n',$x);
    $stack= debug_backtrace();
    $line= str_pad($stack[0]['line'],4,' ',STR_PAD_LEFT);
    $trace.= "<script>console.log( \":  debug/$line: $x\" );</script>";
  }
}
# -------------------------------------------------------------------------------------------- debug
# vygeneruje čitelný obraz pole nebo objektu
# pokud jsou data v kódování win1250 je třeba použít  debug($s,'s',(object)array('win1250'=>1));
# options:
#   gettype=1 -- ve třetím sloupci bude gettype(hodnoty)
function debug1($gt,$label=false,$options=null) {
  global $trace, $debug_level;
  $debug_level= 0;
  $html= ($options && $options->html) ? $options->html : 0;
  $depth= ($options && $options->depth) ? $options->depth : 64;
  $length= ($options && $options->length) ? $options->length : 64;
  $win1250= ($options && $options->win1250) ? $options->win1250 : 0;
  $gettype= ($options && $options->gettype) ? 1 : 0;
  if ( is_array($gt) || is_object($gt) ) {
    $x= debugx($gt,$label,$html,$depth,$length,$win1250,$gettype);
  }
  else {
//     $x= $html ? htmlentities($gt) : $gt;
    $x= $html ? htmlspecialchars($gt,ENT_NOQUOTES,'UTF-8') : $gt;
    $x= "<table class='dbg_array'><tr>"
      . "<td valign='top' class='title'>$label</td></tr><tr><td>$x</td></tr></table>";
  }
  if ( $win1250 ) $x= wu($x);
//   $x= strtr($x,'<>','«»'); //$x= str_replace('{',"'{'",$x);
  $trace.= $x;
  return $x;
}
function debugx(&$gt,$label=false,$html=0,$depth=64,$length=64,$win1250=0,$gettype=0) {
  global $debug_level;
  if ( $debug_level > $depth ) return "<table class='dbg_over'><tr><td>...</td></tr></table>";
  if ( is_array($gt) ) {
    $debug_level++;
    $x= "<table class='dbg_array'>";
    $x.= $label!==false
      ? "<tr><td valign='top' colspan='".($gettype?3:2)."' class='title'>$label</td></tr>" : '';
    foreach($gt as $g => $t) {
      $x.= "<tr><td valign='top' class='label'>$g</td><td>"
      . debugx($t,NULL,$html,$depth,$length,$win1250,$gettype) //TEST==1 ? $t : htmlspecialchars($t)
      .($gettype ? "</td><td>".gettype($t) : '')                      //+typ
      ."</td></tr>";
    }
    $x.= "</table>";
    $debug_level--;
  }
  else if ( is_object($gt) ) {
    $debug_level++;
    $x= "<table class='dbg_object'>";
    $x.= $label!==false ? "<tr><td valign='top' colspan='".($gettype?3:2)."' class='title'>$label</td></tr>" : '';
//     $obj= get_object_vars($gt);
    $len= 0;
    foreach($gt as $g => $t) {
      $len++;
      if ( $len>$length ) break;
//       if ( is_string($t) ) {
//         $x.= "<td>$g:$t</td>";
//       }
//       if ( $g=='parent' ) {
//         $td= $t==null ? "<td class='label'>nil</td>" : (
//           is_object($t) && isset($t->id) ? "<td class='label'>{$t->id}</td>" : (
//           is_string($t) ? "<td>$t</td>" :
//           "<td class='label'>?</td>"));
//         $x.= "<tr><td class='dbg_over'>$g:</td>$td</tr>";
//       }
//       else {
        $x.= "<tr><td valign='top' class='label'>$g:</td><td>"
        . debugx($t,NULL,$html,$depth,$length,$win1250,$gettype) //TEST==1 ? $t : htmlspecialchars($t)
        .($gettype ? "</td><td>".gettype($t) : '')                      //+typ
        ."</td></tr>";
//       }
    }
    $x.= "</table>";
    $debug_level--;
  }
  else {
    if ( is_object($gt) )
      $x= "object:".get_class($gt);
    else
//       $x= $html ? htmlentities($gt) : $gt;
      $x= $html ? htmlspecialchars($gt,ENT_NOQUOTES,'UTF-8') : $gt;
//       if ( is_string($x) ) $x= "'$x'";
  }
  return $x;
}
function fce_error($x) {
  global $trace;
//  if ( function_exists('pdo_real_escape_string') )
//    $x= pdo_real_escape_string($x);
  global $CMS;
  $x= strtr($x,array('"'=>'`',"'"=>'`'));
  if ( $CMS ) {
    $trace.= "ERROR: $x<br>";
  }
  else {
    $trace.= "<script>console.log( 'ERROR: " . $x . "' );</script>";
  }
}
# --------------------------------------------------------------------------------------- call_stack
function call_stack($act,$n,$hloubka=2,$show_call=1) { #$this->debug($act,'call_stack');
  $fce= isset($act[$n]['class'])
    ? "{$act[$n]['class']}{$act[$n]['type']}{$act[$n]['function']}" : $act[$n]['function'];
  $del= '';
  $max_string= 36;
  mb_internal_encoding("UTF-8");
  $args= '';
  if ( $show_call and isset($act[$n]['args']) )
  foreach ( $act[$n]['args'] as $arg ) {
    if ( is_string($arg) ) {
      $arg= mb_substr(htmlspecialchars($arg,ENT_NOQUOTES,'UTF-8'),0,$max_string)
          .(mb_strlen($arg)>$max_string?'...':'');
    }
    $typ= gettype($arg);
    $val= '';
    switch ( $typ ) {
    case 'boolean': case 'integer': case 'double': case 'string': case 'NULL':
      $val= $arg; break;
    case 'array':
      $val= count($arg); break;
    case 'object':
      $val= get_class($arg); break;
    }
    $args.= "$del$typ:$val";
    $del= ',';
  }
  $from= '';
  /*
  for ($k= $n; $k<$n+$hloubka; $k++) {
    if ( isset($act[$k]) )
    switch ( key($act[$k]) ) {
    case 'file':
      $from_file= str_replace('.php','',$act[$k]['file']);
      $from.= " < ".substr(strrchr($from_file,'\\'),1);
      $from.= "/{$act[$k]['line']}";
      break;
    case 'function':
      $from.= " < ".($act[$k]['class']?"{$act[$k]['class']}.":'').$act[$k]['function'];
      break;
    default:
      $from.= " < ? ";
      break;
    }
  }
  */
  return $show_call ? "$fce($args)$from" : $from;
}
# ------------------------------------------------------------------------------------- array2object
function array2object(array $array) {
  $object = new stdClass();
  foreach($array as $key => $value) {
    if(is_array($value)) {
      $object->$key = array2object($value);
    }
    else {
      $object->$key = $value;
    }
  }
  return $object;
}
if ( !defined("EZER_PDO_PORT") ) {
# ------------------------------------------------------------------------------------- ezer_connect
# spojení s databází
# $db = jméno databáze uvedené v konfiguraci aplikace
# $db = .main. pokud má být připojena první databáze z konfigurace
# $initial=1 pokud není ještě aktivní fce_error
function ezer_connect ($db0='.main.',$even=false,$initial=0) {
  global $trace;
//   $trace.= "ezer_connect ($db0)<br>";
  global $ezer_db, $EZER;
  $err= '';
  $db= $db0;
  if ( $db=='.main.' ) {
    foreach ( $ezer_db as $db1=>$desc) {
      $db= $db1;
      break;
    }
  }
  // vlastní připojení, pokud nebylo ustanoveno
  $db_name= (isset($ezer_db[$db][5]) && $ezer_db[$db][5]!='') ? $ezer_db[$db][5] : $db;
  if ( !$ezer_db[$db][0] || $even ) {
    $ezer_db[$db][0]= @mysql_pconnect($ezer_db[$db][1],$ezer_db[$db][2],$ezer_db[$db][3]);
    if ( !$ezer_db[$db][0] ) {
      fce_error("db=$db|connect: server '{$ezer_db[$db][1]}' s databazi '"
        . ($ezer_db[$db][5] ? "$db/$db_name" : $db)."' neni pristupny:").mysql_error();
    }
  }
  $res= @mysql_select_db($db_name,$ezer_db[$db][0]);
  if ( !$res ) {
    $ok= 0;
    $err= "databaze '$db_name' je nepristupna";
    if ( !$initial ) fce_error("connect: $err".mysql_error());
    else die("connect: $err".mysql_error());
  }
  if ( $ezer_db[$db][4] ) {
    pdo_query("SET NAMES '{$ezer_db[$db][4]}'");
  }
  return $err;
}
}
# ------------------------------------------------------------------------------------- getmicrotime
function getmicrotime() {
//   list($usec, $sec) = explode(" ", microtime());
//   return ((float)$usec + (float)$sec);
  return round(microtime(true)*1000);
}
# ---------------------------------------------------------------------------------------- mysql_err
# ošetření chyby a doplnění $y->error, $y->ok
function mysql_err($qry) {
  global $y;
  $msg= '';
  $merr= mysql_error();
  $serr= "You have an error in your SQL";
  if ( $merr && substr($merr,0,strlen($serr))==$serr ) {
    $msg.= "SQL error ".substr($merr,strlen($serr))." in:$qry";
  }
  else {
    $myerr= $err ? $err : $merr;
    $myerr= str_replace('"',"U",$myerr);
    $msg.= win2utf("\"$myerr\" ")."\nQRY:$qry";
  }
  $y->ok= 'ko';
  fce_error($msg);
}
# ------------------------------------------------------------------------------------------- select
# navrácení hodnoty jednoduchého dotazu
# pokud $expr obsahuje čárku, vrací pole hodnot, pokud $expr je hvězdička vrací objekt
# příklad 1: $id= select("id","tab","x=13")
# příklad 2: list($id,$x)= select("id,x","tab","x=13")
function select($expr,$table,$cond=1,$db='.main.') {
  if ( strstr($expr,",") ) {
    $result= array();
    $qry= "SELECT $expr FROM $table WHERE $cond";
    $res= pdo_qry($qry,0,0,0,$db);
    if ( !$res ) { fce_error("chyba funkce select:$qry/".mysql_error()); goto end; }
    $result= pdo_fetch_row($res);
  }
  elseif ( $expr=='*' ) {
    $qry= "SELECT * FROM $table WHERE $cond";
    $res= pdo_qry($qry,0,0,0,$db);
    if ( !$res ) fce_error(wu("chyba funkce select:$qry/".mysql_error()));
    $result= pdo_fetch_object($res);
  }
  else {
    $result= '';
    $qry= "SELECT $expr AS _result_ FROM $table WHERE $cond";
    $res= pdo_qry($qry,0,0,0,$db);
    if ( !$res ) fce_error(wu("chyba funkce select:$qry/".mysql_error()));
    $o= pdo_fetch_object($res);
    $result= $o->_result_;
  }
//                                                 debug($result,"select");
end:
  return $result;
}
# ------------------------------------------------------------------------------------------ select1
# navrácení hodnoty jednoduchého dotazu - $expr musí vracet jednu hodnotu
function select1($expr,$table,$cond=1,$db='.main.') {
  $result= '';
  $qry= "SELECT $expr AS _result_ FROM $table WHERE $cond";
  $res= pdo_qry($qry,0,0,0,$db);
  if ( !$res ) fce_error(wu("chyba funkce select1:$qry/".mysql_error()));
  $o= pdo_fetch_object($res);
  $result= $o->_result_;
  return $result;
}
# ------------------------------------------------------------------------------------ select_object
# navrácení hodnot jednoduchého jednoznačného dotazu jako objektu (funkcí pdo_fetch_object)
function select_object($expr,$table,$cond=1,$db='.main.') {
  $qry= "SELECT $expr FROM $table WHERE $cond";
  $res= pdo_qry($qry,0,0,0,$db);
  if ( !$res ) fce_error(wu("chyba funkce select_object:$qry/".mysql_error()));
  $result= pdo_fetch_object($res);
  return $result;
}
# -------------------------------------------------------------------------------------- query track
# provede některá SQL včetně zápisu do _track
#   INSERT INTO tab (f1,f2,...) VALUES (v1,v2,...) 
#   UPDATE tab SET f1=v1, f2=v2, ... WHERE id_tab=v0
# kde vi jsou jednoduché hodnoty: číslo nebo string uzavřený v apostorfech 
# trasovaná tabulka musí být uvedena v $mysql_tracked, jeji klíč musí být buďto ve tvaru id_tab
# nebo být uveden v $mysql_tracked_id jako tab=>id
function query_track($qry,$db='.main.') {
  global $mysql_db_track, $mysql_tracked, $mysql_tracked_id;
  // rozklad výrazu: 1:table, 2:field list, 3:values list
  $res= 0;
  $m= null;
  $ok= preg_match('/(INSERT)\s+INTO\s+([\w\.]+)\s+\(([,\s\w]+)\)\s+VALUE(?:S|)\s+\(((?:.|\s)+)\)$/',$qry,$m)
    || preg_match('/(UPDATE)\s+([\w\.]+)\s+SET\s+(.*)\s+WHERE\s+([\w]+)\s*=\s*(.*)\s*/m',$qry,$m)
    || preg_match('/(DELETE)\s+FROM\s+([\w\.]+)\s+WHERE\s+([\w]+)\s*=\s*(.*)\s*/m',$qry,$m)
  ;
//  debug($m);
  $fce= $m[1] ?: '';
  $tab= $m[2] ?: '';
  if ( $mysql_db_track && strpos($mysql_tracked,",$tab,")!==false ) {
    global $USER;
    $abbr= isset($USER->abbr) ? $USER->abbr : 'WEB';
    if ($ok && $fce=='INSERT') {
      $fld= explode_csv($m[3]); 
      $val= explode_csv($m[4]); 
      $res= query($qry,$db);
      $key_val= pdo_insert_id();
      for ($i= 0; $i<count($fld); $i++) {
        $f= $fld[$i];
        $v= $val[$i];
//        if ($v[0]=="'") $v= substr($v,1,-1);
//        $v= pdo_real_escape_string($v);
        pdo_query("INSERT INTO _track (kdy,kdo,kde,klic,op,fld,val) "
            . "VALUE (NOW(),'$abbr','$tab',$key_val,'i','$f',$v)",null,null,false,$db);
      }
    }
    elseif ($ok && $fce=='UPDATE') {
  //    debug($m);
      $sets= explode_csv($m[3]); 
      $key_id= $m[4];
      $key_val= $m[5];
      // kontrola podmínky
      $ok= $key_id=="id_$tab" || $key_id==$mysql_tracked_id[$tab];
      if ($ok) {
        foreach ($sets as $set) {
          list($fld,$val)= explode('=',$set,2);
          $old= select($fld,$tab,"$key_id=$key_val");
          $old= pdo_real_escape_string($old);
          if ($val[0]=="'") $val= substr($val,1,-1);
          $val= pdo_real_escape_string($val);
          pdo_query("INSERT INTO _track (kdy,kdo,kde,klic,op,fld,old,val) "
            . "VALUE (NOW(),'$abbr','$tab',$key_val,'u','$fld','$old','$val')",null,null,false,$db);
        }
        $res= query($qry,$db);
      }
    }
    elseif ($ok && $fce=='DELETE') {
      $key_id= $m[3];
      $key_val= $m[4];
      // kontrola podmínky
      $ok= $key_id=="id_$tab" || $key_id==$mysql_tracked_id[$tab];
      if ($ok) {
        pdo_query("INSERT INTO _track (kdy,kdo,kde,klic,op) "
            . "VALUE (NOW(),'$abbr','$tab',$key_val,'x')",null,null,false,$db);
        $res= query("DELETE FROM $tab WHERE $key_id=$key_val",$db);
      }
    }
    else {
      $ok= 0;
    }
    if (!$ok) {
      fce_error("funkce query-track nemá předepsaný tvar argumentu ale $qry");
    }
  }
  else {
    $res= query($qry,$db);
  }
end:
  return $res;
}
# -------------------------------------------------------------------------------------- explode csv
# split CSV s ohledem na závorky a apostrofy
function explode_csv($str, $separator=",", $leftbracket="(", $rightbracket=")", $quote="'", $ignore_escaped_quotes=true ) {
  $buffer = '';
  $stack = array();
  $depth = 0;
  $char= '';
  $betweenquotes = false;
  $len = strlen($str);
  for ($i=0; $i<$len; $i++) {
    $previouschar = $char;
    $char = $str[$i];
    switch ($char) {
      case $separator:
        if (!$betweenquotes) {
          if (!$depth) {
            if ($buffer !== '') {
              $stack[] = $buffer;
              $buffer = '';
            }
            continue 2;
          }
        }
        break;
      case $quote:
        if ($ignore_escaped_quotes) {
          if ($previouschar!="\\") {
            $betweenquotes = !$betweenquotes;
          }
        } else {
          $betweenquotes = !$betweenquotes;
        }
        break;
      case $leftbracket:
        if (!$betweenquotes) {
          $depth++;
        }
        break;
      case $rightbracket:
        if (!$betweenquotes) {
          if ($depth) {
            $depth--;
          } else {
            $stack[] = $buffer.$char;
            $buffer = '';
            continue 2;
          }
        }
        break;
      }
      $buffer .= $char;
  }
  if ($buffer !== '') {
    $stack[] = $buffer;
  }
  return $stack;
}
# -------------------------------------------------------------------------------------------- query
# provedení MySQL dotazu
function query($qry,$db='.main.') {
  $res= pdo_qry($qry,0,0,0,$db);
  //if ( !$res ) fce_error(wu("chyba funkce query:$qry/".mysql_error()));
  return $res;
}
# ---------------------------------------------------------------------------------------- sql_query
# provedení MySQL dotazu
function sql_query($qry,$db='.main.') {
  $obj= (object)array();
  $res= pdo_qry($qry,0,0,0,$db);
  if ( $res ) {
    $obj= pdo_fetch_object($res);
  }
  return $obj;
}
# ---------------------------------------------------------------------------------------- mysql row
# provedení dotazu v $y->qry="..." a vrácení pdo_fetch_assoc (případně doplnění $y->err)
function mysql_row($qry,$err=null) {
  $res= mysql_qry($qry,1);
  $row= $res ? pdo_fetch_assoc($res) : array();
  if ( !$res ) mysql_err($qry);
  return $row;
}
# ---------------------------------------------------------------------------------------- sql_date1
// datum bez dne v týdnu
function sql_date1 ($datum,$user2sql=0,$del='.') {
  if ( $user2sql ) {
    // převeď uživatelskou podobu na sql tvar
    $text= '';
    if ( $datum ) {
      $datum= str_replace(' ','',$datum);
      list($d,$m,$y)= explode('.',$datum);
      $text= $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
    }
  }
  else {
    // převeď sql tvar na uživatelskou podobu (default)
    $text= '';
    if ( $datum && substr($datum,0,10)!='0000-00-00' ) {
      $y=substr($datum,0,4);
      $m=substr($datum,5,2);
      $d=substr($datum,8,2);
      //$h=substr($datum,11,2);
      //$n=substr($datum,14,2);

      $text.= date("j{$del}n{$del}Y",strtotime($datum));
//      $text.= "$d.$m.$y";
//                                                 display("$datum:$text");
    }
  }
  return $text;
}
# ----------------------------------------------------------------------------------------- sql_date
// datum
function sql_date ($datum,$user2sql=0) {
  if ( $user2sql ) {
    // převeď uživatelskou podobu na sql tvar
    $text= '';
    if ( $datum ) {
      $datum= trim($datum);
      list($d,$m,$y)= explode('.',$datum);
      $text= $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
    }
  }
  else {
    // převeď sql tvar na uživatelskou podobu (default)
    $dny= array('ne','po','út','st','čt','pá','so');
    $text= '';
    if ( $datum && substr($datum,0,10)!='0000-00-00' ) {
      $y= 0+substr($datum,0,4);
      $m= 0+substr($datum,5,2);
      $d= 0+substr($datum,8,2);
      //$h=substr($datum,11,2);
      //$n=substr($datum,14,2);
      $t= mktime(0,0,1,$m,$d,$y)+1;
//                                                 display("$datum:$m,$d,$y:$text:$t");
      $text= $dny[date('w',$t)];
      $text.= " $d.$m.$y";
    }
  }
  return $text;
}
# ----------------------------------------------------------------------------------------- ezer_qry
# záznam změn do tabulky _track
# 1. ezer_qry("INSERT",$table,$x->key,$zmeny[,$key_id]);       -- vložení 1 záznamu
# 2. ezer_qry("UPDATE",$table,$x->key,$zmeny[,$key_id]);       -- oprava 1 záznamu
#     zmeny= [ zmena,...]
#     zmena= { fld:field, op:a|p|d|c, val:value, row:n }          -- pro chat
#          | { fld:field, op:u,   val:value, [old:value] }        -- pro opravu
#          | { fld:field, op:i,   val:value }                     -- pro vytvoření
# 3. ezer_qry("UPDATE_keys",$table,$keys,$zmeny[,$key_id]);    -- hromadná oprava pro key IN ($keys)
#     zmeny= { fld:field, op:m|p|a, val:value}                    -- SET fld=value
//function user_test() {
//  global $mysql_db_track, $USER;
//  if ( $mysql_db_track && !$USER->abbr ) {
//    fce_error("Vaše přihlášení již vypršelo - přihlaste se prosím znovu a operaci opakujte");
//  }
//}
function ezer_qry ($op,$table,$cond_key,$zmeny,$key_id='') {
  global $json, $mysql_db, $mysql_db_track, $mysql_tracked, $USER;
//                                                         debug($zmeny,"qry_update($op,$table,$cond_key)");
  $result= 0;
  $tracked= array();
  $keys= '???';                 // seznam klíčů
  $tab= str_replace("$mysql_db.",'',$table);
  if ( !$key_id ) $key_id= $tab=='pdenik' ? 'id_pokl' : str_replace('__','_',"id_$tab");
  $user= isset($USER->abbr) ? $USER->abbr : 'WEB';
//  user_test();
  // zpracování parametrů -- jen pro UPDATE
  switch ( $op ) {
  case 'INSERT':
    // vytvoření INTO a VALUES
    $flds= ''; $vals= ''; $del= '';
    $tracked[0]= array();
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      if ( $fld!='zmena_kdo' && $fld!='zmena_kdy' ) $tracked[0][]= $zmena;
      if ( $fld=='id_cis' ) $id_cis= $zmena->val;
      $val= pdo_real_escape_string($zmena->val);
      $flds.= "$del$fld";
      $vals.= "$del'$val'";
      $del= ',';
    }
    // provedení INSERT
    $key_val= 0;
    $qry= "INSERT INTO $table ($flds) VALUES ($vals)";
    $res= pdo_qry($qry);
    $result= $tab=="_cis" ?  $id_cis : pdo_insert_id();
    $keys= $result;
    break;
  case 'UPDATE':
    // vytvoření SET a doplnění WHERE
    $set= ''; $and= ''; $del= '';
    $tracked[0]= array();
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      if ( $fld!='zmena_kdo' && $fld!='zmena_kdy' ) $tracked[0][]= $zmena;
      $val= pdo_real_escape_string($zmena->val);
      switch ( $zmena->op ) {
      case 'a':
        $set.= "$del$fld=concat($fld,'$val')";
        break;
      case 'p':
        $set.= "$del$fld=concat('$val',$fld)";
        break;
      case 'd': // delete záznam row v chat
        $va= explode('|',$zmena->old);
        $old= pdo_real_escape_string($zmena->old);
        $zmena->old_val= "{$va[2*$zmena->row-2]}|{$va[2*$zmena->row-1]}";
        unset($va[2*$zmena->row-2],$va[2*$zmena->row-1]);
        $vn= pdo_real_escape_string(implode('|',$va));
        $set.= "$del$fld='$vn'";
        $and.= " AND $fld='$old'";
        break;
      case 'c': // change záznam row v chat
        $old= pdo_real_escape_string($zmena->old);
        $va= explode('|',$old);
        $zmena->old_val= "{$va[2*$zmena->row-2]}|{$va[2*$zmena->row-1]}";
        $va[2*$zmena->row-1]= $val;
        $vn= implode('|',$va);
        $set.= "$del$fld='$vn'";
        $and.= " AND $fld='$old'";
        break;
      case 'u':
      case 'U': // určeno pro hromadné změny
        $set.= "$del$fld='$val'";
        if ( isset($zmena->old) ) {
          $old= pdo_real_escape_string($zmena->old);
          $and.= " AND $fld='$old'";
        }
        break;
      case 'i':
        $set.= "$del$fld='$val'";
        break;
      }
      $del= ',';
    }
    // provedení UPDATE pro jeden záznam s kontrolou starých hodnot položek
    $key_val= $cond_key;
    $qry= "SELECT $key_id FROM $table WHERE $key_id=$key_val $and ";
    if ( pdo_qry($qry,1) )  {
      $qry= "UPDATE $table SET $set WHERE $key_id=$key_val $and ";
      pdo_qry($qry);
      $result= 1;
    }
    $keys= $key_val;
    break;
  case 'UPDATE_keys':
//                                                         debug($zmeny,"qry_update($op,$table,$cond_key)");
    $akeys= explode(',',$cond_key);
    sort($akeys);
    foreach ($akeys as $i => $key) {
      $tracked[$i][0]= $zmeny;
      $tracked[$i][0]->key= $key;
    }
    $keys= implode(',',$akeys);
    $fld= $zmeny->fld;
    $val= pdo_real_escape_string($zmeny->val);
    switch ( $zmeny->op ) {
    case 'm':
      // zjištění starých hodnot podle seznamu klíčů
      $qry= "SELECT GROUP_CONCAT($fld SEPARATOR '|') as $fld FROM $table WHERE $key_id IN ($keys)";
      $res= pdo_qry($qry);
      if ( $res ) {
        $row= pdo_fetch_assoc($res);
        foreach (explode('|',$row[$fld]) as $i => $old) {
          $tracked[$i][0]->old= $old;
        }
      }
      $qry= "UPDATE $table SET $fld='$val' WHERE $key_id IN ($keys)";
      break;
    case 'a':
    case 'p':
      $concat= $zmeny->op=='a' ? "concat($fld,'$val')" : "concat('$val',$fld)";
      $qry= "UPDATE $table SET $fld=$concat WHERE $key_id IN ($keys)";
      break;
    case 'd':
    case 'c':
      fce_error("ezer_qry: hromadná operace {$zmeny->op} neimplementována");
      break;
    }
    // provedení UPDATE pro záznamy podle seznamu klíčů
//                                                         display($qry);
    pdo_qry($qry);
    break;
  default:
    fce_error("ezer_qry: operace $op neimplementována");
  }
  // zápis změn do _track
  if (strpos($table,".")!==false) {
    $table= explode('.',$table);
    $table= $table[count($table)-1];
  }
  if ( $mysql_db_track && count($tracked)>0 && strpos($mysql_tracked,",$table,")!==false ) {
    $qry= "";
    $now= date("Y-m-d H:i:s");
    $del= '';
    foreach (explode(',',$keys) as $i => $key) {
      $qry_prefix= "INSERT INTO _track (kdy,kdo,kde,klic,fld,op,old,val) VALUES ('$now','$user','$tab',$key";
      foreach ($tracked[$i] as $zmena) {
        $fld= $zmena->fld;
        $op= $zmena->op;
        switch ($op) {
        case 'd':
          $val= '';
          $old= pdo_real_escape_string($zmena->old_val);
          break;
        case 'c':
          $val= pdo_real_escape_string($zmena->val);
          $old= pdo_real_escape_string($zmena->old_val);
          break;
        default:
          // zmena->pip je definovaná ve form_save v případech zápisu hodnoty přes sql_pipe
          $val= pdo_real_escape_string($zmena->val);
          $old= $zmena->old ? pdo_real_escape_string($zmena->old) : (
                $zmena->pip ? pdo_real_escape_string($zmena->pip) : '');
          break;
        }
        $qry= "$qry_prefix,'$fld','$op','$old','$val'); ";
        $res= pdo_qry($qry);
//                                                 display("TRACK: $qry");
      }
    }
  }
end:
  return $result;
}
# ------------------------------------------------------------------------------------- emailIsValid
# tells you if an email is in the correct form or not
# emailIsValid - http://www.kirupa.com/forum/showthread.php?t=323018
# args:  string - proposed email address
# ret:   bool
function emailIsValid($email,&$reason) {
   $isValid= true;
   $reasons= array();
   $atIndex= strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)    {
      $isValid= false;
      $reasons[]= "chybí @";
   }
   else    {
      $domain= substr($email, $atIndex+1);
      $local= substr($email, 0, $atIndex);
      $localLen= strlen($local);
      $domainLen= strlen($domain);
      if ($localLen < 1 || $localLen > 64)       {
         $isValid= false;
         $reasons[]= "dlouhé jméno";
      }
      else if ($domainLen < 1 || $domainLen > 255)       {
         $isValid= false;
         $reasons[]= "dlouhá doména";
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')       {
         $reasons[]= "tečka na kraji";
         $isValid= false;
      }
      else if (preg_match('/\\.\\./', $local))  {
         $reasons[]= "dvě tečky ve jménu";
         $isValid= false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))   {
         $reasons[]= "chybný znak v doméně";
         $isValid= false;
      }
      else if (preg_match('/\\.\\./', $domain))  {
         $reasons[]= "dvě tečky v doméně";
         $isValid= false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))   {
         $reasons[]= "chybný znak ve jménu";
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))            {
            $isValid= false;
         }
      }
      if ( $domain!='proglas.cz' && $domain!='setkani.org' ) {
        if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))      {
           $reasons[]= "$domain je neznámá doména";
           $isValid= false;
        }
      }
   }
   $reason= count($reasons) ? implode(', ',$reasons) : '';
   return $isValid;
}
# ---------------------------------------------------------------------------------------- mail send
# OAuth2
/**
 * @param $reply_to - address to reply to
 * @param $recipient_address - string sender address
 * @param $subject - mail subject
 * @param $body - mail body
 * @param $gmail_sender_name - name as seen by recipient
 * @param $gmail_sender_mail - email used to send the mail, must be authenticated to using GMAIL OAuth
 * @param $cc - mail to forward the email to or empty string if do not forward
 * @param $cc_name - name of the first forward recipient
 * @param $cc2 - second mail to forward to
 * @param $cc2_name - second forward recipient
 * @return string|null - error description or null in case of success
 */
function send_mail($reply_to, $recipient_address, $subject, $body, $gmail_sender_name, $gmail_sender_mail,
                   $cc='', $cc_name='', $cc2='', $cc2_name='') {
  //GMAIL API CONSTANTS
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

  $filePath = $tokenPathPrefix . $gmail_sender_mail . $tokenPathSuffix;
  if (!is_file($filePath) || !is_readable($filePath)) {
    return "Email nebylo možné odeslat. Za potíže se omlouváme.";
  }

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
        return "Email nebylo možné jej odeslat. Prosíme, kontaktujte administrátora a ohlaste mu nefunkční email. Děkujeme za pochopení.";
      }
    }

    $message = new Google_Service_Gmail_Message();
    prepare_message_with_mailer($message, $reply_to, $recipient_address, $subject, $body, $gmail_sender_name,
        $gmail_sender_mail, $cc, $cc_name, $cc2, $cc2_name);

    $service = new Google_Service_Gmail($client);
    try {
      $service->users_messages->send('me', $message);
      return null;
    } catch (Exception $e) {
      file_put_contents("email-logs.txt", $e, FILE_APPEND);
      return "Něco se pokazilo. Děkujeme za pochopení.";
    }
  } catch (Exception $e) {
    file_put_contents("email-logs.txt", $e, FILE_APPEND);
    return "Něco se pokazilo. Děkujeme za pochopení.";
  }
}

// $gmail_message:: Google_Service_Gmail_Message instance
function prepare_message_with_mailer($gmail_message, $reply_to, $recipient_address, $subject, $body,
                                     $gmail_sender_name, $gmail_sender_mail, $cc, $cc_name, $cc2, $cc2_name) {
  $phpmailer_path = $_SERVER['DOCUMENT_ROOT']."/ezer3.1/server/licensed/phpmailer";
  require_once("$phpmailer_path/class.phpmailer.php");
  require_once("$phpmailer_path/class.smtp.php");

  $mail= new PHPMailer(true);
  $mail->SetLanguage('cs',"$phpmailer_path/language/");
  $mail->CharSet= "UTF-8";
  $mail->IsHTML(true);
  $mail->ClearReplyTos();
  $mail->AddReplyTo($reply_to);
  $mail->SetFrom($gmail_sender_mail, $gmail_sender_name);
  $mail->Subject= $subject;
  $mail->Body= $body;
  $mail->ClearAttachments();
  $mail->ClearAddresses();
  $mail->ClearCCs();
  $mail->AddAddress($recipient_address);
  if ($cc != '') $mail->AddCC($cc, $cc_name);
  if ($cc2 != '') $mail->AddCC($cc2, $cc2_name);

  //$ok= $mail->Send();    don't send, but pre-send and get data
  if ($mail->preSend()) {
    $mime = $mail->getSentMIMEMessage();
    $data = base64_encode($mime);
    $data = str_replace(array('+','/','='),array('-','_',''),$data); // url safe
    $gmail_message->setRaw($data);
  } else {
    throw new Exception("Unable to create mail with PHPMailer: " . $mail->ErrorInfo);
  }
}

// TOOD DELETE old mailer for chlapi:
function mail_send($reply_to,$address,$subject,$body,$gmail_name) {
  global $chlapi_gmail_user, $chlapi_gmail_pass;
  $gmail_user = $chlapi_gmail_user;
  $gmail_pass = $chlapi_gmail_pass;

  $ret= (object)array('err'=>0,'msg'=>'N.Y.I');
// goto end;
//   $address= "martin@smidek.eu";
//   $subject= "test";
//   $body= "TEST";
  $TEST= 0;
  $ezer_path_serv= "ezer3.1/server";
  $phpmailer_path= "$ezer_path_serv/licensed/phpmailer";
  require_once("$phpmailer_path/class.phpmailer.php");
  require_once("$phpmailer_path/class.smtp.php");
  $n= $nko= 0;
  // nastavení phpMail
  $mail= new PHPMailer(true);
  $mail->SetLanguage('cs',"$phpmailer_path/language/");
  $mail->IsSMTP();
  $mail->SMTPAuth = true; // enable SMTP authentication
  $mail->SMTPSecure= "ssl"; // sets the prefix to the server
  $mail->Host= "smtp.gmail.com"; // sets GMAIL as the SMTP server
  $mail->Port= 465; // set the SMTP port for the GMAIL server
  $mail->Username= $gmail_user;
  $mail->Password= $gmail_pass;
  $mail->CharSet= "UTF-8";
  $mail->IsHTML(true);
  // zpětné adresy
  $mail->ClearReplyTos();
  $mail->AddReplyTo($reply_to);
  $mail->SetFrom($gmail_user, $gmail_name);
  // vygenerování mailu
  $mail->Subject= $subject;
  $mail->Body= $body;
  // přidání příloh
  $mail->ClearAttachments();
  // přidání adres
  $mail->ClearAddresses();
  $mail->ClearCCs();
  $mail->AddAddress($address);

  if ( $TEST ) {
    $ret->msg= "TESTOVÁNÍ - vlastní mail.send je vypnuto";
    goto end;
  }
  else {
    // odeslání mailu
    try {
      $ok= $mail->Send();
      $ret->msg= $ok ? '' : $mail->ErrorInfo;
      $ret->err= $ok ? 0 : 1;
    } catch ( Exception $exc ) {
      $ret->msg= $mail->ErrorInfo;
      $ret->err= 2;
    }
  }
  end:
  return $ret;
}


// </editor-fold>
$PIN_alive = 24;              /** povolená doba života vydaného PIN v hodinách */
// <editor-fold defaultstate="collapsed" desc="++++++++++++++++++++++++++ CMS functions">
# ----------------------------------------------------------------------------------------- cms full
# zjištění jestli je akce již plná
function cms_full($id_akce) {
  list($nahradnik,$max)= select('web_obsazeno,web_maximum','akce',"id_duakce=$id_akce",'ezer_db2');
  if ( !$nahradnik ) {
    $ucastniku= select("COUNT(*)","pobyt","id_akce=$id_akce AND funkce IN (0,1,2)",'ezer_db2');
    $nahradnik= $ucastniku >= $max;
  }
  return $nahradnik;
}
# ----------------------------------------------------------------------------------- cms family_get
# vrátí seznam členů kmenové rodiny osoby jako pole [{name:jméno,rok_narozené,id:id_osoba},...]
# nebo null pokud v žádné rodině není
function cms_family_get($id_osoba,$ida) {
  $cleni= array();
  // zjisti pobyt
  $idp= select('id_pobyt',"pobyt JOIN spolu USING (id_pobyt)",
      "id_akce=$ida AND id_osoba=$id_osoba",'ezer_db2');
  // zjisti kmenovou rodinu nebo je-li osoba singl
  $k= sql_query("
    SELECT IFNULL(SUBSTR(
      (SELECT MIN(CONCAT(role,id_rodina))
        FROM tvori AS ot JOIN rodina AS r USING (id_rodina) WHERE ot.id_osoba=o.id_osoba
      ),2),0) AS id_rodina
    FROM osoba AS o
    WHERE o.id_osoba='$id_osoba'
    GROUP BY o.id_osoba",'ezer_db2');
  // zjisti seznam členů rodiny
  ezer_connect('ezer_db2');
  $qp= pdo_qry("
    SELECT jmeno,YEAR(narozeni),id_osoba
    FROM osoba AS o
    JOIN tvori AS t USING (id_osoba)
    WHERE o.deleted='' AND id_rodina=$k->id_rodina AND id_osoba!=$id_osoba AND role IN ('a','b','d')
    ORDER BY role,narozeni ");
  while ( $qp && (list($jmeno,$rok,$ido)= pdo_fetch_array($qp)) ) {
    $ids= select('COUNT(*)','spolu',"id_pobyt=$idp AND id_osoba=$ido",'ezer_db2');
    $cleni[]= (object)array('name'=>"$jmeno, $rok",'id'=>$ido,'spolu'=>$ids);
  }
  return count($cleni) ? $cleni : null;
}
# ----------------------------------------------------------------------------------- cms family_set
# projde seznam členů rodiny [{id:id_osoba,ucast:0|1},...]
# a přidá nebo odebere je na akci
function cms_family_set($id_osoba,$ida,$cleni) {
  $ok= 1;
  // přeformátuj pole
  $spolu= array();
  foreach ($cleni as $clen) {
    $spolu[$clen->id]= $clen->spolu;
  }
  // zjisti pobyt
  $idp= select('id_pobyt',"pobyt JOIN spolu USING (id_pobyt)",
      "id_akce=$ida AND id_osoba=$id_osoba",'ezer_db2');
  // projdi členy jsou-li
  $dnes= date('Y-m-d');
  if ( count($cleni) ) {
    foreach ($cleni as $clen) {
      ezer_connect('ezer_db2');
      $ids= select1('IFNULL(id_spolu,0)','spolu',"id_pobyt=$idp AND id_osoba=$clen->id",'ezer_db2');
      if ( $ids && !$spolu[$clen->id]) {
        $ok&= query("DELETE FROM spolu WHERE id_spolu=$ids",'ezer_db2');
        $ok&= query("UPDATE pobyt SET web_zmena='$dnes' WHERE id_pobyt=$idp",'ezer_db2');
      }
      elseif ( !$ids && $spolu[$clen->id]) {
        $s= array();
        $s[]= (object)array('fld'=>'id_pobyt','op'=>'i','val'=>$idp);
        $s[]= (object)array('fld'=>'id_osoba','op'=>'i','val'=>$clen->id);
        $s[]= (object)array('fld'=>'s_role','op'=>'i','val'=>1);                      // 1=účastník
        $s[]= (object)array('fld'=>'web_zmena','op'=>'i','val'=>$dnes);    
        $ok&= ezer_qry('INSERT','spolu',0,$s);
        $ok&= query("UPDATE pobyt SET web_zmena='$dnes' WHERE id_pobyt=$idp",'ezer_db2');
      }
    }
  }
  return $ok;
}
# -------------------------------------------------------------------------------------- cms confirm
# obsluha polí osoby, týkajících se souhlasu se zpracováním osobních dat
# zapíše běžné datum do položky web_souhlas a upraví access osoby
function cms_confirm($id_osoba,$id_akce) {
  // úprava hodnoty access podle akce
  $access_a= select('access','akce',"id_duakce=$id_akce",'ezer_db2');
  $access_o= select('access','osoba',"id_osoba=$id_osoba",'ezer_db2');
  $access= $access_o | $access_a;
  query("UPDATE osoba SET web_souhlas=NOW(),access=$access WHERE id_osoba=$id_osoba",'ezer_db2');
}
# -------------------------------------------------------------------------------------- cms changes
# upraví položku pobyt.web_changes hodnotou
# 1/2 pro INSERT/UPDATE pobyt a spolu | 4/8 pro INSERT/UPDATE osoba
function cms_changes($id_pobyt,$changes) {
  query("UPDATE pobyt SET web_changes=web_changes|$changes, funkce=0 
    WHERE id_pobyt=$id_pobyt",'ezer_db2');
}
# ------------------------------------------------------------------------------------------ cms qry
# záznam změn do tabulek osoba,rodina,pobyt,spolu,_track
# volání odpovídá 2. formě fce ezer_qry: 2. ezer_qry(UPDATE,osoba,id_osoba,zmeny,'id_osoba')
#            nebo 1. formě fce ezer_qry: 1. ezer_qry(INSERT,pobyt,0,zmeny,'id_pobyt')
#     zmeny= [ zmena,...]
#     zmena= { fld:field, op:u,   val:value, [old:value] }        -- pro opravu
# vrací pro UPDATE 0/1 pro INSERT id_osoba nebo id_pobyt
function cms_qry($op,$table,$id_osoba,$zmeny,$key_id='') {
  $id= 0;
  if ( $op=='UPDATE' && $table=='osoba' && $key_id=='id_osoba' ) { 
    // UPDATE osoba
    $o= $r= array();
    $adresa= $kontakt= $id_rodina= $web_zmena= 0;
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      $val= $zmena->val;
      if     ( $fld=='adresa')     $adresa= $val;
      elseif ( $fld=='kontakt')    $kontakt= $val;
      elseif ( $fld=='id_rodina' ) $id_rodina= $val;
      elseif ( $fld=='web_zmena' ) $web_zmena= $zmena;
    }
    // osoba.adresa=0 znamená, že ulice,psc,obec,stat budou v tabulce rodina
    // osoba.kontakt=0 znamená, že telefon,email budou v tabulce rodina jako telefony,emaily
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      $val= $zmena->val;
      if ( in_array($fld,array('ulice','psc','obec')) ) { 
        if ($adresa) $o[]= $zmena; else $r[]= $zmena;
      }
      elseif ( in_array($fld,array('mail','telefon')) ) {
        if ($kontakt) $o[]= $zmena; else $r[]= $zmena;
      }
      elseif ( in_array($fld,array('jmeno','prijmeni','narozeni')) ) {
        $o[]= $zmena; 
      }
      elseif ( !in_array($fld,array('adresa','kontakt','id_rodina','web_zmena','web_souhlas')) )  
        goto end;
    }
    // přístup k db
    ezer_connect('ezer_db2');
    // zápis změn osoby
    $oko= $okr= 1;
    if ( count($o) ) {
      if ( $web_zmena ) $o[]= $web_zmena;
      $oko= ezer_qry('UPDATE','osoba',$id_osoba,$o);
    }
    // zápis změn rodiny
    if ( count($r) ) {
      if ( !$id_rodina ) goto end;
      if ( $web_zmena ) $r[]= $web_zmena;
      $okr= ezer_qry('UPDATE','rodina',$id_rodina,$r);
    }
    $id= $oko | $okr; // pro UPDATE to není id ale 0/1
  }
  elseif ( $op=='INSERT' && $table=='osoba' && $key_id=='id_osoba' ) {
    // INSERT osoba
    $o= array();
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      $val= $zmena->val;
      if     ( $fld=='id_rodina' )   $zmena= null; // odstranění pomocné hodnoty
      elseif ( $fld=='adresa'    ) { $zmena->val= 1; $o[]= $zmena; }
      elseif ( $fld=='kontakt'   ) { $zmena->val= 1; $o[]= $zmena; }
      else                           $o[]= $zmena; 
    }
    // vložení osoby
    $id= ezer_qry('INSERT','osoba',0,$o);
  }
  elseif ( $op=='INSERT' && $table=='pobyt' && $key_id=='id_pobyt' ) {
    // INSERT pobyt + spolu
    $s= $p= array();
    $id_akce= 0;
    foreach ($zmeny as $zmena) {
      $fld= $zmena->fld;
      $val= $zmena->val;
      if     ( $fld=='id_akce' )   { $p[]= $zmena; $id_akce= $val; }
      elseif ( $fld=='poznamka' )    $p[]= $zmena;
      elseif ( $fld=='id_osoba' )    $s[]= $zmena;
      elseif ( $fld=='web_zmena' ) { $s[]= $zmena; $p[]= $zmena; }
    }
    // zjištění obsazenosti akce
    $nahradnik= cms_full($id_akce);
    // vložení pobyt 
    $p[]= (object)array('fld'=>'funkce','op'=>'i','val'=>$nahradnik ? 9 : 0);  // náhradník?
    $idp= ezer_qry('INSERT','pobyt',0,$p);
    // vložení spolu
    $s[]= (object)array('fld'=>'id_pobyt','op'=>'i','val'=>$idp);
    $s[]= (object)array('fld'=>'s_role','op'=>'i','val'=>1);                      // 1=účastník
    $ok= ezer_qry('INSERT','spolu',0,$s);
    $id= $ok ? $idp : 0;
  }
end:  
  return $id;
}
# ------------------------------------------------------------------------------- cms send_potvrzeni
# pošle potvrzení o přijetí přihlášky
# vrací {ok:0/1, msg:v případě chyby}
function cms_send_potvrzeni($email,$ido,$ida) {
  $ret= (object)array('ok'=>0);
  list($nazev,$cis_garant)= select('nazev,poradatel','ezer_db2.akce',"id_duakce=$ida");
  // je garant v číselníku (kontrola)
  $id_garant= select('ikona','ezer_db2._cis',"druh='akce_garant' AND data='$cis_garant'");
  if ( $id_garant ) {
    list($jmeno,$prijmeni,$telefon,$reply)= 
        select('jmeno,prijmeni,telefon,email','ezer_db2.osoba',"id_osoba=$id_garant");
    // pročištění reply_to
    list($reply)= explode(',',$reply);
    $reply= trim($reply);
  }
  else {
    // záchytná informace
    $jmeno= 'Miloš';
    $prijmeni= 'Vyleťal';
    $telefon= '731 625 615';
    $reply= "ymca@setkani.org";
  }
  $subj= "Potvrzení přijetí přihlášky na $nazev";
  $body= "Dobrý den,<br><br>
    potvrzuji příjem Vaší přihlášky na akci <b>$nazev</b>.<br>
    V týdnu před akcí dostanete <i>Dopis na cestu</i> s doplňujícími informacemi.<br><br>
    S přáním hezkého dne<br>
    $jmeno $prijmeni<br>
    <a href='mailto:$reply'>$reply</a><br>
    $telefon";
//  $reply= "martin.smidek@gmail.com";    // **********************************
//  $email= "martin@smidek.eu";           // **********************************
  $ret= cms_mail_send($email,$subj,$body,$reply);
end:
  return $ret;    
}
# ------------------------------------------------------------------------------------ login by_mail
# CALL
#   podle hodnoty x.web= 
#     setkani.org => přihlášení pomoci položek osoba.pin a osoba.pin_vydan
#     chlapi.cz   => přihlášení pomoci položek osoba.pin_ch a osoba.pin_vydan_ch
# VERIFIKACE
#   pokud $x->verify pouze vrátí základní info o osobě (user,mrop,name) jinak jde o
# PŘIHLÁŠENÍ
#   pomocí $x->mail a $x->pin a $x->cond ... musí se rovnat údajům v setkani.osoba
#   $x->cond='mrop' => testuje se iniciace
#   pokud souhlasí mail a je splněno cond, testuje se pin: 
#     pokud je prošlý nebo jiný nebo nulový bude na $x->mail zaslán nový pro $x->web
#   pokud mail nebo cond nesouhlasí je přihlášení odmítnuto
# RETURN  
#   y.state = verified | err    -- pro verifikaci
#   y.state = ok | wait | err   -- pro přihlášení - wait znamená, že byl poslán mail s pinem
#
function login_by_mail($x, $y) { // přesunuto do mini.php aby bylo společné se servant.php
  global $totrace, $PIN_alive;
  // chybové hodnoty
  $ido = 0;
  $iniciace = $user = $err = '';
  // rozlišení webu - pro chlapi.cz použijeme pro položky postfix _ch
  $_ch= $x->web=="chlapi.cz" ? '_ch' : '';
  // ověření syntaxe adresy a existence domény
  if ( !$x->mail ) {
    $y->state = 'err';
    $y->txt = isset($x->lang) && $x->lang=='en'
      ? "I can't sign you in without a completed email address"
      : "bez vyplněné mailové adresy tě nemohu přihlásit";
    goto end;
  }
  if ( !emailIsValid($x->mail, $err) ) {
    $y->state = 'err';
    $y->txt = isset($x->lang) && $x->lang=='en'
      ? "'$x->mail' does not look like a valid email address"
      : "'$x->mail' nevypadá jako mailová adresa ($err).";
    goto end;
  }
  // ověření adresy a stavu PIN z Answeru
  $totrace = '';
  ezer_connect('ezer_db2');
  list($ido, $jmeno, $prijmeni, $iniciace, $firming, $pin, $web_level, $diff) = 
      select("id_osoba,jmeno,prijmeni,iniciace,firming,pin$_ch,web_level,TIMESTAMPDIFF(HOUR,pin_vydan$_ch,NOW())", 
          "ezer_db2.osoba", 
          "deleted='' AND kontakt=1 AND email RLIKE '(^|[\\\\s,;]+){$x->mail}([\\\\s,;]+|$)'", 'ezer_db2');
//          "deleted='' AND kontakt=1 AND FIND_IN_SET('{$x->mail}',REPLACE(email,'*',''))", 'ezer_db2');
  $user = $ido ? "$jmeno $prijmeni" : '';
  // web_level je zapisováno z ezerscriptu blokem select.map+
  // pro weby je třeba vrátit součet klíčů
  $level= 0;
  if ( $x->web=="setkani.org" && $web_level ) foreach (explode(',',$web_level) as $part) {
    $level+= $part;
  }
  // VERIFIKACE - bylo požadováno pouze ověření adresy?
  if ( isset($x->verify) && $x->verify ) {
    $y->state = $ido ? 'verified' : 'err';
    $y->user = $ido;
    $y->mrop = $iniciace;
    $y->firm= $firming;
    if ( $x->web=="setkani.org" ) $y->level = $level;
    $y->name = "$jmeno $prijmeni";
    goto end;
  }
  // PŘIHLÁŠENÍ
  if ( !$ido ) {
    // hlášení chybného mailu v přihlášení
    query("INSERT INTO _touch (day,time,module,menu,msg)
        VALUES (CURDATE(),CURTIME(),'$x->web','me_login','neznámý email:$x->mail')",'setkani');
    $y->state = 'err';
    $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "didn't you write an address other than '$x->mail' in the application for the last event?"
        : "nenapsal jsi v poslední přihlášce na akci jinou adresu než '$x->mail'?";
    goto end;
  }
  // kontrola oprávněnosti přístupu
  switch ( $x->cond ) {
    case 'web':
    case 'mrop':
      if ( !$iniciace ) {
        $y->state = 'err';
        $y->txt = isset($x->lang) && $x->lang=='en' 
          ? "didn't you write an address other than '$x->mail' in the application for the MROP/EROP?"
          : "adresa '$x->mail' nebyla použita v přihlášce na iniciaci nebo EROP";
        goto end;
      }
      break;
    default:
      $y->state = 'err';
      $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "sorry, there's been an error - please email me at martin@smidek.eu"
        : "omlouvám se, došlo k chybě - napiš mi prosím na martin@smidek.eu";
      goto end;
  }
  // uživatel ok - kontrola PIN
  $y->cond = 1;
  $y->user = $ido;
  $y->mrop = $iniciace;
  $y->firm= $firming;
  if ( $x->web=="setkani.org" ) $y->level = $level;
  $y->name = "$jmeno $prijmeni";
  // diskuse PIN
  if ( !$x->pin && $pin && $diff<=$PIN_alive ) {
    // PIN nebyl uveden a starý byl ještě platný
//    $y->state = 'err';
//    $y->txt = "k přihlášení napiš PIN, který jsem ti poslal na tvoji mailovou adresu";
//    goto end;
  }
  elseif ( !$x->pin ) {
    // PIN ještě nevydán - pošleme 
    $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "to log in, enter the PIN that I will send to your email address"
        : "k přihlášení napiš PIN, který ti pošlu na tvoji mailovou adresu";
  }
  elseif ( $pin==$x->pin && $diff<=$PIN_alive ) {
    // PIN správný a čerstvý - lze přihlásit bez čekání
    $y->state = 'ok'; 
    // prodloužíme platnost PINu
    query("UPDATE osoba SET pin_vydan$_ch=NOW() WHERE id_osoba=$ido", 'ezer_db2');
    goto end;
  }
  elseif ( $pin==$x->pin && $diff>$PIN_alive ) {
    // PIN správný ale starý
    $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "PIN ($PIN_alive hours) has expired, I will send a new one to your email address"
        : "platnost PINu ($PIN_alive hodin) vypršela, pošlu na tvoji mailovou adresu nový";
  } 
  elseif ( $pin!=$x->pin && $diff<=$PIN_alive ) {
    // PIN nesprávný ale byl vydán čerstvý
    $y->state = 'err';
    $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "this is a different PIN than the one sent to you in the last email - try again"
        : "to je jiný PIN, než který ti byl poslán v posledním mailu - zkus to ještě jednou";
    goto end;
  }
  else {
    // PIN nesprávný - pošleme nový
    $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "to log in you need to enter your PIN, which I will now send to your email address"
        : "k přihlášení je nutné zapsat PIN, který ti nyní pošlu na tvoji mailovou adresu";
  }
  // vytvoření nového PIN, zaslání mailem a zápis do osoba
  $pin = rand(1000, 9999);
  // zápis do db s datem
  query("UPDATE osoba SET pin$_ch=$pin,pin_vydan$_ch=NOW() WHERE id_osoba=$ido", 'ezer_db2');
  $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "a PIN has been sent to the email address, enter it in the box next to the address"
        : "na mailovou adresu byl odeslán PIN, zapiš jej do pole vedle adresy";
  // odeslání mailu
  if ( $_ch ) {
    global $api_gmail_user;
    $ret = isset($x->lang) && $x->lang=='en' 
      ? send_mail('martin@smidek.eu', $x->mail, "Login to www.$x->web ($pin)",
        "In the website login dialog, type $pin next to your email address.
        <br>Have a nice browsing experience, your website", "chlapi.cz", $api_gmail_user)
      : send_mail('martin@smidek.eu', $x->mail, "Přihlášení na www.$x->web ($pin)",
        "V přihlašovacím dialogu webové stránky napiš vedle svojí mailové adresy $pin.
        <br>Přeji Ti příjemné prohlížení, Tvůj web", "chlapi.cz", $api_gmail_user);
    if ( $ret != null ) {
      $y->state = 'err';
      $y->txt = isset($x->lang) && $x->lang=='en' 
          ? "Sorry, I couldn't send the mail with PIN ... martin@smidek.eu will be happy to help you ..." 
          : "Lituji, mail s PINem se mi nepovedlo odeslat ($ret)";
      goto end;
    }
  } 
  else { // setkani.org
    global $api_gmail_user;
    $ret = send_mail('martin@smidek.eu', $x->mail, "Rozšíření přístupu na $x->web",
        "V přihlašovacím dialogu webové stránky napiš vedle svojí mailové adresy $pin.
        <br>Přeji Ti příjemné prohlížení, Tvůj web", "YMCA Setkání", $api_gmail_user);
    if ( $ret != null ) {
      $y->state = 'err';
      $y->txt = isset($x->lang) && $x->lang=='en' 
        ? "Sorry, the mail with PIN could not be sent ($ret). Ask for help martin@smidek.eu"
        : "Lituji, mail s PINem se nepovedlo odeslat ($ret)";
      goto end;
    }
  }

  $y->state = 'wait'; // čekáme na zadání PINu z mailu
  $y->cond = 1;
  $y->user = $ido;
  $y->mrop = $iniciace;
  $y->name = "$jmeno $prijmeni";
  // KONEC
end:
  return;
}
// </editor-fold>
