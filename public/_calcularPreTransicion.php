<?php


//var_dump($_POST);

$respuesta = array();
if (isset($args) && !empty($args) && isset($args[0]) && !empty($args[0])) {
    $ate_id = $args[0];

    if (isset($_POST['estado']) && !empty($_POST['estado'])) {
        $estado = $_POST['estado'];
        $id = $_POST['id'];
        $ate_id = $id;
        //$tea_id = $_POST['tea_id'];
        $tea_id = $_POST['tea_id'];
        $traer_campos_asociados = 1;
        $extender_campos_anteriores = 1;

        //Se obtienen todos los campos que pertenecen a la transición de estado
        // definida por $tea_id, al igual que sus transiciones hermanas que 
        // compartan estado actual, estado siguiente y pertinencia de proveedor,
        // es decir también trae los campos de las transiciones de todos los otros 
        // destinatarios (cliente, usuario, proveedor)

        require('_obtenerCampos.php');
        $campos = (!isset($campos) || !is_array($campos)) ? array() : $campos;

//echo "[$sql]";
//var_dump($campos);

        $result_contenido = q("
            SELECT * 
            ,(
                SELECT esa_nombre
                FROM sai_estado_atencion
                WHERE esa_id = tea_estado_atencion_actual
            ) AS actual
            ,(
                SELECT esa_nombre
                FROM sai_estado_atencion
                WHERE esa_id = tea_estado_atencion_siguiente
            ) AS siguiente 
            ,(
                SELECT 
                des_nombre 
                FROM sai_destinatario 
                WHERE des_id = tea_destinatario
            ) AS destinatario
            FROM sai_transicion_estado_atencion 
            ,sai_plantilla
            WHERE tea_borrado IS NULL
            AND pla_borrado IS NULL
            AND pla_transicion_estado_atencion = tea_id
            AND tea_id IN (
                SELECT tea_id
                FROM sai_transicion_estado_atencion
                WHERE tea_borrado IS NULL
                AND tea_estado_atencion_actual = (
                    SELECT tea_estado_atencion_actual 
                    FROM sai_transicion_estado_atencion 
                    WHERE tea_id = $tea_id
                )
                AND tea_estado_atencion_siguiente = (
                    SELECT tea_estado_atencion_siguiente 
                    FROM sai_transicion_estado_atencion 
                    WHERE tea_id = $tea_id
                )
                AND tea_pertinencia_proveedor = (
                    SELECT tea_pertinencia_proveedor 
                    FROM sai_transicion_estado_atencion 
                    WHERE tea_id = $tea_id
                )
            )
        ");


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
            AND ate_id = $ate_id
        ")[0]['con_correo_electronico'];

        $email_proveedor = q("
            SELECT
            vpr_correo_electronico
            FROM sai_vendedor_proveedor
            ,sai_pertinencia_proveedor
            ,sai_atencion
            WHERE vpr_borrado IS NULL
            AND pep_borrado IS NULL
            AND ate_borrado IS NULL 
            AND ate_pertinencia_proveedor = pep_id
            AND pep_vendedor_proveedor = vpr_id
            AND ate_id = $ate_id
        ")[0]['vpr_correo_electronico'];

        $email_proveedor_adicionales = q("
            SELECT
            pep_contactos_adicionales
            FROM 
            sai_pertinencia_proveedor
            ,sai_atencion
            WHERE 
            pep_borrado IS NULL
            AND ate_borrado IS NULL 
            AND ate_pertinencia_proveedor = pep_id
            AND ate_id = $ate_id
        ")[0]['pep_contactos_adicionales'];
        if (!empty($email_proveedor_adicionales)) {
            $contactos_adicionales = json_decode($email_proveedor_adicionales);
            if (!empty($contactos_adicionales) && is_array($contactos_adicionales)) {
                foreach ($contactos_adicionales as $contacto_adicional) {
                    $email_proveedor .= ',' . $contacto_adicional;
                }
            }
        }

        $email_usuario_tecnico = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_atencion
            WHERE usu_borrado IS NULL
            AND ate_borrado IS NULL 
            AND ate_usuario_tecnico = usu_id
            AND ate_id = $ate_id
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

        $email_usuario_responsable = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_transicion_estado_atencion
            WHERE usu_borrado IS NULL
            AND tea_borrado IS NULL
            AND tea_usuario = usu_id
            AND tea_id = $tea_id
        ")[0]['usu_correo_electronico'];

        //echo "[$email_cliente - $email_proveedor - $email_usuario]";
        $respuesta['emails'] = array(
            'cliente'     => $email_cliente
            , 'proveedor' => $email_proveedor
            , 'usuario'   => $email_usuario_tecnico . ',' . $email_usuario_comercial . ',' . $email_usuario_responsable
        );
        /*
        $sql = ("
            SELECT *
            ,(
                SELECT 
                des_nombre 
                FROM sai_destinatario 
                WHERE des_id = tea_destinatario
            ) AS destinatario
            FROM sai_atencion
            ,sai_paso_atencion
            ,sai_transicion_estado_atencion
            ,sai_plantilla
            WHERE ate_borrado IS NULL
            AND paa_borrado IS NULL
            AND tea_borrado IS NULL
            AND pla_borrado IS NULL
            AND ate_id = paa_atencion
            AND paa_paso_anterior IS NULL
            AND paa_transicion_estado_atencion = tea_id
            AND pla_transicion_estado_atencion = tea_id
            AND ate_id = $ate_id
        ");
         */

        $respuesta['contenido'] = $result_contenido;
        $respuesta['plantillas'] = array();

        //echo "<pre>";
        //echo "$sql<hr>"; 
        //var_dump($result_contenido);
        //echo "</pre>";
        
        if ($result_contenido) {
            //////////////
            // esto va en la confirmacion
            //q("UPDATE sai_paso_atencion SET paa_borrado=now() WHERE paa_atencion=$ate_id");
            $sql = ("
                SELECT *
                ,(usu_tecnico.usu_nombres || ' ' || usu_tecnico.usu_apellidos) AS usuario_tecnico
                ,(usu_tecnico.usu_correo_electronico) AS usuario_tecnico_correo_electronico
                ,(usu_comercial.usu_nombres || ' ' || usu_comercial.usu_apellidos) AS usuario_comercial
                ,(usu_comercial.usu_correo_electronico) AS usuario_comercial_correo_electronico
                FROM sai_atencion
                
                LEFT OUTER JOIN sai_cliente
                    ON cli_borrado IS NULL
                    AND ate_cliente = cli_id
                LEFT OUTER JOIN sai_cuenta
                    ON cue_borrado IS NULL
                    AND ate_cuenta = cue_id
                LEFT OUTER JOIN sai_contacto
                    ON con_borrado IS NULL
                    AND cue_contacto = con_id
                LEFT OUTER JOIN sai_pertinencia_proveedor
                    ON pep_borrado IS NULL
                    AND ate_pertinencia_proveedor = pep_id
                LEFT OUTER JOIN sai_proveedor
                    ON pro_borrado IS NULL
                    AND pep_proveedor = pro_id
                LEFT OUTER JOIN sai_usuario AS usu_tecnico
                    ON usu_tecnico.usu_borrado IS NULL
                    AND ate_usuario_tecnico = usu_tecnico.usu_id
                LEFT OUTER JOIN sai_usuario AS usu_comercial
                    ON usu_comercial.usu_borrado IS NULL
                    AND ate_usuario_comercial = usu_comercial.usu_id
                LEFT OUTER JOIN sai_servicio
                    ON ser_borrado IS NULL
                    AND ate_servicio = ser_id
                LEFT OUTER JOIN sai_estado_atencion
                    ON esa_borrado IS NULL
                    AND ate_estado_atencion = esa_id
                WHERE ate_id = $ate_id
                    AND ate_borrado IS NULL

            ");
            $result_metadata_atencion = q($sql);
            if ($result_metadata_atencion) {

                foreach ($result_contenido as $rc) {
                    //$tea_id = $rc['tea_id'];
                    $pla_asunto = $rc['pla_asunto'];
                    $pla_adjunto_nombre = $rc['pla_adjunto_nombre'];

                    $pla_cuerpo = $rc['pla_cuerpo'];
                    $pla_adjunto_texto = $rc['pla_adjunto_texto'];
                    $pla_id = $rc['pla_id'];

                    $respuesta['plantillas'][$pla_id] = array();

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
                    $adjuntos_plantilla = q($sql);

                    $respuesta['plantillas'][$pla_id]['adjuntos'] = $adjuntos_plantilla;
                    $respuesta['plantillas'][$pla_id]['campos'] = array();

                    $campos_valores = array();

                    $search = array();
                    $replace = array();
                    foreach ($campos as $campo) {
                        $search[] = '%'.$campo['cae_codigo'].'%';
                        $replace[] = $campo['valor'];
                        $campos_valores['%'.$campo['cae_codigo'].'%'] = $campo['valor'];
                    }
                    //Agregando campos desde metadata de atencion:
                    //echo "[[RESULT METADATA ATENCION]]";
                    //var_dump($result_metadata_atencion);
                    //$result_metadata_atencion = $result_metadata_atencion[0];
                    foreach ($result_metadata_atencion as $k => $v) {
                        $campos_valores['%' . strtoupper($k) . '%'] = $v;
                    }
                    if ($result_metadata_atencion[cli_es_persona_juridica] == 1) {
                        $razon_social = $result_metadata_atencion[cli_razon_social];
                        $nombre = $result_metadata_atencion[cli_representante_legal_nombre];
                        $cedula = $result_metadata_atencion[cli_representante_legal_cedula];
                        $email = $result_metadata_atencion[cli_representante_legal_email];
                        $domiciliado = $result_metadata_atencion[cli_representante_legal_domiciliado];
                        $canton = $result_metadata_atencion[cli_representante_legal_canton];
                        $provincia = $result_metadata_atencion[cli_representante_legal_provincia];
                        $campos_valores['%'.'CLIENTE_CONTRATO'.'%'] = <<<EOT
$razon_social, representada por $nombre, con número de cédula/RUC $cedula, con email $email, domiciliado en $domiciliado cantón $canton, provincia $provincia
EOT;
                    } else {
                        $razon_social = $result_metadata_atencion[cli_razon_social];
                        $ruc = $result_metadata_atencion[cli_ruc];
                        $campos_valores['%'.'CLIENTE_CONTRATO'.'%'] = "$razon_social, con número de cédula/RUC $ruc";
                    }
                    //Agregando campos automaticos:
                    $campos_valores['%'.'FECHA'.'%'] = p_formatear_fecha(date("Y-m-d H:i:s"));
                    $campos_valores['%'.'NOW'.'%'] = p_formatear_fecha(date("Y-m-d H:i:s"));

                    $pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
                    $pla_asunto = str_replace($search, $replace, $pla_asunto);
                    $pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
                    $pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
                    $respuesta['plantillas'][$pla_id]['campos'] = $campos;

                    $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
                    $pla_adjunto_nombre = limpiar_nombre_archivo($pla_adjunto_nombre);

                    $pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;
                    $pla_cuerpo = (empty($pla_cuerpo)) ? 'Favor revisar' : $pla_cuerpo;


                    $respuesta['plantillas'][$pla_id]['textos'] = array($pla_cuerpo, $pla_asunto, $pla_adjunto_nombre, $pla_adjunto_texto);


                    $respuesta['plantillas'][$pla_id]['adjuntos_generados'] = array(); 
                    $xls_generado = false;
                    try {
                        if ($adjuntos_plantilla) {
                            foreach ($adjuntos_plantilla as $adjunto_plantilla) {
                                //$adjunto_plantilla = $adjunto_plantilla[0];
                                $nombre = $pla_adjunto_nombre;
                                $nombre = $nombre . '-' . random_int(100000, 999999);
                                $ext = strtolower(pathinfo($adjunto_plantilla['arc_nombre'], PATHINFO_EXTENSION));
                                $ruta_plantilla = 'uploads/' . $adjunto_plantilla['arc_nombre'];
                                if ($ext == 'xls' || $ext == 'xlsx' || $ext == 'ods') {
                                    //////////////
                                    //Excel

                                    //echo "sacando Excel";

                                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta_plantilla);

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

                                    $nombre = $nombre . '.xls';
                                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
                                    $writer->save($nombre);
                                    $xls_generado = true;

                                //} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'odt') { //no funciona con .doc, sale este error:  
                                //                        ZipArchive::getFromName(): Invalid or uninitialized Zip object
                                } else if ($ext == 'docx' || $ext == 'odt') {
                                    ////////////
                                    // Word
                                    //$doc = \PhpOffice\PhpWord\IOFactory::load($ruta_plantilla);
                                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($ruta_plantilla);

                                    foreach ($campos_valores as $campo => $valor) {
                                        $templateProcessor->setValue($campo, $valor);
                                    }

                                    $nombre = $nombre .'.docx';

                                    // $writer = \PhpOffice\PhpWord\IOFactory::createWriter($doc, 'Word2007');
                                    // $writer->save($pla_adjunto_nombre);
                                    $templateProcessor->saveAs($nombre);
                                    $xls_generado = true;
                                } else {
                                    //cualquier otro tipo de archivo se pasa como está, sin ninguna modificación
                                    $nombre = $nombre . '.' . $ext;
                                    $result_copy = copy($ruta_plantilla, $nombre);
                                    if ($result_copy) {
                                        l('no se pudo copiar el archivo ' . $ruta_plantilla);
                                    }
                                }
                                $respuesta['plantillas'][$pla_id]['adjuntos_generados'][] = $nombre;
                            }
                        }
                        $respuesta['plantillas'][$pla_id]['xls_generado'] = $xls_generado;


                        //////////////
                        //PDF
                        if (!empty($pla_adjunto_texto)) {
                            //if (file_exists('adjunto.html')) {
                            //    unlink('adjunto.html');
                            //}
                            $nombre = $pla_adjunto_nombre;
                            $nombre = $nombre . '-' . random_int(100000, 999999);
                            $nombre = $nombre . '.pdf';

                            if (file_exists($nombre)) {
                                unlink($nombre);
                            }

                            $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
                            $msg = ($pla_adjunto_texto);
                            //file_put_contents( 'adjunto.html', $msg);
                            //$msg = file_get_contents('adjunto.html');
                            //$msg = utf8_decode($msg);
                            $snappy->generateFromHtml($msg, $nombre, array('encoding' => 'utf-8'));
                            $respuesta['plantillas'][$pla_id]['adjuntos_generados'][] = $nombre;
                        }
                        $respuesta['plantillas'][$pla_id]['pdf_generado'] = $nombre;

                    } catch(Exception $e) {
                        //echo '<div>ERROR EN LOS ARCHIVOS: ' . $e->getMessage() . '</div>';
                        l($e->getMessage());
                    }
                }
            }
        }
    }
}

echo json_encode($respuesta);
