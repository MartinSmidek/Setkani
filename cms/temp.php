<?php

/**

 * Customify functions and definitions

 *

 * @link    https://developer.wordpress.org/themes/basics/theme-functions/

 *

 * @package customify

 */



/**

 *  Same the hook `the_content`

 *

 * @TODO: do not effect content by plugins

 *

 * 8 WP_Embed:run_shortcode

 * 8 WP_Embed:autoembed

 * 10 wptexturize

 * 10 wpautop

 * 10 shortcode_unautop

 * 10 prepend_attachment

 * 10 wp_make_content_images_responsive

 * 11 capital_P_dangit

 * 11 do_shortcode

 * 20 convert_smilies

 */

global $wp_embed;

add_filter( 'customify_the_content', array( $wp_embed, 'run_shortcode' ), 8 );

add_filter( 'customify_the_content', array( $wp_embed, 'autoembed' ), 8 );

add_filter( 'customify_the_content', 'wptexturize' );

add_filter( 'customify_the_content', 'wpautop' );

add_filter( 'customify_the_content', 'shortcode_unautop' );

add_filter( 'customify_the_content', 'wp_make_content_images_responsive' );

add_filter( 'customify_the_content', 'capital_P_dangit' );

add_filter( 'customify_the_content', 'do_shortcode' );

add_filter( 'customify_the_content', 'convert_smilies' );





/**

 *  Same the hook `the_content` but not auto P

 *

 * @TODO: do not effect content by plugins

 *

 * 8 WP_Embed:run_shortcode

 * 8 WP_Embed:autoembed

 * 10 wptexturize

 * 10 shortcode_unautop

 * 10 prepend_attachment

 * 10 wp_make_content_images_responsive

 * 11 capital_P_dangit

 * 11 do_shortcode

 * 20 convert_smilies

 */

add_filter( 'customify_the_title', array( $wp_embed, 'run_shortcode' ), 8 );

add_filter( 'customify_the_title', array( $wp_embed, 'autoembed' ), 8 );

add_filter( 'customify_the_title', 'wptexturize' );

add_filter( 'customify_the_title', 'shortcode_unautop' );

add_filter( 'customify_the_title', 'wp_make_content_images_responsive' );

add_filter( 'customify_the_title', 'capital_P_dangit' );

add_filter( 'customify_the_title', 'do_shortcode' );

add_filter( 'customify_the_title', 'convert_smilies' );



// not necessary, filter call disabled in inc/template_functions.php

// function show_page_titles( $show ){

// 	return $show;

// }

// add_filter('customify_is_post_title_display', 'show_page_titles', 100 );





function wpse33551_post_type_link( $link, $post = 0 ){

    if (is_single() && 'post' == get_post_type() && $post) {



        $categ_array = get_the_category($post->ID);

        $slugs = array();

        foreach($categ_array as $categ) {

            array_push($slugs, $categ->slug);

        }

        if (in_array('osp-clanky', $slugs)) {

            return '/scio-testy/osp/clanky/' . $post->post_name;

        } else if (in_array('tsp-clanky', $slugs)) {

            return '/tsp/clanky/' . $post->post_name;

        } else if (in_array('zsv-clanky', $slugs)) {

            return '/scio-testy/zsv/clanky/' . $post->post_name;

        }

    }

    return $link;

}

add_filter('post_link', 'wpse33551_post_type_link', 1, 3);





//modify main menu if user logged in

