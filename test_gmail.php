<?php

sendMail("objednavky-domu@setkani.org");

function sendMail($email) {
    $tokenPathPrefix = '../files/setkani4/token_'; //path and token file prefix, email address will be appended
    $tokenPathSuffix = '.json';
    $filePath = $tokenPathPrefix . $email . $tokenPathSuffix;
    if (!is_file($filePath) || !is_readable($filePath)) {
        //todo error
        echo "FILE NOT FOUND";
    }
    require_once $_SERVER['DOCUMENT_ROOT'].'/ezer3.1/server/licensed/google_api/vendor/autoload.php';

    //todo add try catch
    try {
        $client = new Google_Client();
        $credentials_path = '../files/setkani4/credential.json';
        $client->setAuthConfig($credentials_path);
        $client->setPrompt("consent");
        $required_privileges = array(
            //"https://www.googleapis.com/auth/gmail.settings.basic", //to view email metadata
            //"https://www.googleapis.com/auth/gmail.send" //to send emails
            "https://mail.google.com/" //global privilege
        );
        $client->setScopes($required_privileges);
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $accessToken = json_decode(file_get_contents($filePath), true);
        $client->setAccessToken($accessToken);
        $message = new Google_Service_Gmail_Message();
        mail_send($message, 'zlatydeny@seznam.cz', "zlatydeny@seznam.cz", "Nový mail",
            "Funguje to MAILER",$email,"piratskypokoj29");
        echo "7<br>";
        $service = new Google_Service_Gmail($client);
        try {
            echo "8<br>";
            return $service->users_messages->send('me', $message);
        } catch (Exception $e) {
            //todo error - use $e->getMessage();
        }
    } catch (Exception $e) {
        echo  $e;
    }
    return null;
}

// gmail_service:: Google_Service_Gmail_Message instance
function mail_send_cc($gmail_service, $reply_to,$address,$subject,$body, $cc, $cc_name, $cc2='', $cc2_name='') {
    return mail_send($gmail_service, $reply_to,$address,$subject,$body, $gmail_name="YMCA Setkání",$gmail_user="unknown",
        $gmail_pass="unknown", $cc, $cc_name, $cc2, $cc2_name);
}
function mail_send($gmail_service, $reply_to,$address,$subject,$body, $gmail_name="YMCA Setkání",$gmail_user="unknown",
                   $gmail_pass="unknown", $cc='', $cc_name='', $cc2='', $cc2_name='') {

    global $api_gmail_user, $api_gmail_pass;
    if ($gmail_user==='unknown') $gmail_user = $api_gmail_user;
    if ($gmail_pass==='unknown') $gmail_pass = $api_gmail_pass;

    $ret= (object)array('err'=>0,'msg'=>'N.Y.I');
// goto end;
//   $address= "martin@smidek.eu";
//   $subject= "test";
//   $body= "TEST";
    $TEST= 0;
    $ezer_path_serv= $_SERVER['DOCUMENT_ROOT']."/ezer3.1/server";
    $phpmailer_path= "$ezer_path_serv/licensed/phpmailer";
    require_once("$phpmailer_path/class.phpmailer.php");
    require_once("$phpmailer_path/class.smtp.php");
    $n= $nko= 0;
    // nastavení phpMail
    $mail= new PHPMailer(true);
    $mail->SetLanguage('cs',"$phpmailer_path/language/");
    //$mail->IsSMTP();
   // $mail->SMTPAuth = true; // enable SMTP authentication
    // $mail->SMTPSecure= "ssl"; // sets the prefix to the server
   // $mail->Host= "smtp.gmail.com"; // sets GMAIL as the SMTP server
   // $mail->Port= 465; // set the SMTP port for the GMAIL server
  //  $mail->Username= $gmail_user;
   // $mail->Password= $gmail_pass;

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
    if ($cc != '') $mail->AddCC($cc, $cc_name);
    if ($cc2 != '') $mail->AddCC($cc2, $cc2_name);

    if ( $TEST ) {
        $ret->msg= "TESTOVÁNÍ - vlastní mail.send je vypnuto";
        goto end;
    }
    else {
        // odeslání mailu
        try {
            //todo changes
            //$ok= $mail->Send();
            $ok = $mail->preSend();
            $mime = $mail->getSentMIMEMessage();
            $data = base64_encode($mime);
            $data = str_replace(array('+','/','='),array('-','_',''),$data); // url safe
            $gmail_service->setRaw($data);

//            $ret->msg= $ok ? '' : $mail->ErrorInfo;
//            $ret->err= $ok ? 0 : 1;
        } catch ( Exception $exc ) {
//            $ret->msg= $mail->ErrorInfo;
//            $ret->err= 2;
        }
    }
    end:
    //return $ret;
    return true;
}

