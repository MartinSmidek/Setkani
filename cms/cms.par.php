<?php # (c) 2018 Martin Smidek <martin@smidek.eu>

  // klíče
  $deep_root= "../files/setkani4";
  require_once("$deep_root/cms.dbs.php");
  global $api_gmail_user, $api_gmail_pass;
  
  /// viz \ref struktura-EZER.CMS
  
  $EZER= (object)array(
      // inicializace objektu EZER pro aplikaci WEB (setkani.org)
      'version'=>'ezer'.$_SESSION[$ezer_root]['ezer'],
      'options'=>(object)array(
          'mail' => "martin@smidek.eu",
          'phone' => "603&nbsp;150&nbsp;565",
          'author' => "Martin"
      ),
//      // inicializace objektu EZER pro aplikace FEB
//      'version'=>$_SESSION['feb']['ezer'],
//      'options'=>(object)array(),
      'activity'=>(object)array(),
      'CMS'=>(object)array(
        // dynamicky je definován index
        // pro různé testovací účely  
        'TEST'=>0,
        /// informace o přístupu na gmailový účet  
        'GMAIL'=>(object)array(
          /// přístup na použitý gmail
          'mail'=>$api_gmail_user,
          'pswd'=>$api_gmail_pass,
          'name'=>'YMCA Setkání'
        ),
        'FORM'=>array(
          // přihláška na seminář - bez kontaktu na rodiče
          'akce'=>array(
            'TYPE'=>array('allow_unknown',/*'allow_relogin',*/'confirm'/*,'family'*/,'send_mail'),
            'TEXT'=>array( 
              'cms_confirm'  => 
                    "<span style='font-size:8pt'>
                      Vyplněním této přihlášky dávám výslovný souhlas s použitím uvedených osobních 
                      údajů pro potřeby organizace akcí YMCA Setkání v souladu s Nařízením 
                      Evropského parlamentu a Rady (EU) 2016/679 ze dne 27. dubna 2016 o ochraně 
                      fyzických osob a zákonem č. 101/2000 Sb. ČR. v platném znění. Současně 
                      souhlasím s tím, že pořadatel je oprávněn dokumentovat její průběh – 
                      pořizovat foto, audio, video záznamy a tyto materiály může použít pro účely 
                      další propagace své činnosti. Přečetl jsem si a souhlasím s podrobnou 
                      <i>Informací o zpracování osobních údajů v YMCA Setkání</i>, dostupnou na 
                      <a href='https://www.setkani.org/ymca-setkani/5860' target='show'>www.setkani.org</a>.
                    </span>",
                'cms_confirm_missing_1' =>
                  "<span class='problem'>Projevte prosím souhlas ...</span>",
              'cms_confirm_missing_2' =>
                  "Váš souhlas jsme vzali na vědomí",
              'cms_family_1'  => 
                  "Přihlašuji i další členy rodiny:",
              'CMS_family_2'  => 
                  "Zvolte podle jména a roku narození, kdo s vámi na akci pojede.",
              'CMS_family_3'  => 
                  "Nemáme od vás z dřívějška informaci o členech vaší rodiny, 
                  pošlete prosím přihlášku náhradním způsobem.",
              'CMS_family_error_1'  => 
                  "Při přidávání rodinných příslušníků došlo k chybě.",
              'cms_create_1'  => 
                  "Napište prosím svoji mailovou adresu, na kterou vám dojde 
                  mail s PINem, který umožní dokončit vyplnění přihlášky ...",
              'cms_create_2'  => 
                  "Akce je již obsazená, můžete se ale přihlásit jako náhradník.
                  Napište prosím svoji mailovou adresu, na kterou vám dojde 
                  mail s PINem, který umožní dokončit vyplnění přihlášky ...",
              'CMS_mail_error_1'  => 
                  "Pro přihlášení na akci je nutné nejprve vyplnit mailovou adresu",
              'CMS_mail_error_2'  => 
                  "Mail je v databázi vícekrát",
              'CMS_mail_error_3'  => 
                  "<b style='color:red'>Lituji, mail se nepovedlo odeslat ({SMTP})</b>",
              'CMS_mail_error_4'  => 
                  "Myslím, že '{MAIL}' nevypadá jako správná mailová adresa ({MSG})",
              'CMS_mail_txt'  => 
                  "Pokud jste žádal(a) o přihlášení na {AKCE} napište prosím vedle svojí 
                  mailové adresy {PIN} a použijte tlačítko <b>Potvrdit PIN</b>.
                  <br>Pokud se jedná o omyl, pak prosím tento mail ignorujte.",
              'CMS_mail_1'  => 
                  "Byl vám poslán mail s PINem. Opište prosím doručený PIN do formuláře.",
              'CMS_pin_1'  => 
                  "Děkujeme za potvrzení PINu, nyní prosím zkontrolujte případně opravte
                   nebo doplňte vaše osobní údaje. ",
              'CMS_pin_2'  => 
                  "Děkujeme za potvrzení PINu, nyní prosím vyplňte vaše osobní údaje.",
              'CMS_pin_error_1'  => 
                  "Pro přihlášení je zapotřebí opsat PIN z došlého mailu.",
              'CMS_pin_error_2'  => 
                  "Pro přihlášení je zapotřebí <b>správně opsat</b> PIN z došlého mailu.",
              'cms_pin_no'  => 
                  "Bohužel tato akce je určena jen pro účastníky našich dřívějších akcí.",
              'CMS_submit_1'  => 
                  "Děkujeme za doplnění vašich osobních údajů.",
              'CMS_submit_2'  => 
                  "Vaše údaje byly zapsány.",
              'CMS_submit_3'  => 
                  "Děkujeme za doplnění údajů týkajících se této akce.",
              'CMS_submit_4'  => 
                  "Vaši přihlášku na tuto akci již máme od dřívějška zaevidovanou.",
              'CMS_submit_5'  => 
                  "Vaše přihláška na akci byla zaevidována a byl Vám poslán potvrzující mail.",
              'CMS_submit_error_2'  => 
                  "Při zpracování opravených údajů bohužel došlo k chybě (2).",
              'CMS_submit_error_4'  => 
                  "Při zpracování vkládaných údajů bohužel došlo k chybě (4).",
              'CMS_submit_error_3'  => 
                  "Při zpracování přihlášky bohužel došlo k chybě (3).",
              'cms_submit_missing'  => 
                  "<span class='problem'>Vyplňte prosím chybějící položky.</span>",
              'cms_submit_bad_date'  => 
                  "<span class='problem'>Zadejte prosím datum ve tvaru den.měsíc.rok (d.m.rrrr)</span>",
              'cms_error'=>
                  "Omlouváme se, při zpracování formuláře došlo bohužel k chybě, přihlaste se prosím na akci pomocí kontaktních informací uvedených na webové stránce." 
            ),
            'ELEM'=>array(
              'Ojmeno'    => array('t','+','jméno',115),      // + znamená při novém vynucená
              'Oprijmeni' => array('t','+','příjmení',120),   //   při starém zakázaná položka
              'Otelefon'  => array('t','-','telefon',100),
              'Onarozeni' => array('d','*','narození',70),    // * znamená vynucená položka
              'Oulice'    => array('t','*','ulice',177), 
              'Opsc'      => array('t','*','psč',82), 
              'Oobec'     => array('t','*','obec',158),
              'Oadresa'   => array('h'),
              'Okontakt'  => array('h'),
              'Oid_rodina'=> array('h'),
              'Oweb_souhlas'=> array('h'),
              'Rpoznamka' => array('t','-','poznámka pro pořadatele','95%;',50) //dirty trick to make width 95% instead of px
            ),
            'SQL'=>array(
              'O'=>array('osoba','id_osoba','email'), // ale musí být kontakt=1
              'A'=>array('akce','id_duakce'),
              'R'=>array('pobyt','id_pobyt','id_osoba','id_akce'), // ale jde přes spolu
              // přihlášení přes rodinný mail zatím nelze  
              'mail'=>array("ezer_db2",
                  "SELECT id_osoba FROM osoba WHERE deleted='' AND kontakt=1 AND 
                    email RLIKE '(^|[* ,;]){MAIL}($|[ ,;])' "),
              'select_O'=>array("ezer_db2",
                  "SELECT prijmeni, jmeno, narozeni, adresa, kontakt, 
                     IFNULL(id_rodina,0) AS id_rodina,
                     IF(adresa,o.ulice,r.ulice) AS ulice,
                     IF(adresa,o.psc,r.psc) AS psc, 
                     IF(adresa,o.obec,r.obec) AS obec,
                     IF(kontakt,o.telefon,r.telefony) AS telefon, 
                     IF(kontakt,o.email,r.emaily) AS mail
                   FROM osoba AS o
                   LEFT JOIN tvori AS t USING (id_osoba)
                   LEFT JOIN rodina AS r USING (id_rodina)
                   WHERE o.deleted='' AND id_osoba={IDO}
                   ORDER BY role LIMIT 1"),                
              // přes tabulku spolu - první musí být id_pobyt 
              'select_R'=>array("ezer_db2",
                  "SELECT id_pobyt,pobyt.poznamka
                   FROM pobyt JOIN spolu USING (id_pobyt) 
                   WHERE id_osoba={IDO} AND id_akce={IDA}"),
              // občerstvení příhlášky v situaci, kdy bylo např. odhlášení
              'Rrefresh'=>'funkce=0',
              // Rchange platí pro první spolu
              'Ochange' =>'web_zmena',
              'Rchange' =>'web_zmena',
              ''=>''
            ),
            'CALL'=>array(
              'full_A'   => "cms_full",         // parametr IDA vrací 1 pro plnou akci 0 pro neplnou
              'confirm_O'=> "cms_confirm",      // parametry IDO,IDA
              'family_?' => "cms_family_get",   // parametry IDO,IDA
              'family_!' => "cms_family_set",   // parametry IDO,IDA,JOIN
              'update_O' => "cms_qry",          // parametry jako ezer_qry
              'insert_O' => "cms_qry",          // parametry jako ezer_qry
              'insert_R' => "cms_qry",          // parametry jako ezer_qry
              'sendmail_OA'=> "cms_send_potvrzeni", // parametry mail,IDO,IDA vrací {ok,msg}
              // naplní položku web_changes = 1/2 pro INSERT/UPDATE R | 4/8 pro INSERT/UPDATE O
              'changes_R' => "cms_changes"      // parametry IDR,changes
            )
          ),
          // přihlášení do CMS
          'login'=>array(
            'sql_mail'=>"SELECT id_user FROM _user WHERE mail='{mail}'",
            'LOGIN'=>"login_by_mail({IDO})"  
          )
        )
      )
    );

?>
