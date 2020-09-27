<?php  //THIS FILE IS DISPLAYED OUTSIDE WEBPAGE SCOPE
//USAGE: 1) SETUP ADDRESS: $_SESSION["gmail_api_refresh_token"] = ADDRESS;
//USAGE: 2) OPEN THIS SCRIPT (using script!): window.open("www.setkani.org/gmail_autentizace");

// css in web.css
// url '/gmail_autentizace' in .htaccess
session_start();

echo "<html lang=\"cs-CZ\">
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" >
  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=9\" >
  <meta name=\"viewport\" content=\"width=device-width,user-scalable=yes,initial-scale=1\" >
  <title>YMCA: Gmail Token Autentizace</title>
  <link rel=\"shortcut icon\" href=\"cms/img/web_dsm.png\" >
 
  <link href=\"https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap&amp;subset=latin-ext\" rel=\"stylesheet\">
  <style>
     html { height:100%; width:100%; }
     body { font-family: 'Open Sans', sans-serif; overflow-x:hidden; margin: 0; min-height: 100%; width: 100vw; background: #f2f2f2; font-size:10pt; }
    .lds-dual-ring {display: inline-block;width: auto;height: 120px;position: relative;}
    .lds-dual-ring:after {content: ' ';display: block;width: 64px;height: 64px;margin: 8px;border-radius: 50%;border: 6px solid;
      border-color: #c76b6b transparent #ca4c4c transparent;animation: lds-dual-ring 1.2s linear infinite;}
    @keyframes lds-dual-ring {
      0% {transform: rotate(0deg);}
      100% {transform: rotate(360deg);}
    }
    .lds_fullwidth {width: 100%; text-align:center;}
    .lds_container {width: 60%;height: fit-content;border-radius: 25px;padding: 30px 20px;position: absolute;
      left: 0;right: 0;top: 0;bottom: 0;border: 1px solid #bbb;margin: auto;max-width: 100%;
      max-height: 100%;overflow: auto;}
    .lds_button {background-color: #fd6969;border: none;color: white;padding: 15px 32px;text-align: center;
      text-decoration: none;display: inline-block;font-size: 16px;}
    .lds_code {max-width: 650px;margin: 0 auto;background: #c7c7c7;padding: 10px;overflow: auto;}
    #counterText {width: 100px;text-align: center;font-size: 17pt;line-height: 89px;position: absolute;color: #ca4c4c;left: -4px;}
   </style> 
</head><body>";

$be_user= isset($_SESSION['cms']['user_id']) ? $_SESSION['cms']['USER'] : 0;
$be_allowed = ($be_user) ? in_array('m', explode(" ", $be_user->skills)): false;
if (!$be_allowed) {
    displayToUser('Nepovolený přístup.', "Pro autentizaci emailové adresy musíte být přihlášen jako redaktor.",
        'ZAVŘÍT', "top.close();", 0);
} else {
    // SETUP
    $delay = 5; //seconds to redirect
    $allowed_mails = array("objednavky-domu@setkani.org", "dum@setkani.org");
    $credentials_path = '../files/setkani4/credentials.json';
    $required_privileges = array(
        "https://www.googleapis.com/auth/gmail.settings.basic", //to view email metadata
        "https://www.googleapis.com/auth/gmail.send" //to send emails
    );
    $tokenPathPrefix = '../files/setkani4/token_'; //path and token file prefix, email address will be appended
    $tokenPathSuffix = '.json';
    $email = $_SESSION["gmail_api_refresh_token"];

    // FIRE
    require_once 'ezer3.1/server/licensed/google_api/vendor/autoload.php';

    $client = new Google_Client();
    $client->setAuthConfig($credentials_path);
    $client->setPrompt("consent");
    $client->setScopes($required_privileges);
    $client->setAccessType('offline');
    $client->setIncludeGrantedScopes(true);

    // Get new token - see redirect below
    if (isset($_GET['code'])) {
        $filePath = $tokenPathPrefix . $email . $tokenPathSuffix;
        try {
            $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            // Save the token to a file.
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0700, true);
            }

            $client->setAccessToken($accessToken);
            $service = new Google_Service_Gmail($client);
            $me = $service->users->getProfile('me');
            if ($email != $me->emailAddress) {
                throw new Exception("Špatná emailová adresa: '{$me->emailAddress}'. Přihlašte se jako uživatel '$email'.");
            } else {
                //if (file_exists($filePath)) { echo "WARN: overriding existing token file $filePath"; }
                file_put_contents($filePath, json_encode($accessToken));
                displayToUser('Autentizace se zdařila.',
                    'Můžete zavřít toto okno a pokračovat v odesílání emailů.',
                    'ZAVŘÍT', "top.close();", 0);
                $_SESSION["gmail_api_refresh_token"] = "";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            displayToUser('Autentizace selhala.', "<pre class='lds_code'><code>$error</code></pre>", 'OPAKOVAT',
                "window.location.href = 'https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}';", 0);
        }
    } else  {
        // CHECK MAIL
        if (!in_array($email, $allowed_mails)) {
            displayToUser('Nepovolený přístup.', 'Zadaná adresa \'$email\' není validní adresou pro autentizaci.',
                'ZAVŘÍT', 'top.close();', 0);
            exit;
        }

        // Get the token - redirect to the same page !! HTTP or HTTPS must be correctly set
        $client->setLoginHint($email);
        $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $client->setRedirectUri($redirect_uri);
        $auth_url = $client->createAuthUrl();

        displayToUser('Budete přesměrováni na Google.',
            "Přihlašte se jako uživatel <b>$email</b> a povolte aplikaci <i>Answer</i> práva k odesílání emailů.",
            'ZAVŘÍT', 'top.close();', $delay);

        echo "<script type='text/javascript'>
         var counter = $delay;
         let counterText = document.getElementById('counterText');
         setInterval(function(){
           counter--;
           counterText.innerHTML = counter + ' s';
         },1000);
         setTimeout(function(){
            window.location.href = '$auth_url';
         }, {$delay}000);
         </script>";
    }
}
echo "</body></html>";

function displayToUser($title, $msg, $button_text, $button_js, $loader) {
    echo "<div class='lds_container'>";
    echo "<h2 class='lds_fullwidth'>$title</h2><p class='lds_fullwidth' style='font-size: 18pt;'>$msg</p>";
    if ($loader) echo "<div class='lds_fullwidth'><div class='lds-dual-ring'><span id='counterText'>{$loader} s</span></div><div>";
    echo "<div class='lds_fullwidth'><a class='lds_button' style='cursor:pointer' onclick=\"$button_js\"><b>$button_text</b></a></div></div>";
}

?>