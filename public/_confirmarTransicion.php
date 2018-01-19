<?php

$respuesta = array();
if (isset($args) && !empty($args) && isset($args[0]) && !empty($args[0]) && isset($args[1]) && !empty($args[1])) {
    $tea_id = $args[0];
    $ate_id = $args[1];

    if (isset($_POST['estado']) && !empty($_POST['estado'])) {
        $estado = $_POST['estado'];
        $id = $_POST['id'];
        $ate_id = $id;
        $tea_id = $_POST['tea_id'];

        $email_cliente = $_POST['email_cliente'];
        $email_proveedor = $_POST['email_proveedor'];
        $email_usuario = $_POST['email_usuario'];
        $email_usuario_comercial = $_POST['email_usuario_comercial'];

        $sql = ("
            SELECT *
            ,(SELECT des_nombre FROM sai_destinatario WHERE des_id = tea_destinatario) AS destinatario
            FROM sai_atencion
            ,sai_transicion_estado_atencion
            ,sai_plantilla
            WHERE ate_borrado IS NULL
            AND tea_borrado IS NULL
            AND pla_borrado IS NULL
            AND pla_transicion_estado_atencion = tea_id
            AND tea_estado_atencion_actual = ate_estado_atencion
            AND ate_pertinencia_proveedor = tea_pertinencia_proveedor
            AND ate_id=$ate_id
        ");
        $result_contenido = q($sql);

        //echo "<pre>";
        //echo "$sql<hr>"; 
        //var_dump($result_contenido);
        //echo "</pre>";
        
        if ($result_contenido) {
            q("UPDATE sai_paso_atencion SET paa_borrado=now() WHERE paa_atencion=$ate_id");

            foreach ($result_contenido as $rc) {
                $pla_asunto = $rc['pla_asunto'];
                $pla_adjunto_nombre = $rc['pla_adjunto_nombre'];

                $pla_cuerpo = $rc['pla_cuerpo'];
                $pla_adjunto_texto = $rc['pla_adjunto_texto'];
                $pla_id = $rc['pla_id'];

                $destinatario = $rc['destinatario'];

                $sql = ("
                    SELECT * 
                    FROM sai_adjunto_plantilla
                    ,sai_archivo
                    WHERE adp_borrado IS NULL
                    AND arc_borrado IS NULL
                    AND arc_id = adp_archivo 
                    AND adp_plantilla=$pla_id
                ");
                $adjunto_plantilla = q($sql);





//echo '<pre>';
//var_dump($rc);
            //  var_dump($adjunto_plantilla);
    //echo $sql;

    //echo "<pre>";
  //  echo "<hr><h1>RESULT CONTENIDO</h1>";
    //var_dump($result_contenido);
//echo '</pre>';
    //die();

                $campos_valores = array();

                if (isset($campos) && is_array($campos)) {
                    $search = array();
                    $replace = array();
                    foreach ($campos as $campo) {
                        $search[] = '%'.$campo['cae_codigo'].'%';
                        $replace[] = $campo['valor'];
                        $campos_valores['%'.$campo['cae_codigo'].'%'] = $campo['valor'];
                    }
                    //echo "<pre>";
                    //echo $pla_cuerpo;
                    //var_dump($search);
                    //var_dump($replace);
                    //var_dump($campos_valores);
                    //echo str_replace($search, $replace, $pla_cuerpo);
                    //echo "</pre>";
                    $pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
                    $pla_asunto = str_replace($search, $replace, $pla_asunto);
                    $pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
                    $pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
                }

                $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
                $pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;
                $pla_cuerpo = (empty($pla_cuerpo)) ? 'Favor revisar' : $pla_cuerpo;


                //require_once('../vendor/autoload.php');


                //echo '<pre>';
                //var_dump($adjunto_plantilla);
                //echo '</pre>';

                $xls_generado = false;
                try {
                    if ($adjunto_plantilla) {
                        $adjunto_plantilla = $adjunto_plantilla[0];
                        //////////////
                        //Excel

                        //echo "sacando Excel";

                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploads/'.$adjunto_plantilla['arc_nombre']);

                        $worksheet = $spreadsheet->getActiveSheet();

                        $filas = $worksheet->toArray();

                        //var_dump($filas);
                        foreach($filas as $x => $fila){
                            foreach($fila as $y => $celda){
                                if (!empty($celda)) {
                                    //echo "[$x, $y: $celda]";
                                    if (preg_match('/\%.+\%/', $celda)){
                                        $nuevo_valor = (isset($campos_valores[$celda])) ? $campos_valores[$celda] : 'Dato no definido';
                                        $worksheet->setCellValueByColumnAndRow($y+1, $x+1, $nuevo_valor);
                                    }
                                }
                            }
                        }

                        //$worksheet->getCell('A1')->setValue('John');
                        //$worksheet->getCell('A2')->setValue('Smith');

                        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
                        $writer->save('adjunto.xls');
                        $xls_generado = true;
                    }


                    //////////////
                    //PDF
                    if (file_exists('adjunto.html')) {
                        unlink('adjunto.html');
                    }
                    if (file_exists($pla_adjunto_nombre.'.pdf')) {
                        unlink($pla_adjunto_nombre.'.pdf');
                    }

                    if (!empty($pla_adjunto_texto)) {
                        $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
                        $msg = ($pla_adjunto_texto);
                        file_put_contents( 'adjunto.html', $msg);
                        $msg = file_get_contents('adjunto.html');
                        //$msg = utf8_decode($msg);
                        $snappy->generateFromHtml($msg, $pla_adjunto_nombre.'.pdf', array('encoding' => 'utf-8'));
                    }

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
                    //$mail->SMTPDebug = 2;
                    $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
                    $mail->Subject = $pla_asunto;
                    $mail->MsgHTML($pla_cuerpo);
                    if ($destinatario == 'cliente' && !empty($email_cliente)) {
                        $mail->AddAddress($email_cliente);
                    }
                    if ($destinatario == 'proveedor' && !empty($email_proveedor)) {
                        $mail->AddAddress($email_proveedor);
                    }
                    $mail->AddAddress($email_usuario);
                    $mail->AddAddress($email_usuario_comercial);
                    //$mail->AddAddress('sminga@nedetel.net');
                    //$mail->AddAddress('dcedeno@nedetel.net');
                    //$mail->AddAddress('edgar.valarezo@gmail.com');
                    //$mail->AddAttachment('prueba.txt');
                    if (!empty($pla_adjunto_texto)) {
                        //echo "AGREGANDO ADJUNTO PDF";
                        $mail->AddAttachment($pla_adjunto_nombre.'.pdf');
                    }
                    if ($xls_generado) {
                        //echo "AGREGANDO ADJUNTO XLS";
                        $mail->AddAttachment('adjunto.xls');
                    }
                    //$mail->AddAttachment('example.xlsx');
                    $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

                    if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
                } catch(Exception $e) {
                    //echo $e->getMessage();
                    l($e->getMessage());
                }

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
                        ,'$pla_asunto'
                        ,'$pla_cuerpo'
                        ,'$email_cliente,$email_proveedor'
                    ) RETURNING *
                ");
            }
        }
        $result = q("UPDATE sai_atencion SET ate_estado_atencion=$estado WHERE ate_id=$id RETURNING *");
    }
}

echo json_encode($respuesta);


