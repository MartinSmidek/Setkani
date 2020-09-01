<?php
//todo: remove
//this file contains duplicate code from ezer-cms server side - can be united after some thought into one place

# ----------------------------------------------------------------------------------- ds cena_pobytu
# ASK
# vypočítá cenu pobytu účastníka (1), rodiny (2), akce (3)
# $id_osoba je z tabulky ds_osoba obsahující osobo-dny
function ds_cena_pobytu($idos,$cenik_roku) {
    $y= (object)array('fields'=>(object)array(),'rows'=>array());
    // číselníky
    $ds_luzko=  map_cis('ds_luzko','zkratka');  $ds_luzko[0]=  '?';
    $ds_strava= map_cis('ds_strava','zkratka'); $ds_strava[0]= '?';
    // přepočet kategorie pokoje na typ ubytování v ceníku
    $luzko_pokoje= ds_cat_typ();
    // společná data
    list($order,$jmeno,$prijmeni,$rodina)=
        select('id_order,jmeno,prijmeni,rodina','ds_osoba',"id_osoba=$idos",'setkani');
    $y->fields->jmeno= wu($jmeno);
    $y->fields->prijmeni= wu($prijmeni);
    $y->fields->rodina= wu($rodina);
    $ob= select_object('*','tx_gnalberice_order',"uid=$order",'setkani');
    $cenik_roku= $cenik_roku?: date('Y',$ob->untilday);
    ds_cenik($cenik_roku);

    // sběr a kontrola dat pro hosta, rodinu, celou objednávku
    foreach (array(1=>"id_osoba=$idos",2=>"rodina='$rodina'",3=>"1") as $i=>$cond) {
        $fields= (object)array();
        $ros= mysql_qry("SELECT * FROM ds_osoba WHERE id_order=$order AND $cond");
        while ( $ros && $os= pdo_fetch_object($ros) ) {
            if ( !$os->pokoj ) { $y->err= "není zapsán pokoj pro $y->prijmeni $y->jmeno "; goto end; }
            $host_pol= ds_polozky_hosta($ob,$os,$luzko_pokoje,$ds_luzko,$ds_strava);
            if ( $i==1 ) {
                $y->host= $host_pol;
            }
            ds_platba_hosta($cenik_roku,$host_pol->cena,$fields,$i);
            foreach ($fields as $field=>$value) {
                $y->fields->$field+= $value;
            }
        }
    }
    end:
    return $y;
}
# -------------------------------------------------------------------------------==> ds platba_hosta
# výpočet ceny za položky hosta jako ubyt,strav,popl,prog,celk
function ds_platba_hosta ($cenik_roku,$polozky,$platba,$i='',$podrobne=false) {
    $druhy= array("ubyt$i"=>'noc|pobyt',"strav$i"=>'strava',"popl$i"=>'ubyt',"prog$i"=>'prog');
    $celki= "celk$i";
    // výpočet
    $one= (object)array();
    $platba->$celki= 0;
    foreach ( $druhy as $druh=>$prefix ) {
        $platba->$druh= 0;
        $rc= mysql_qry("SELECT typ,cena,dph FROM ds_cena WHERE rok=$cenik_roku AND typ RLIKE '$prefix' ");
        while ( $rc && list($typ,$cena,$dph)= pdo_fetch_row($rc) ) {
            $one->$typ+= $cena;
            list($typ_)= explode('_',$typ);
            if ( $polozky->$typ ) {
                $za_noc= in_array($typ_,array('noc','strava','ubyt'));
                $cena= $za_noc ? $cena*$polozky->noci : $cena;
                $platba->$druh+= $cena;
                if ( $podrobne ) {
                    $platba->$typ+= $cena;
                }
            }
        }
        $platba->$celki+= $platba->$druh;
    }
    return $one;
}
# ------------------------------------------------------------------------------------ ds objednavka
# vrátí ID objednávky pokud existuje k této akce
function ds_objednavka($ida) {
    $order= 0;
    list($rok,$kod)= select('g_rok,g_kod','join_akce',"id_akce=$ida",'ezer_db2');
    if ( $kod ) {
        $order= select('uid','tx_gnalberice_order',
            "akce=$kod AND YEAR(FROM_UNIXTIME(fromday))=$rok",'setkani');
        $order= $order ? $order : 0;
    }
    return $order;
}

