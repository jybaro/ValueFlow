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
        require_once('_obtenerCampos.php');

        $email_cliente = q("
            SELECT 
            con_correo_electronico
            FROM sai_contacto
            ,sai_atencion
            ,sai_cuenta
            WHERE ate_borrado IS NULL
            AND cue_borrado IS NULL
            AND con_borrado IS NULL 
            AND ate_cuenta = cue_id
            AND cue_contacto = con_id
            AND ate_id=$ate_id
        ")[0]['con_correo_electronico'];

        $email_proveedor = q("
            SELECT
            vpr_correo_electronico
            FROM sai_vendedor_proveedor
            ,sai_pertinencia_proveedor
            ,sai_atencion
            WHERE ate_pertinencia_proveedor = pep_id
            AND pep_vendedor_proveedor = vpr_id
            AND ate_id=$ate_id
        ")[0]['vpr_correo_electronico'];

        $email_usuario = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_pertinencia_usuario
            ,sai_atencion
            WHERE ate_pertinencia_usuario = peu_id
            AND peu_usuario = usu_id
            AND ate_id=$ate_id
        ")[0]['usu_correo_electronico'];


        $email_usuario_comercial = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_atencion
            WHERE usu_borrado IS NULL
            AND ate_usuario_comercial = usu_id
            AND ate_id=$ate_id
        ")[0]['usu_correo_electronico'];

        //echo "[$email_cliente - $email_proveedor - $email_usuario]";
        $respuesta['emails'] = array($email_cliente, $email_proveedor, $email_usuario, $email_usuario_comercial);
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

        $respuesta['contenido'] = $result_contenido;
        $respuesta['plantilas'] = array();

        //echo "<pre>";
        //echo "$sql<hr>"; 
        //var_dump($result_contenido);
        //echo "</pre>";
        
        if ($result_contenido) {
            //////////////
            // esto va en la confirmacion
            //q("UPDATE sai_paso_atencion SET paa_borrado=now() WHERE paa_atencion=$ate_id");

            foreach ($result_contenido as $rc) {
                $pla_asunto = $rc['pla_asunto'];
                $pla_adjunto_nombre = $rc['pla_adjunto_nombre'];

                $pla_cuerpo = $rc['pla_cuerpo'];
                $pla_adjunto_texto = $rc['pla_adjunto_texto'];
                $pla_id = $rc['pla_id'];

                $respuesta['plantilas'][$pla_id] = array();

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

                //if ($adjunto_plantilla) {

                $respuesta['plantilas'][$pla_id]['adjunto'] = $adjunto_plantilla;
                $respuesta['plantilas'][$pla_id]['campos'] = array();


                $campos_valores = array();

                if (isset($campos) && is_array($campos)) {
                    $search = array();
                    $replace = array();
                    foreach ($campos as $campo) {
                        $search[] = '%'.$campo['cae_codigo'].'%';
                        $replace[] = $campo['valor'];
                        $campos_valores['%'.$campo['cae_codigo'].'%'] = $campo['valor'];
                    }

                    $pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
                    $pla_asunto = str_replace($search, $replace, $pla_asunto);
                    $pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
                    $pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
                    $respuesta['plantilas'][$pla_id]['campos'] = $campos;
                }

                $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
                $pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;
                $pla_cuerpo = (empty($pla_cuerpo)) ? 'Favor revisar' : $pla_cuerpo;


                $respuesta['plantilas'][$pla_id]['textos'] = array($pla_cuerpo, $pla_asunto, $pla_adjunto_nombre, $pla_adjunto_texto);

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
                    $respuesta['plantilas'][$pla_id]['xls_generado'] = $xls_generado;


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
                    $respuesta['plantilas'][$pla_id]['pdf_generado'] = $pla_adjunto_nombre;

                    //////////////////////////
                    //MAIL va en confirmacion
                    //
                } catch(Exception $e) {
                    //echo $e->getMessage();
                    l($e->getMessage());
                }

                /////////////
                // va en confirmacion
                /*
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
                 */
            }
        }
        /////////////
        // va en la confirmacion
        //
        //$result = q("UPDATE sai_atencion SET ate_estado_atencion=$estado WHERE ate_id=$id RETURNING *");
    }
}

echo json_encode($respuesta);
