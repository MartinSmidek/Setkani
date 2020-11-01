<?php

$allowed_mails = array("objednavky-domu@setkani.org", "dum@setkani.org");
$credentials_path = '../files/setkani4/credential.json';
$required_privileges = array(
    //"https://www.googleapis.com/auth/gmail.settings.basic", //to view email metadata
    //"https://www.googleapis.com/auth/gmail.send" //to send emails
    "https://mail.google.com/" //global privilege
);
$tokenPathPrefix = '../files/setkani4/token_'; //path and token file prefix, email address will be appended
$tokenPathSuffix = '.json';
$gmail_api_library = $_SERVER['DOCUMENT_ROOT'].'/ezer3.1/server/licensed/google_api/vendor/autoload.php';