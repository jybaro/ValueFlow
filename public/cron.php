<?php

// cron job:
// 0 * * * * php /var/www/nedetel/public/cron.php

//$ruta = '/var/www/nedetel/';

require_once($ruta . 'private/config.php');
require_once($ruta . 'private/utils.php');
require_once($ruta . 'private/bdd.php');
require_once($ruta . 'vendor/autoload.php');

$result = q("
    SELECT *
    ,(
        SELECT usu_correo_electronico
        FROM sai_usuario
        WHERE usu_borrado IS NULL
        AND usu_id = ate_usuario_tecnico
    ) AS email_tecnico
    ,(
        SELECT usu_correo_electronico
        FROM sai_usuario
        WHERE usu_borrado IS NULL
        AND usu_id = ate_usuario_comercial
    ) AS email_comercial
    ,(
        SELECT usu_correo_electronico
        FROM sai_usuario
        WHERE usu_borrado IS NULL
        AND usu_id = tea_usuario
    ) AS email_extra
    ,(
        SELECT ser_nombre
        FROM sai_servicio
        WHERE ser_borrado IS NULL
        AND ser_id = ate_servicio
    )
    ,(
        SELECT pro_nombre_comercial
        FROM sai_proveedor
        ,sai_pertinencia_proveedor
        WHERE pro_borrado IS NULL
        AND pep_borrado IS NULL
        AND pep_proveedor = pro_id
        AND pep_id = ate_pertinencia_proveedor
    )
    FROM sai_transicion_estado_atencion
    ,sai_paso_atencion
    ,sai_atencion
    ,sai_estado_atencion
    WHERE tea_borrado IS NULL
    AND paa_borrado IS NULL
    AND ate_borrado IS NULL
    AND esa_borrado IS NULL
    AND paa_transicion_estado_atencion = tea_id
    AND paa_atencion = ate_id
    AND ate_estado_atencion = esa_id
    AND paa_paso_anterior IS NULL
    AND NOT paa_confirmado IS NULL
    AND tea_tiempo_alerta_horas > 0
    AND paa_contador_alerta >= tea_tiempo_alerta_horas
");



if ($result) {
    foreach ($result as $r) {
        q("
            UPDATE sai_paso_atencion
            SET paa_contador_alerta = 0
            WHERE paa_id = {$r[paa_id]}
        ");
        //$asunto = 'Recordatorio';
        //$asunto = $r[paa_asunto];
        //$asunto = 'Recordatorio: ' . $asunto;
        //$mensaje = 'Hola, tienes pendientes en SAIT, por favor revísalos.';
        //$mensaje = $r[paa_cuerpo];
        //$mensaje = empty($mensaje) ? $asunto : $mensaje;
        $campos_valores = array();
        $ate_codigo = empty($r['ate_codigo']) ? '' : ", con ID de servicio {$r['ate_codigo']}";
        foreach ($r as $k => $v) {
            $campos_valores[strtoupper($k)] = $v;
        }
        $campos_valores['ATE_CODIGO'] = $ate_codigo;
        
        $plantilla_recordatorio_asunto = c('plantilla_recordatorio_asunto');
        if (empty($plantilla_recordatorio_asunto)) {
            $plantilla_recordatorio_asunto = 'Recordatorio: ${ESA_NOMBRE} ${ATE_SECUENCIAL}${ATE_CODIGO}';
        }

        $plantilla_recordatorio_mensaje = c('plantilla_recordatorio_mensaje');
        if (empty($plantilla_recordatorio_mensaje)) {
            $plantilla_recordatorio_mensaje = 'Se le recuerda dar seguimiento a ${ESA_NOMBRE} de ${SER_NOMBRE} ${PRO_NOMBRE_COMERCIAL} número ${ATE_SECUENCIAL}${ATE_CODIGO}';
        }

    

        //$asunto = "Recordatorio: {$r['esa_nombre']} {$r['ate_secuencial']}{$ate_codigo}";
        //$mensaje = "Se le recuerda dar seguimiento a {$r['esa_nombre']} de {$r['ser_nombre']} {$r['pro_nombre_comercial']} número {$r['ate_secuencial']}$ate_codigo";
        
        $asunto = p_reemplazar_campos_valores($plantilla_recordatorio_asunto);
        $mensaje = p_reemplazar_campos_valores($plantilla_recordatorio_mensaje);

        //$emails = $r[paa_destinatarios];
        //$emails = $r['email_comercial'] . ',' . $r['email_tecnico'];
        $emails = $r['email_tecnico'];
        if ($r['esa_recordar_comercial']) {
            $emails .= ',' . $r['email_comercial'];
        }
        if (!empty($r['email_extra'])) {
            $emails .= ',' . $r['email_extra'];
        }
        $emails = explode(',', $emails);

        $adjuntos = $r[paa_adjuntos];
        $adjuntos = explode(',', $adjuntos);

        if (!empty($asunto) && !empty($mensaje)) {
            try {

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->CharSet = 'UTF-8';
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
}



q("
    UPDATE sai_paso_atencion
    SET paa_contador_alerta = paa_contador_alerta + 1
    WHERE paa_borrado IS NULL
    AND paa_paso_anterior IS NULL
    AND NOT paa_confirmado IS NULL
    AND paa_transicion_estado_atencion IN (
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_tiempo_alerta_horas > 0
    )
");