function menu_add_admin_buttons( $items, $args ) {

    $btn_format = '<li id="menu-item--main-desktop-460" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-460">

                 <a href="%s"><span class="link-before">%s</span></a>

                 %s</li>';

    if ( is_user_logged_in() ) {

        $btn = sprintf($btn_format, '/muj-ucet/', __(wp_get_current_user()->display_name) . '<span class="nav-icon-angle">&nbsp;</span>', '<ul class="sub-menu sub-lv-0">

	    <li id="menu-item--main-desktop-461" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-461"><a href="'.wp_logout_url().'"><span class="link-before">Odhlásit se</span></a></li>

        </ul>');

    } else {

        $btn = sprintf($btn_format, '/prihlaseni/', __('PŘIHLÁŠENÍ'), '');

    }

    return $items . $btn;

}

add_filter('wp_nav_menu_header_items', 'menu_add_admin_buttons', 20, 2);



//add_action('wp_logout','ps_redirect_after_logout');

//function ps_redirect_after_logout(){

//         wp_redirect( 'https://www.oscio.cz/' );

//         exit();

//}





/**

 * Variously used value for oscio

 */

global $wpdb;

$VAR_KATEGORIE = Array();

$VAR_KATEGORIE_MENU = Array();

$VAR_KATEGORIE_COMP = Array();

$VAR_KATEGORIE["0"] = "článek nepatří k žádnému testu";

$VAR_KATEGORIE_MENU["0"] = "";

$VAR_KATEGORIE_COMP["0"] = "";

$dotaz = $wpdb->get_results("SELECT id,klic,menu,nadpis FROM kategorie ORDER BY poradi DESC", ARRAY_A);

if ( !empty($dotaz) ) {

    foreach($dotaz as $vypis) {

        $VAR_KATEGORIE[$vypis["id"]] = $vypis["nadpis"];

        $VAR_KATEGORIE_MENU[$vypis["id"]] = $vypis["menu"];

        $VAR_KATEGORIE_COMP[$vypis["id"]] = "/" . $vypis["klic"];

    }

}

$dotaz = $wpdb->get_results("SELECT id,nazev FROM subkategorie ORDER BY poradi DESC", ARRAY_A);

if ( !empty($dotaz) ) {

    foreach($dotaz as $vypis) {

        $VAR_SUBKATEGORIE[$vypis["id"]] = $vypis["nazev"];

    }

}



/**

 * Define new url arguments accepted by website

 */

function add_query_vars( $variables ) {

    $variables[] = "subkategorie";

    $variables[] = "kategorie";

    $variables[] = "strana";

    $variables[] = "typ_ulohy";

    $variables[] = "id";

    $variables[] = "typ";

    $variables[] = "date";

    $variables[] = "action";

    //PREMIUM ENDPOINT
    $variables[] = 'premium-oscio';

    return $variables;
}

add_filter( 'query_vars', 'add_query_vars' );



/*Add rewrite rules*/

function rw_rewrite_rules() {



    //Úlohy seznamy

    //add_rewrite_rule('^scio-testy/ulohy/?$','index.php?pagename=ulohy','top');  //modified in page settings

    add_rewrite_rule('^scio-testy/ulohy/strana-([0-9]+)/?$','index.php?pagename=ulohy&strana=$matches[1]','top');

    add_rewrite_rule('^scio-testy/([^/]+)/ulohy/?$','index.php?pagename=ulohy-kategorie&kategorie=$matches[1]','top');

    add_rewrite_rule('^scio-testy/([^/]+)/ulohy/strana-([0-9]+)/?$','index.php?pagename=ulohy-kategorie&kategorie=$matches[1]&strana=$matches[2]','top');

    add_rewrite_rule('^scio-testy/[^/]+/ulohy/[^/]*-([0-9]+)/?$','index.php?pagename=ulohy-subkategorie&subkategorie=$matches[1]','top');

    add_rewrite_rule('^scio-testy/[^/]+/ulohy/[^/]*-([0-9]+)/strana-([0-9]+)/?$','index.php?pagename=ulohy-subkategorie&subkategorie=$matches[1]&strana=$matches[2]','top');

    add_rewrite_rule('^ulohy-dle-typu-([0-9]+)/strana-([0-9]+)/?$','index.php?pagename=ulohy-typ_ulohy&typ_ulohy=$matches[1]&strana=$matches[2]','top');

    add_rewrite_rule('^ulohy-dle-typu-([0-9]+)/?$','index.php?pagename=ulohy-typ_ulohy&typ_ulohy=$matches[1]','top');

    add_rewrite_rule('^scio-testy/([^/]+)/ulohy/([0-9]+)/typ-([0-9]+)/?$', 'index.php?pagename=uloha&kategorie=$matches[1]&id=$matches[2]&typ=$matches[3]', 'top');

    add_rewrite_rule('^scio-testy/([^/]+)/ulohy/([0-9]+)/?$', 'index.php?pagename=uloha&kategorie=$matches[1]&id=$matches[2]', 'top');



    //Kategorie článků, more versions -> rewrite rule for old oscio page system

    add_rewrite_rule('^scio-testy/osp/clanky/?$', 'index.php?category_name=osp-clanky', 'top');

    add_rewrite_rule('^scio-testy/osp/nazory/?$', 'index.php?category_name=osp-clanky', 'top');

    add_rewrite_rule('^scio-testy/osp/navody/?$', 'index.php?category_name=osp-clanky', 'top');

    add_rewrite_rule('^scio-testy/osp/tipy-a-triky/?$', 'index.php?category_name=osp-clanky', 'top');



    add_rewrite_rule('^scio-testy/zsv/clanky/?$', 'index.php?category_name=zsv-clanky', 'top');

    add_rewrite_rule('^scio-testy/zsv/nazory/?$', 'index.php?category_name=zsv-clanky', 'top');

    add_rewrite_rule('^scio-testy/zsv/navody/?$', 'index.php?category_name=zsv-clanky', 'top');

    add_rewrite_rule('^scio-testy/zsv/tipy-a-triky/?$', 'index.php?category_name=zsv-clanky', 'top');



    add_rewrite_rule('^tsp/clanky/?$', 'index.php?category_name=tsp-clanky', 'top');

    add_rewrite_rule('^tsp/ulohy/?$','index.php?pagename=ulohy-kategorie&kategorie=tsp','top');

    add_rewrite_rule('^tsp/ulohy/([0-9]+)/?$', 'index.php?pagename=uloha&kategorie=tsp&id=$matches[1]', 'top');



    //Jednotlivé články

    add_rewrite_rule('^scio-testy/osp/clanky/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/osp/nazory/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/osp/navody/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/osp/tipy-a-triky/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/zsv/clanky/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/zsv/nazory/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/zsv/navody/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/zsv/tipy-a-triky/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');

    add_rewrite_rule('^tsp/clanky/([^/]+)/?$', 'index.php?pagename=$matches[1]', 'top');



    //other pages redirects  TODO why this block does not work?

    add_rewrite_rule('^test-vysledky/?$', 'index.php?pagename=test-osp-historie', 'top');

    add_rewrite_rule('^scio-testy/osp/test-osp/testy/strana-([0-9]+)?$', 'index.php?pagename=test-osp-historie&strana=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/osp/test-osp/testy/?$', 'index.php?pagename=test-osp-historie', 'top');

    add_rewrite_rule('^scio-testy/osp/test-osp/test=([0-9]+)/?$', 'index.php?pagename=osp-test-results&date=$matches[1]', 'top');

    add_rewrite_rule('^scio-testy/clanky/strana-([0-9]+)/$', 'index.php?category_name=clanky', 'top'); //todo invalid? how wordpress manages paging?

    add_rewrite_rule('^scio-testy/([^/]+)/clanky/strana-([0-9]+)/$', 'index.php?category_name=osp-clanky', 'top'); //todo invalid? how wordpress manages paging?

    add_rewrite_rule('^statni-maturita/?$', 'index.php?pagename=nova-maturita', 'top');

    add_rewrite_rule('^generuj_test/?$', 'index.php?pagename=osp-test', 'top');

    add_rewrite_rule('^registrace/?$', 'index.php?pagename=premiovy-ucet', 'top');

    add_rewrite_rule('^ucebnice/aktualizace/?$', 'index.php?pagename=ucebnice', 'top');

    add_rewrite_rule('^scio-testy/anglicky-jazyk/gramatika-teorie/?$', 'index.php?pagename=aj-gram', 'top');

    add_rewrite_rule('^scio-testy/anglicky-jazyk/gramatika-teorie/action=([0-9]+)/?$', 'index.php?pagename=aj-gram&action=$matches[1]', 'top');

    add_rewrite_rule('^o-nas/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^podminky/?$', 'index.php?pagename=obchodni-podminky', 'top');

    add_rewrite_rule('^download/ucebnice_ukazka.pdf/?$', 'index.php?pagename=ucebnice', 'top');

    add_rewrite_rule('^ucebnice/?$', 'index.php?pagename=osp-ucebnice', 'top');

    add_rewrite_rule('^produkt/videokurz-osp-verbalni-oddil/?$', 'index.php?pagename=videokurz-osp', 'top');

    add_rewrite_rule('^produkt/videokurz-osp-analyticky-oddil/?$', 'index.php?pagename=videokurz-osp', 'top');

    add_rewrite_rule('^produkt/videokurz-osp-verbalni-i-analyticky/?$', 'index.php?pagename=videokurz-osp', 'top');

    add_rewrite_rule('^produkt/ucebnice-obecne-studijni-predpoklady/?$', 'index.php?pagename=osp-ucebnice', 'top');

    add_rewrite_rule('^produkt/ucebnice-osp-elektronicka-tistena/?$', 'index.php?pagename=osp-ucebnice', 'top');

    add_rewrite_rule('^produkt/ucebnice-osp-elektronicka-verze/?$', 'index.php?pagename=osp-ucebnice', 'top');

    add_rewrite_rule('^produkt/ucebnice-tsp-e-book/?$', 'index.php?pagename=ebook-tsp', 'top');

    add_rewrite_rule('^produkt/jednodenni-online-kurz-osp/?$', 'index.php?pagename=jednodenni-online-kurz-osp', 'top');

    add_rewrite_rule('^ucebnice-osp/?$', 'index.php?pagename=ucebnice', 'top');



    //old ebook download system

    add_rewrite_rule('^data/code=([^/]+)/?$', 'index.php?pagename=old-download-link', 'top');





    //Not working pages redirect to the main page

    add_rewrite_rule('^diskuse/([^/]+)/strana-([[:digit:]]+)/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^diskuse/([^/]+)/([^/]+)-([[:digit:]]+)/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^diskuse/([^/]+)/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^diskuse/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^anlrkvja4RK73sdgTZ534KdfEF/?$', 'index.php?pagename=uvodni-stranka', 'top');

    add_rewrite_rule('^diskuse/osp/scio-217/?$', 'index.php?pagename=uvodni-stranka', 'top');


    //PREMIUM ENDPOINT
    add_rewrite_endpoint( 'premium-oscio', EP_ROOT | EP_PAGES );
}

add_action('init', 'rw_rewrite_rules', 10, 0);





/**

 * Replace '[-KEY-]' with 'VALUE'

 */

const SABL_KEY_BEGIN = "[-";

const SABL_KEY_END = "-]";

function replace($data, $key, $value) {

    return str_replace( SABL_KEY_BEGIN.$key.SABL_KEY_END, $value, $data );

}

function clearAll($data) {

    return preg_replace("#\[-[^\]]+-]#", "", $data);

}



/**

 *   @autor Petr Paøízek

 *   @version 1.0

 *   @updated 4. bøezen 2007

 *   -----------------------------

 *   Popis: Funkce pro zmìnu velikosti obrázku

 *   -----------------------------

 *   Filename: imgresize.php

 *   Copyright: (c) Marcus Flintus 2007

 *   imgResize($path, $path2, $pripona, $X_max=120, $Y_max=120, $quality=100)

 *   @param $path - zdroj obrazku

 *   @param $path2 - cil obrazku

 *   @param $pripona - pøípona obrázku - pro rozeznání gif x png x jpg

 *   @param $x_may - max. ¹íøka, pokud == 0, pak se dopoèítá podle vý¹ky

 *   @param $y_may - max. vý¹ka, pokud == 0, pak se dopoèítá podle ¹íøky

 *   @param $quality - kvalita výstupního obrázku. 0 - 100

 *   vrací true pri úspìchu jinak false

 */

function imgResize($path, $path2, $pripona, $X_max = 140, $Y_max = 105, $quality = 100, $nahled = 0)

{

    global $s;



    $rozmery = getImageSize($path);

    $X = $rozmery[0];

    $Y = $rozmery[1];



    if (($X / $X_max) > ($Y / $Y_max)) {

        $X_new = $X_max;

        $Y_new = round(($X_max / $X) * $Y, 0);

    } else {

        $Y_new = $Y_max;

        $X_new = round(($Y_max / $Y) * $X, 0);

    }



    if (($pripona == "jpg") OR ($pripona == "jpeg")) {

        $in = ImageCreateFromJPEG($path);

    } elseif ($pripona == "gif") {

        $in = ImageCreateFromGIF($path);

    } elseif ($pripona == "png") {

        $in = ImageCreateFromPNG($path);

    } else {

        //todo create wrong image icon and hardcode it here

    }



    if ($nahled == 0) {

        $out = ImageCreateTrueColor($X_new, $Y_new);

        ImageCopyResampled($out, $in, 0, 0, 0, 0, $X_new, $Y_new, $X, $Y);

    } else {

        if ($Y_new == 106) {

            $Y_new = 105;

        }

        if ($X_new == 141) {

            $x_new = 140;

        }

        $out = ImageCreateTrueColor(142, 107);

        $source = imagecreatefromgif("./img/univ.gif");

        imageCopy($out, $source, 0, 0, 0, 0, 142, 107);

        $sirka = round((142 - $X_new) / 2);

        $vyska = round((107 - $Y_new) / 2);

        ImageCopyResampled($out, $in, $sirka, $vyska, 0, 0, $X_new, $Y_new, $X, $Y);

    }





    if (ImageJpeg($out, $path2, $quality)) {

        $ret = true;

    } else {

        $ret = false;

    }

    ImageDestroy($in);

    ImageDestroy($out);

    return $ret;

}



$pole_diakritiky = Array(

    'ě' => 'e',

    'š' => 's',

    'č' => 'c',

    'ř' => 'r',

    'ž' => 'z',

    'ý' => 'y',

    'á' => 'a',

    'í' => 'i',

    'é' => 'e',

    'ó' => 'o',

    'ť' => 't',

    'ň' => 'n',

    "ů" => "u",

    "ú" => "u"

);

$pole_spec_znaku = Array(

    '&quot;' => "",

    "+" => "-plus-",

    "/" => "-lomitko-",

    "\\" => "-lomitko-",

    "%" => "-procent-",

    '"' => "",

    "<" => "",

    ">" => "",

    '"' => "",

    "'" => "",

    "&" => "",

    " " => "-",

    "?" => "",

    "." => "",

    "," => "",

    "#" => ""

);

$pole_diak_to_small = Array(

    'Ě' => 'e',

    'Š' => 's',

    'Č' => 'c',

    'Ř' => 'r',

    'Ž' => 'z',

    'Ý' => 'y',

    'Á' => 'a',

    'Í' => 'i',

    'É' => 'e',

    'Ó' => 'o',

    'Ť' => 't',

    'Ň' => 'n',

    'Ů' => 'u',

    'Ú' => 'u'

);

$pole_to_url = Array(

    ':' => '%3A',

    '/' => '%2F'

);



/*Edit string to not co contain any clashes with ModRewite*/

function strToComp($str){

    global $pole_diak_to_small;

    global $pole_diakritiky;

    global $pole_spec_znaku;



    $str = strtr( $str, $pole_diak_to_small );

    $str = strtr( $str, $pole_diakritiky );

    $str = strtolower( $str );

    $str = strtr( $str, $pole_spec_znaku );



    while ( strpos($str, "--") !== false ){

        $str = str_replace("--", "-", $str);

    }



    if( $str == "strana" ){ //kvuli Mod_Rewrite

        $str = "stranka";

    }

    if( $str == "clanek" ){

        $str = "text-clanek";

    }

    return $str;

}







function premium_internal($id, $keyword) {

    if ($id == 0) { return false; }

    $user_meta = get_userdata($id);

    $user_roles = $user_meta->roles;

    return in_array($keyword, $user_roles) || in_array("administrator", $user_roles);

}



function premium_osp($id) {

    return premium_internal($id, 'osp_premium');

}

function premium_tsp($id) {

    return premium_internal($id, 'tsp_premium');

}

function premium_zsv($id) {

    return premium_internal($id, 'zsv_premium');

}



// tests given privilege depending on test id

function premium($id, $test_id) {

    switch ($test_id) {

        case 1: return premium_osp($id);

        case 2: return premium_zsv($id);

        case 6: return premium_tsp($id);

        default: return true;

    }

}

function premium_code($test_id) {

    switch ($test_id) {

        case 1: return 'osp_premium';

        case 2: return 'zsv_premium';

        case 6: return 'tsp_premium';

        default: return true;

    }

}



// tests given privilege depending on test id and returns SQL select condition, all privileges below 2 are public

function get_premium_cond($id, $test_id, $prefix = "") {

    $is_allowed = premium($id, $test_id);

    return ($is_allowed) ? " TRUE " : " ".$prefix."Privilege<'2' ";

}



// tests global privilege conditions

function get_premium_cond_global($id) {

    global $wpdb;

    $tests = $wpdb->get_results("SELECT id, privilege FROM kategorie", ARRAY_A);

    $condition = " ";



    foreach($tests as $key=>$value) {

        switch($value["id"]) {

            case 1: if (!premium_osp($id)) {$condition .= "Privilege != ".$value["privilege"]." AND ";} break;

            case 2: if (!premium_zsv($id)) {$condition .= "Privilege != ".$value["privilege"]." AND ";} break;

            case 6: if (!premium_tsp($id)) {$condition .= "Privilege != ".$value["privilege"]." AND ";} break;

            default: break;

        }

    }

    return $condition . "TRUE ";

}

function eval_premium_code($code) {
    switch($code) {
        case 'sdklfl66asVAS5': return premium_code(1);
        case 'sddvjn87CHuhSC': return premium_code(2);
        case 'sdVcn65dfjbMC8': return premium_code(6);
        default: return "";
    }
}





// add privilege account rights on order completion
add_action( 'woocommerce_payment_complete', 'assign_premium_account' );

function assign_premium_account( $order_id ){

    $order = wc_get_order($order_id);

    $string = "";

    if ($order instanceof \WC_Order) {

        $items = $order->get_items();



        foreach($items as $key=>$value) {

            $string .= $value->get_name() . ", ";

        }



    }

    $user = $order->get_user_id();

    file_put_contents("product_log.txt", $user . ": " . $items, FILE_APPEND);

}







function scripts_oscio() {

    wp_enqueue_style( 'custom-css', get_stylesheet_directory_uri(). '/assets/css/custom.css' );

    wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri(). '/assets/js/custom.js', array( 'jquery' ), '', true );

}

