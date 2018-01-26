<?php

// cron job:
// 0 * * * * php /var/www/nedetel/public/cron.php


require_once('../private/config.php');
require_once('../private/utils.php');
require_once('../private/bdd.php');
require_once('../vendor/autoload.php');

$result = q("
    SELECT *
    FROM sai_transicion_estado_atencion
    ,sai_paso_atencion
    WHERE tea_borrado IS NULL
    AND paa_borrado IS NULL
    AND paa_transicion_estado_atencion = tea_id
    AND paa_paso_anterior IS NULL
    AND tea_tiempo_alerta_horas > 0
    AND paa_contador_alerta >= tea_tiempo_alerta_horas;
");



if ($result) {
    foreach ($result as $r) {
        q("
            UPDATE sai_paso_atencion
            SET paa_contador_alerta = 0
            WHERE paa_id = {$r[paa_id]}
        ");
        //$asunto = 'Recordatorio';
        $asunto = $r[paa_asunto];
        //$mensaje = 'Hola, tienes pendientes en SAIT, por favor revísalos.';
        $mensaje = $r[paa_cuerpo];

        $emails = $r[paa_destinatarios];
        $emails = explode(',', $emails);

        $adjuntos = $r[paa_adjuntos];
        $adjuntos = explode(',', $adjuntos);

        try {

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Host = SMTP_SERVER;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            //$mail->SMTPDebug = 2;
            $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->Subject = $asunto;
            $mail->MsgHTML($mensaje);

            foreach ($emails as $email) {
                if (!empty($email)) {
                    $mail->AddAddress($email);
                }
            }

            foreach ($adjuntos as $adjunto) {
                if (!empty($adjunto)) {
                    $mail->AddAttachment($adjunto);
                }
            }

            $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->AddBCC(MAIL_COPY_ALL_ADDRESS, MAIL_COPY_ALL_NAME);


            //echo '<pre>';
            //var_dump($r);
            //echo '</pre><hr>';
            if (!$mail->Send()) { 
                throw new Exception($mail->ErrorInfo);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            l('Error en ' . $e->getFile() . ', linea ' . $e->getLine() . ': ' . $e->getMessage());
            return;
        }
    }
}



q("
    UPDATE sai_paso_atencion
    SET paa_contador_alerta = paa_contador_alerta + 1
    WHERE paa_borrado IS NULL
    AND paa_paso_anterior IS NULL
    AND paa_transicion_estado_atencion IN (
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_tiempo_alerta_horas > 0
    )
");
