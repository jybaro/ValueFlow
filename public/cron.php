<?php

$result = q("
    SELECT *
    FROM sai_transicion_estado_atencion
    ,sai_paso_atencion
    WHERE tea_borrado IS NULL
    AND paa_borrado IS NULL
    AND paa_transicion_estado_atencion = tea_id
    AND paa_paso_anterior IS NULL
    AND tea_tiempo_alerta_horas > 0
");

if ($result) {
    foreach ($result as $r) {
        if ($r[paa_contador_alerta] == $r[tea_tiempo_alerta_horas]) {
        $asunto = 'Recordatorio';
        $mensaje = 'Hola, tienes pendientes en SAIT, por favor revísalos.';
        $emails = $r[];
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

            /*
            foreach ($adjuntos as $adjunto) {
                $mail->AddAttachment($adjunto);
            }
             */

            $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->AddBCC(MAIL_COPY_ALL_ADDRESS, MAIL_COPY_ALL_NAME);


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
