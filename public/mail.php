<?php

require_once('../library/PHPMailer_v5.1/class.phpmailer.php');

        try{
            $mail = new PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = SMTP_SERVER;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPDebug = 2;
            $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->Subject = 'AMAZONEK.cz - objednávka číslo '.$_SESSION['orderId'];
            $mail->MsgHTML('<b>Ahoj</b>');
            $mail->AddAddress($_SESSION['user']['email'], $_SESSION['user']['name'].' '.$_SESSION['user']['surname']);
            $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

            if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
