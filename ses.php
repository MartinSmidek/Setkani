<?php

$ezer_root= 'web';

if ( !isset($_SESSION) ) session_start();

# -------------------------------------------------------------------- identifikace ladícího serveru
// definice podporovaných serverů
$ezer_server= 
    $_SERVER["SERVER_NAME"]=='setkani.bean'    ? 0 : (        // 0:lokální 
    $_SERVER["SERVER_NAME"]=='setkani.org'     ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='www.setkani.org' ? 1 : (        // Synology YMCA
    $_SERVER["SERVER_NAME"]=='setkani4.doma'   ? 2 : (        // Synology DOMA
    $_SERVER["SERVER_NAME"]=='setkani4.bean'   ? 3 : (        // 3:lokální VERZE 4 - Jirka
    $_SERVER["SERVER_NAME"]=='setkani4m.bean'  ? 4 : -1))))); // 4:lokální VERZE 4 - Martin
$ezer_local= $ezer_server==0;
$paths_log= array(
  'C:\Apache\logs\php_error.log',       // Win10
  "/var/log/httpd/apache24-error_log",  // YMCA
  "/var/log/httpd/apache24-error_log",  // DOMA
  '',                                   // ?
  'C:\Apache\logs\php_error.log'        // Win10
);
# ----------------------------------------------------------------------------------------------- js
$js= <<<__EOD
function op(op_arg) {
  if ( op_arg=='reload.' )
    location.href= "ses.php";
  else
    location.href= "ses.php?op="+op_arg;
}
__EOD;
# ------------------------------------------------------------------------------------------- server
$log= '';
//  echo("op={$_GET['op']}");
if ( isset($_GET['op']) ) {
  list($op,$arg)= explode('.',$_GET['op']);
  switch ($op) {
  case 'clear':
    $_SESSION[$arg]= array();
    break;
  case 'destroy':
    session_destroy();
    break;
  case 'phpinfo':
    phpinfo();
    break;
  case 'log':
    $log= isset($paths_log[$ezer_server]) ? tailCustom($paths_log[$ezer_server],$arg) : '---';
//    $log= isset($paths_log[$ezer_server]) ? tailShell($paths_log[$ezer_server],$arg) : '---';
    $log= nl2br($log);
    goto render;
  case 'down':
    copy($paths_log[$ezer_server],"docs/error.log");
    goto render;
    break;
  case 'cookie':
    setcookie('error_reporting',$arg);
    break;
  case 'dbg':
    $_SESSION['dbg']= $arg;
    break;
  }
  header('Location: ses.php');
  exit();
}
# ------------------------------------------------------------------------------------------- client
render:
$all= true;
$icon= $ezer_local ? "$ezer_root/img/ses_local.png" : "$ezer_root/img/ses.png";

$cms= debug($_GET,'GET').'<br/>';
$cms.= debug($_POST,'POST').'<br/>';
$cms.= debug($_COOKIE,'COOKIE').'<br/>';
$cms.= debug($_SESSION,'SESSION',(object)array('depth'=>0)).'<br/>';
$dbg= isset($_SESSION['dbg']) ? $_SESSION['dbg'] : '';
$cms.= "
  <div>
    <button style='float:left;' onclick=\"op('dbg.'+document.getElementById('dbg').value);\">save</button>
    <textarea id='dbg' rows='4' cols='60'>$dbg</textarea>
  </div>
";
$cms.= "<div>
         <div style='float:left'>
           <button onclick=\"op('log.10');\">log</button><br>
           <button onclick=\"op('down.');\" title='copy logfile to docs')>vvv</button>
         </div>  
         <div style='width:800px;height:100px;overflow:auto;background:white;margin:5px 40px'>$log</div>
       </div>";
$cms.= debug($_SESSION,'SESSION').'<br/>';
$cms.= debug($_SERVER,'SERVER').'<br/>';

