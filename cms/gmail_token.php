<?php
//USAGE: 1) SETUP ADDRESS: $_SESSION["gmail_api_refresh_token"] = ADDRESS;
//USAGE: 2) OPEN THIS SCRIPT (using script!): window.open("www.setkani.org/gmail_autentizace");

// css in web.css
// url '/gmail_autentizace' in .htaccess

$be_user= isset($_SESSION['cms']['user_id']) ? $_SESSION['cms']['user_id'] : 0;
if (!$be_user) {
    displayToUser('Nepovolený přístup.',
        "Pro autentizaci emailové adresy musíte být přihlášen jako redaktor.",
        'ZAVŘÍT', "top.close();", 0);
} else {
    // SETUP
    $delay = 5; //seconds to redirect
    $allowed_mails = array("objednavky-domu@setkani.org", "dum@setkani.org");
    $credentials_path = '../../files/setkani4/credentials.json';
    $required_privileges = array(
        "https://www.googleapis.com/auth/gmail.settings.basic", //to view email metadata
        "https://www.googleapis.com/auth/gmail.send" //to send emails
    );
    $tokenPathPrefix = '../../files/setkani4/token_'; //path and token file prefix, email address will be appended
    $tokenPathSuffix = '.json';
    $email = $_SESSION["gmail_api_refresh_token"];

    displayToUser('Povolený přístup.',
        "",
        'ZAVŘÍT', "top.close();", 0);
    exit;


    // FIRE
    require_once 'wp-content/external/vendor/autoload.php';

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

        displayToUser('Budete přesměrováni na Google.',
            "Přihlašte se jako uživatel <b>$email</b> a povolte aplikaci <i>Answer</i> práva k odesílání emailů.",
            'ZAVŘÍT', 'top.close();', $delay);

        // Get the token - redirect to the same page !! HTTP or HTTPS must be correctly set
        $client->setLoginHint($email);
        $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $client->setRedirectUri($redirect_uri);
        $auth_url = $client->createAuthUrl();

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


function displayToUser($title, $msg, $button_text, $button_js, $loader) {
    echo "<div class='lds_container'>";
    echo "<h2 class='lds_fullwidth'>$title</h2><p class='lds_fullwidth' style='font-size: 18pt;'>$msg</p>";
    if ($loader) echo "<div class='lds_fullwidth'><div class='lds-dual-ring'><span id='counterText'>{$loader} s</span></div><div>";
    echo "<div class='lds_fullwidth'><a class='lds_button' style='cursor:pointer' onclick=\"$button_js\"><b>$button_text</b></a></div></div>";
}

?>