add_action( 'wp_enqueue_scripts', 'scripts_oscio' );



function hashPwd($vstup){

    $vystup = md5( "al-g_rit" . sha1($vstup) . "oscio:" );

    return $vystup;

}



function hashIp($vstup){

    $vystup = md5( "ip-addr" . sha1($vstup) . "database:" );

    return $vystup;

}



function hashMl($vstup){

    $vystup = md5( "posr-mail" . sha1($vstup) . "crypt:" );

    return $vystup;

}



function my_strstr ($haystack, $needle, $before_needle = false) {

    if (!$before_needle) return strstr($haystack, $needle);

    else return substr($haystack, 0, strpos($haystack, $needle));

}


/////PREMIUM  ENDPOINT ACCOUNT GET PRIVILEGE
function add_premium_support_link_my_account( $items ) {
    $items['premium-support'] = 'Aktivace účtu';
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'add_premium_support_link_my_account' );
function premium_field($title, $key) {
    return '<p class="code">
           <label for="user_'.$key.'">'.$title.'</label><input type="text" name="'.$key.'" id="user_'.$key.'" class="input" value="" size="10">
         </p>';

}
function premium_code_add($user, $code) {
    if ( isset($_POST[$code] )) {
        $result = eval_premium_code($_POST[$code]);
        if ($result != "") {
            $user->add_role($result);
        }
    }
}
function premium_support_content() {
    echo '<h3>Aktivace prémiového obsahu</h3>';
    echo '<p>Pokud jste si zakoupili jednu z našich učebnic, obdrželi jste aktivační kód. Ten můžete použít zde.</p>';
    $id = get_current_user_id();

    global $wpdb;
    $tests = $wpdb->get_results("SELECT id, privilege FROM kategorie", ARRAY_A);
    $output = "";
    foreach($tests as $key=>$value) {
        switch($value["id"]) {

            case 1: if (!premium_osp($id)) {$output .= premium_field("Prémiový účet OSP", "osp");} break;

            case 2: if (!premium_zsv($id)) {$output .= premium_field("Prémiový účet ZSV", "zsv");} break;

            case 6: if (!premium_tsp($id)) {$output .= premium_field("Prémiový účet TSP", "tsp");} break;

            default: break;

        }

    }
    $user = get_user_by('id', $id);
    if ( isset($_POST['submit'] ) ) {
        premium_code_add($user, 'osp');
        premium_code_add($user, 'zsv');
        premium_code_add($user, 'tsp');
    }

    if ($output!="") {
        echo '<form name="activation-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">' . $output . '<p class="login-submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="AKTIVOVAT VLOŽENÉ KÓDY"></p></form>';
    } else {
        echo 'Všechny prémiové údaje jsou aktivovány.';
    }

}
add_action( 'woocommerce_account_premium-oscio_endpoint', 'premium_support_content' );





// Include the main Customify class.

require_once get_template_directory() . '/inc/class-customify.php';



/**

 * Main instance of Customify.

 *

 * Returns the main instance of Customify.

 *

 * @return Customify

 */

function Customify() {

    // phpc:ignore WordPress.NamingConventions.ValidFunctionName.

    return Customify::get_instance();

}



Customify();



