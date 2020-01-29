<?php # (c) 2016 Martin Smidek <martin@smidek.eu>
error_reporting(E_ALL & ~E_NOTICE);
session_start();
if ( isset($_FILES['upload']) ) {
  // nastavení cesty
  if ( isset($_GET['root']) && $_GET['root']=='test' ) {
    $root= 'C:/#';
    $path= 'cms/';
  }
  else {
    $root= $_SESSION['cms']['abs_root'];
    $path= $_SESSION['cms']['cid_path'];
  }
  // případně založení složky
  $dir= rtrim("$root/$path","/");
  $ok= recursive_mkdir($dir,"/");
  if ( !$ok ) { $msg= "nelze vytvořit složku $dir"; goto err; }
  // soubor vložený do CKEditoru
  $xname= $_FILES['upload']['tmp_name'];
  $fname= utf2ascii(urldecode($_FILES['upload']['name']),'.');
  move_uploaded_file($xname,"$root/$path$fname");
//   $url= "http://web.bean:8080/$path$fname";
  $url= "./$path$fname";
  $ret= <<<__EOD
  {
    "uploaded": 1,
    "fileName": "$fname",
    "url": "$url"
  }
__EOD;
  echo $ret;
  exit;
err:
  $ret= <<<__EOD
  {
    "uploaded": 0,
    "error": {
      "message": "$msg"
    }
  }
__EOD;
  echo $ret;
  exit;
}
# -------------------------------------------------------------------------------------------------- utf2ascii
# konverze z UTF-8 do písmen, číslic a podtržítka, konvertují se i html entity
function utf2ascii($val,$allow='') {
  $txt= preg_replace('~&(.)(?:acute|caron);~u', '\1', $val);
  $txt= preg_replace('~&(?:nbsp|amp);~u', '_', $txt);
  $ref= preg_replace("~[^\\pL0-9_$allow]+~u", '_', $txt);
  $ref= trim($ref, "_");
//     setLocale(LC_CTYPE, "cs_CZ.utf-8");                      bohužel nebývá nainstalováno
//     $url= iconv("utf-8", "us-ascii//TRANSLIT", $url);
  $ref= strtr($ref,array('ě'=>'e','š'=>'s','č'=>'c','ř'=>'r','ž'=>'z','ý'=>'y','á'=>'a','í'=>'i',
                         'é'=>'e','ů'=>'u','ú'=>'u','ó'=>'o','ď'=>'d','ť'=>'t','ň'=>'n'));
  $ref= strtr($ref,array('Ě'=>'E','Š'=>'S','Č'=>'C','Ř'=>'R','Ž'=>'Z','Ý'=>'Y','Á'=>'A','Í'=>'I',
                         'É'=>'E','Ů'=>'U','Ú'=>'U','Ó'=>'O','Ď'=>'D','Ť'=>'T','Ň'=>'N'));
  $ref= mb_strtolower($ref);
  $ref= preg_replace("~[^-a-z0-9_$allow]+~", '', $ref);
  return $ref;
}
# ---------------------------------------------------------------------------------- recursive_mkdir
// vytvoří adresář
function recursive_mkdir($path, $sep="\\", $mode = 0777) {
  $dirs= explode($sep, $path);
  $count= count($dirs);
  $path= '';
  for ($i= 0; $i < $count; ++$i) if ( $dirs[$i] ) {
    $path.= strchr($dirs[$i],':') ? $dirs[$i] : $sep . $dirs[$i];
    if (!is_dir($path) && !@mkdir($path, $mode)) {
      return false;
    }
  }
  return true;
}
?>