# přepočet kategorie pokoje na typ ubytování v ceníku
function ds_cat_typ() {
    $cat_typ= array('C'=>'A','B'=>'L','A'=>'S');
    $luzko_pokoje[0]= 0;
    $rr= mysql_qry("SELECT number,category FROM tx_gnalberice_room WHERE version=1");
    while ( $rr && list($pokoj,$typ)= pdo_fetch_row($rr) ) {
        $luzko_pokoje[$pokoj]= $cat_typ[$typ];
    }
    return $luzko_pokoje;
}
# načtení ceníku pro daný rok
function ds_cenik($rok) {  #trace('','win1250');
    global $ds_cena;
    $ds_cena= array();
    $res2= mysql_qry("SELECT * FROM ds_cena WHERE rok=$rok");
    while ( $res2 && $c= pdo_fetch_object($res2) ) {
        $wc= $c;
        $wc->polozka= wu($c->polozka);
        $wc->druh= wu($c->druh);
        $ds_cena[$c->typ]= $wc;
    }
//                                                 debug($cena,'cena',(object)array('win1250'=>1));
}

# výpočet položek hosta
function ds_polozky_hosta ($o,$h,$luzko_pokoje,$ds_luzko,$ds_strava) {
    global $ds_cena;
    // výpočet
    $hf= sql2stamp($h->fromday); $hu= sql2stamp($h->untilday);
    $od_ts= $hf ? $hf : $o->fromday;  $od= date('j.n',$od_ts);
    $do_ts= $hu ? $hu : $o->untilday; $do= date('j.n',$do_ts);
    $vek= ds_vek($h->narozeni,$o->fromday);
    $narozeni= $h->narozeni ? sql_date1($h->narozeni): '';
    $strava= $h->strava ? $h->strava : $o->board;
    // připsání řádku
    $host= array();
    $host[]= wu($h->rodina);
    $host[]= wu($h->jmeno);
    $host[]= wu($h->prijmeni);
    $host[]= wu($h->ulice);
    $host[]= wu("{$h->psc} {$h->obec}");
    $host[]= $narozeni;
    $host[]= $vek;
    $host[]= $h->telefon;
    $host[]= $h->email;
    $host[]= $od;
    $host[]= $do;
    // položky hosta
    $pol= (object)array();
    $pol->test= "{$h->strava} : {$o->board} - $strava = {$ds_strava[$strava]}";
    $noci= round(($do_ts-$od_ts)/(60*60*24));
    $pol->vek= $vek;
    $pol->noci= $noci;
    $pol->pokoj= (int)$h->pokoj;
    // ubytování
    $luzko= trim($ds_luzko[$h->luzko]);     // L|P|B
    if ( $luzko=='L' )
        $luzko= $luzko_pokoje[$h->pokoj];
    if ( $luzko )
        $pol->{"noc_$luzko"}= $noci;
    // strava
    $pol->strava_CC= $ds_strava[$strava]=='C' && $vek>=$ds_cena['strava_CC']->od ? $noci : '';
    $pol->strava_CD= $ds_strava[$strava]=='C' && $vek>=$ds_cena['strava_CD']->od
    && $vek< $ds_cena['strava_CD']->do ? $noci : '';
    $pol->strava_PC= $ds_strava[$strava]=='P' && $vek>=$ds_cena['strava_PC']->od ? $noci : '';
    $pol->strava_PD= $ds_strava[$strava]=='P' && $vek>=$ds_cena['strava_PD']->od
    && $vek< $ds_cena['strava_PD']->do ? $noci : '';
    // pobyt
    if ( $h->postylka ) {
        $pol->pobyt_P= 1;
    }
    // poplatky
    if ( $vek>=18 ) {
        $pol->ubyt_S= $noci;
        if ( !$o->skoleni ) $pol->ubyt_C= $noci;   // rekreační poplatek se neplatí za školení
    }
    else {
        $pol->ubyt_P= $noci;
    }
    // program
    $pol->prog_C= $vek>=$ds_cena['prog_C']->od  ? 1 : 0;
    $pol->prog_P= $vek>=$ds_cena['prog_P']->od && $vek<$ds_cena['prog_P']->do ? 1 : 0;
    return (object)array('host'=>$host,'cena'=>$pol);
}

# zjištění věku v době zahájení akce
function ds_vek($narozeni,$fromday) {
    if ( $narozeni=='0000-00-00' )
        $vek= -1;
    else {
        $vek= $fromday-sql2stamp($narozeni);
        $vek= round($vek/(60*60*24*365.2425),1);
    }
    return $vek;
}