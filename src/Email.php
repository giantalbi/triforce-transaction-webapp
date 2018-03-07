<?php

namespace OgreWeb\Lib;
class Email{
    static function send($pAddress, $pSubject, $pHtml, $pAlt = ""){
        $Mail = new PHPMailer();
        $Mail->IsSMTP(); // Use SMTP
        $Mail->Host = $GLOBALS['config']['smtp_host']; // Sets SMTP server
        $Mail->SMTPAuth = TRUE; // enable SMTP authentication
        $Mail->SMTPSecure = "tls"; //Secure conection
        $Mail->Port = 587; // set the SMTP port
        $Mail->Username = $GLOBALS['config']['smtp_username']; // SMTP account username
        $Mail->Password =  $GLOBALS['config']['smtp_password']; // SMTP account password
        $Mail->Priority = 1; // Highest priority - Email priority (1 = High, 3 = Normal, 5 = low)
        $Mail->CharSet = 'UTF-8';
        $Mail->Encoding = '8bit';
        $Mail->Subject = $pSubject;
        $Mail->ContentType = 'text/html; charset=utf-8\r\n';
        $Mail->From = $GLOBALS['config']['mail_from'];
        $Mail->FromName = $GLOBALS['config']['mail_fromName']; 
        $Mail->WordWrap = 900; // RFC 2822 Compliant for Max 998 characters per line

        //DEBUG
        // $Mail->SMTPDebug  = 1;

        $Mail->AddAddress($pAddress); // To:
        $Mail->isHTML(true);
        $Mail->Body = $pHtml;
        $Mail->AltBody = $pAlt;
        $Mail->Send();
        $Mail->SmtpClose();
    }
}

?>
