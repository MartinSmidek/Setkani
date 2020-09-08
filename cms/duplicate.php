<?php
//todo: remove
//this file contains duplicate code from ezer-cms server side - can be united after some thought into one place


# -------------------------------------------------------------------------------==> ds platba_hosta
# výpočet ceny za položky hosta jako ubyt,strav,popl,prog,celk

#ZMĚNĚNO
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
                $cena= $za_noc ? $cena*$polozky->noci*$polozky->$typ : $cena*$polozky->$typ;
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


 // NEW
function ds_order_price_for($days, $adults, $kids15_10, $kids9_3, $kids3_0, $board, $rooms, $free_rooms, $incl_order) {
    global $ds_cena;
    $free_rooms = (array)$free_rooms;
    $incl_order = (array)$incl_order;
    $platba = $polozky = (object)array();
    $platba->error = '';
    $platba->celk = '-';

    $rok= date('Y');
    ds_cenik($rok);
    if ( !count($ds_cena) ) {
        $platba->error = "není dostupný ceník pro rok " . date('Y');
        return $platba;
    }

    $rooms_data = (object)array();
    $rooms_nums = array();
    $res= mysql_qry("SELECT number, beds, addbeds FROM tx_gnalberice_room WHERE NOT deleted AND NOT hidden AND version=1");
    while($row = mysql_fetch_assoc($res)) {
        $rooms_data->room_{$row["number"]} = array("beds" => $row["beds"], "addbeds" => $row["addbeds"]);
        $rooms_nums[]= $row["number"];
    }
    if (strpos("*", $rooms) !== false) $ordered_rooms = $rooms_nums;
    else $ordered_rooms = explode(",", $rooms);

    $polozky->prog_C = 0; //custom orders dont apply
    $polozky->prog_P = 0; //custom orders dont apply
    $polozky->ubyt_S = 0; //training, not used
    $polozky->noc_Z = 0; //animal, we don't know this
    $polozky->pobyt_P = 0; //toddler beds, we don't know this
    $platba->info = "Odhad nezahrnuje: program, domácí mazlíčci, slevy, speciální okolnosti (různé časy odjezdů) ...";

    $polozky->noc_A = 0;
    $polozky->noc_B = $kids3_0;
    $polozky->noc_L = 0;
    $polozky->noc_P = 0;
    $polozky->noc_S = 0;
    $polozky->strava_CC = 0;
    $polozky->strava_CD = 0;
    $polozky->strava_PC = 0;
    $polozky->strava_PD = 0;
    $polozky->ubyt_C = $adults;
    $polozky->ubyt_P = $kids3_0 + $kids9_3 + $kids15_10;
    //number of days
    $polozky->noci = $days;

    //board
    if ($board == 1) { //penze
        $polozky->strava_CC = $adults + $kids15_10;
        $polozky->strava_CD = $kids9_3;
    } else if ($board == 2) { //polopenze
        $polozky->strava_PC = $adults + $kids15_10;
        $polozky->strava_PD = $kids9_3;
    }

    //beds
    $older_ppl = $kids9_3 + $kids15_10 + $adults;
    $places = $left_places = 0;
    $order_colisions = array();
    foreach ($ordered_rooms as $r) {
        $r = trim($r);
        if (!$r) {
            $platba->error = "Smažte zbytečné čárky pokojů, např: <b>1,,2</b> nebo <b>1,2,</b>";
            return $platba;
        }
        if (!in_array($r, $rooms_nums)) {
            $platba->error = "Pokoj $r neexistuje.";
            return $platba;
        }
        if (!in_array($r, $free_rooms)) {
            $platba->error = "Pokoj $r není volný.";
            return $platba;
        }
        if (in_array($r, $incl_order)) $order_colisions[]= $r;
        $places += $rooms_data->room_{$r}["beds"];
        $type = "noc_L";
        if ($r >= 14 && $r <= 17) $type = "noc_S";
        elseif ($r < 10) $type = "noc_A";
        while ($older_ppl > 0 && $rooms_data->room_{$r}["beds"] > 0) {
            $polozky->$type++;
            $older_ppl--;
            $rooms_data->room_{$r}["beds"]--;
        }
        $left_places += $rooms_data->room_{$r}["beds"];
    }

    //addbeds
    if ($older_ppl > 0) {
        foreach ($ordered_rooms as $r) {
            while ($older_ppl > 0 && $rooms_data->room_{$r}["addbeds"] > 0) {
                $polozky->noc_P++;
                $older_ppl--;
                $rooms_data->room_{$r}["addbeds"]--;
            }
        }
    }
    if ($polozky->noc_P > 0) $platba->info = "Přistýlek: $polozky->noc_P<br>" . $platba->info;

    if ($older_ppl > 0) $platba->error = "Ve zvolených pokojích nezbylo míst pro $older_ppl hostů.";
    else if ($left_places / $places > 0.5) $platba->error = "Více než polovina postelí (z $places) není obsazená.";
    else ds_platba_hosta(date("Y"), $polozky, $platba);

    if ($order_colisions && count($order_colisions) > 0) {
        $platba->info = "Pozor: existuje nezávazná objednávka na pokoje č. "
            . implode(", ", $order_colisions) . "<br>$platba->info";
    }

    return $platba;
}