echo <<<__EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="shortcut icon" href="$icon">
  <style>
    body { background:silver; 
      font-family:Arial,Helvetica,sans-serif; padding:0; margin:0; position:static; padding: 5px; }

    button { position:relative; font-size:9pt; white-space:nowrap; z-index:1; padding:1px 4px; }
      @-moz-document url-prefix() { button { padding:0px 4px; } }
      button::-moz-focus-inner { border:0; padding:0; }
    .dbg { margin:0; overflow-y:auto; font-size:8pt; line-height:13px; }
    .dbg table { border-collapse:collapse; margin:1px 0;}
    .dbg td { border:1px solid #aaa; font:x-small Arial;color:#777;padding:1px 3px; line-height:11px; }
    .dbg td.title { color:#000; background-color:#aaa; }
    .dbg td.label { color:#a33;}
    .dbg table.dbg_array { background-color:#ddeeff; }
    .dbg table.dbg_object { background-color:#ffffaa; }
  </style>
  <script type="text/javascript">
    $js
  </script>
  <title>SESSION</title>
  </head>
  <body>
    <div id='cmd'>
      <button onclick="op('reload.');">reload</button>
      <button onclick="op('destroy.');">destroy SESSION</button>
      <button onclick="op('phpinfo.');">phpinfo</button>
      <span style='font-size:12px'>COOKIE error_reporting: </span>
      <button onclick="op('cookie.0');">0</button>
      <button onclick="op('cookie.1');">1</button>
      <button onclick="op('cookie.2');">2</button>
      <button onclick="op('cookie.3');">3</button>
    </div>
      <div class='dbg' style="position:absolute;top:30px">
        $cms
      </div>
  </body>
</html>
__EOD;

# -------------------------------------------------------------------------------------------- debug
# vygeneruje čitelný obraz pole nebo objektu
# pokud jsou data v kódování win1250 je třeba použít  debug($s,'s',(object)array('win1250'=>1));
# options:
#   gettype=1 -- ve třetím sloupci bude gettype(hodnoty)
function debug($gt,$label=false,$options=null) {
  global $trace, $debug_level;
  $debug_level= 0;
  $html= ($options && isset($options->html)) ? $options->html : 0;
  $depth= ($options && isset($options->depth)) ? $options->depth : 64;
  $length= ($options && isset($options->length)) ? $options->length : 64;
  $win1250= ($options && isset($options->win1250)) ? $options->win1250 : 0;
  $gettype= ($options && isset($options->gettype)) ? 1 : 0;
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
    $len= 0;
    foreach($gt as $g => $t) {
      $len++;
      if ( $len>$length ) break;
        $x.= "<tr><td valign='top' class='label'>$g:</td><td>"
        . debugx($t,NULL,$html,$depth,$length,$win1250,$gettype) //TEST==1 ? $t : htmlspecialchars($t)
        .($gettype ? "</td><td>".gettype($t) : '')                      //+typ
        ."</td></tr>";
    }
    $x.= "</table>";
    $debug_level--;
  }
  else {
    if ( is_object($gt) )
      $x= "object:".get_class($gt);
    else
      $x= $html ? htmlspecialchars($gt,ENT_NOQUOTES,'UTF-8') : $gt;
  }
  return $x;
}
# -------------------------------------------------------------------------------------------- my ip
# zjištění klientské IP
function my_ip() {
  return isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
}
# --------------------------------------------------------------------------------------- tailCustom
	/**
	 * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 * @author Torleif Berger, Lorenzo Stanco
	 * @link http://stackoverflow.com/a/15025877/995958
	 * @license http://creativecommons.org/licenses/by/3.0/
	 */
	function tailCustom($filepath, $lines = 1, $adaptive = true) {
		// Open file
		$f = @fopen($filepath, "rb");
		if ($f === false) return "cannot read $filepath";
		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if (!$adaptive) $buffer = 4096;
		else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
		// Jump to last character
		fseek($f, -1, SEEK_END);
		// Read it and adjust line number if necessary
		// (Otherwise the result would be wrong if file doesn't end with a blank line)
		if (fread($f, 1) != "\n") $lines -= 1;
		// Start reading
		$output = '';
		$chunk = '';
		// While we would like more
		while (ftell($f) > 0 && $lines >= 0) {
			// Figure out how far back we should jump
			$seek = min(ftell($f), $buffer);
			// Do the jump (backwards, relative to where we are)
			fseek($f, -$seek, SEEK_CUR);
			// Read a chunk and prepend it to our output
			$output = ($chunk = fread($f, $seek)) . $output;
			// Jump back to where we started reading
			fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
			// Decrease our line counter
			$lines -= substr_count($chunk, "\n");
		}
		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ($lines++ < 0) {
			// Find first newline and remove all text before that
			$output = substr($output, strpos($output, "\n") + 1);
		}
        // Close file and return
		fclose($f);
		return trim($output);
	}
# ---------------------------------------------------------------------------------------- tailShell
	function tailShell($filepath, $lines = 1) {
		ob_start();
		passthru('tail -'  . $lines . ' ' . escapeshellarg($filepath));
		return trim(ob_get_clean());
	}
    ?>
