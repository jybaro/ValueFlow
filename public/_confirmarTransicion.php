<?php

//var_dump($_POST);
//return;

$respuesta = array();
$email_count = 0;
if (!empty($_POST) && isset($_POST['ate_id']) && !empty($_POST['ate_id']) && isset($_POST['estado_siguiente_id']) && !empty($_POST['estado_siguiente_id'])) {

    $ate_id = $_POST['ate_id'];
    $estado_siguiente_id = $_POST['estado_siguiente_id'];

    $result_destinatarios = q("
        SELECT des_nombre FROM sai_destinatario
    ");
    //$destinatarios = array('cliente', 'proveedor', 'usuario');
    $destinatarios = array();
    if ($result_destinatarios) {
        foreach($result_destinatarios  as $r) {
            $destinatarios[] = $r['des_nombre'];
        }
    }

    $paa_id_lista = array();
    $paa_lista = array();

    foreach ($destinatarios as $destinatario) {
        if (isset($_POST['email_' . $destinatario]) && !empty($_POST['email_' . $destinatario])) {
            $tea_id = $_POST['tea_id_' . $destinatario];
            $asunto = (isset($_POST['asunto_' . $destinatario]) && !empty($_POST['asunto_' . $destinatario])) ? $_POST['asunto_' . $destinatario] : 'Notificación SAIT';

            $mensaje = (isset($_POST['mensaje_' . $destinatario]) && !empty($_POST['mensaje_' . $destinatario])) ? $_POST['mensaje_' . $destinatario] : 'Notificación SAIT';

            $emails = $_POST['email_' . $destinatario];
            $emails = explode(',', $email);

            $adjuntos = array();

            if (isset($_POST['adjunto_' . $destinatario]) && !empty($_POST['adjunto_' . $destinatario]) && is_array($_POST['adjunto_' . $destinatario])) {
                foreach ($_POST['adjunto_' . $destinatario] as $adjunto) {
                    $adjuntos[] = $adjunto;
                }
            }


            try {
                //MAIL
                //echo "[[$pla_asunto - $pla_cuerpo]]";
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
                $mail->Subject = $asunto;
                $mail->MsgHTML($mensaje);

                foreach ($emails as $email) {
                    if (!empty($email)) {
                        $mail->AddAddress($email);
                    }
                }

                foreach ($adjuntos as $adjunto) {
                    $mail->AddAttachment($adjunto);
                }

                $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
                $mail->AddBCC(MAIL_COPY_ALL_ADDRESS, MAIL_COPY_ALL_NAME);


                if (!$mail->Send()) { 
                    throw new Exception($mail->ErrorInfo);
                } else {
                    $email_count++;
                    $emails = implode(',', $emails);
                    $result = q("
                        INSERT INTO sai_paso_atencion (
                            paa_atencion
                            ,paa_transicion_estado_atencion
                            ,paa_codigo
                            ,paa_asunto
                            ,paa_cuerpo
                            ,paa_destinatarios 
                        ) VALUES (
                            $ate_id
                            ,$tea_id
                            ,''
                            ,'$asunto'
                            ,'$mensaje'
                            ,'$emails'
                        ) RETURNING *
                    ");
                    if ($result) {
                        $paa_id = $result[0]['paa_id'];
                        $paa_id_lista[] = $paa_id;
                        $paa_lista[] = $result;
                    }
                }
            } catch (Exception $e) {
                //echo $e->getMessage();
                l('Error en ' . $e->getFile() . ', linea ' . $e->getLine() . ': ' . $e->getMessage());
                echo json_encode(array('ERROR'=>$e->getMessage()));
                return;
            }
        }
    }

    if (!empty($paa_id_lista)) {
        $respuesta['pasos_nuevos'] = $paa_lista;
        $paa_id_lista = implode(',', $paa_id_lista);
        $result = q("
            UPDATE sai_paso_atencion 
            SET paa_paso_anterior = now()
            WHERE paa_borrado IS NULL
            AND paa_paso_anterior IS NULL
            AND paa_atencion = $ate_id
            AND NOT paa_id IN ($paa_id_lista)
            RETURNING *
        ");
        $respuesta['pasos_anteriores'] = $result;
        $result = q("
            UPDATE sai_atencion 
            SET ate_estado_atencion = $estado_siguiente_id 
            WHERE ate_borrado IS NULL
            AND ate_id = $ate_id 
            RETURNING *
        ");
        $respuesta['atencion'] = $result;
    } else {
        $respuesta = array('ERROR' => 'No se pudo realizar el cambio de estado.');
    }

}

if ($email_count == 0) {
    $respuesta = array('ERROR' => 'No se pudieron enviar los mensajes.');
}

echo json_encode($respuesta);


