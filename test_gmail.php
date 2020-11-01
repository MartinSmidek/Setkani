<?php

send_mail("objednavky-domu@setkani.org", "zlatydeny@seznam.cz", "Hello World",
    "Mail WORKS!", "Objednávky Domu Setkání", "objednavky-domu@setkani.org");

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

    require_once "gmail_constants.php"; //common constants definition in www/setkani4/ folder
    // see if token file exists and is readable for given $gmail_sender_mail
    $filePath = $tokenPathPrefix . $gmail_sender_mail . $tokenPathSuffix;
    if (!is_file($filePath) || !is_readable($filePath)) {
        return "This email address was not authenticated to with OAuth2: " . $gmail_sender_mail;
    }

    // gmail or phphmailer can throw
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
                return "Unable to obtain refresh token. New token must be requested using gmail_token.php";
            }
        }

        //create message and fill raw data with phpmailer
        $message = new Google_Service_Gmail_Message();
        prepare_message_with_mailer($message, $reply_to, $recipient_address, $subject, $body, $gmail_sender_name,
            $gmail_sender_mail, $cc, $cc_name, $cc2, $cc2_name);

        //send email
        $service = new Google_Service_Gmail($client);
        try {
            $service->users_messages->send('me', $message);
            return null;
        } catch (Exception $e) {
            return "Unable to send created email.";
        }
    } catch (Exception $e) {
       return $e->getMessage();
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

    //not needed anymore:
    //$mail->IsSMTP();
    //$mail->SMTPAuth = true; // enable SMTP authentication
    //$mail->SMTPSecure= "ssl"; // sets the prefix to the server
    //$mail->Host= "smtp.gmail.com"; // sets GMAIL as the SMTP server
    //$mail->Port= 465; // set the SMTP port for the GMAIL server
    //$mail->Username= $gmail_user;
    //$mail->Password= $gmail_pass;

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

