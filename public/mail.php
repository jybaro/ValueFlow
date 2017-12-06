<?php

//require_once('../library/PHPMailer_v5.1/class.phpmailer.php');
//require_once('vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once('vendor/autoload.php');

define('SMTP_SERVER', 'mail.nedetel.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'sait@nedetel.net');
define('SMTP_PASSWORD', 'n3D1$207*');

define('MAIL_ORDERS_ADDRESS', 'sait@nedetel.net');
define('MAIL_ORDERS_NAME', 'SAIT');


        try{
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Host = SMTP_SERVER;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPDebug = 2;
            $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->Subject = 'prueba';
            $mail->MsgHTML('<b>Esto es una prueba desde SAIT</b>');
            $mail->AddAddress('edgar.valarezo@gmail.com');
            //$mail->AddAddress('sminga@nedetel.net');
            //$mail->AddAddress('dcedeno@nedetel.net');
            $mail->AddAttachment('prueba.txt');
            $mail->AddAttachment('prueba.pdf');
            $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

            if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
