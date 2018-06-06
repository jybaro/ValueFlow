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

        /*
        // Obtiene el paso_atencion en base a la atencion y a la transicion, pero en caso de no tener campos no habría paso en este punto...
        $paa_id = q("
            SELECT max(paa_id)
            FROM sai_paso_atencion
            WHERE paa_borrado IS NULL
            AND paa_atencion = $ate_id
            AND paa_transicion_estado_atencion = $tea_id
        ")[0]['max'];
         * */

        $paa_id = q("
            SELECT max(paa_id)
            FROM sai_paso_atencion
            WHERE paa_borrado IS NULL
            AND NOT paa_confirmado IS NULL
            AND paa_atencion = $ate_id
            
        ")[0]['max'];

        if ($paa_id) {
            $paa_secuencial = q("
                SELECT paa_secuencial
                FROM sai_paso_atencion
                WHERE paa_borrado IS NULL
                AND paa_id = $paa_id
            ")[0]['paa_secuencial'];
        } else {
            $paa_secuencial = 0;
        }


//echo "[[1]]";
        $traer_campos_asociados = 1;
        require('_obtenerCampos.php');
        $campos = (!isset($campos) || !is_array($campos)) ? array() : $campos;
        $campos_aun_no_confirmados = $campos;

//var_dump($campos);
//echo "[[2]]";

        //Se obtienen todos los campos que pertenecen a la transición de estado
        // definida por $tea_id, al igual que sus transiciones hermanas que 
        // compartan estado actual, estado siguiente y pertinencia de proveedor,
        // es decir también trae los campos de las transiciones de todos los otros 
        // destinatarios (cliente, usuario, proveedor)

        /*
        $extender_campos_anteriores = 1;
        require('_obtenerCampos.php');
        $campos = (!isset($campos) || !is_array($campos)) ? array() : $campos;
         */
        require('_obtenerValoresVigentes.php');
        $valores_vigentes = $resultado;
        //var_dump($valores_vigentes);
//echo "[[3]]";

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

        $email_cuenta = q("
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


        $email_cliente = q("
            SELECT 
            con_correo_electronico
            FROM sai_contacto
            ,sai_atencion
            WHERE ate_borrado IS NULL
            AND con_borrado IS NULL 
            AND ate_contacto = con_id
            AND ate_id = $ate_id
        ")[0]['con_correo_electronico'];

        $email_cliente = empty($email_cliente) ? $email_cuenta : (empty($email_cuenta) ? $email_cliente : "$email_cliente,$email_cuenta");

        /*
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
         */

        $result_email= q("
            SELECT
            pep_contactos_adicionales
            ,pep_contactos_internos
            ,*
            FROM 
            sai_pertinencia_proveedor
            ,sai_atencion
            WHERE 
            pep_borrado IS NULL
            AND ate_borrado IS NULL 
            AND ate_pertinencia_proveedor = pep_id
            AND ate_id = $ate_id
        ");
        //echo "[[ate_id: $ate_id]]";
        //var_dump($result_email);
        $email_proveedor_adicionales = $result_email[0]['pep_contactos_adicionales'];
        $email_proveedor = '';
        $glue = '';
        if (!empty($email_proveedor_adicionales)) {
            $contactos_adicionales = json_decode($email_proveedor_adicionales);
            if (!empty($contactos_adicionales) && is_array($contactos_adicionales)) {
                foreach ($contactos_adicionales as $contacto_adicional) {
                    $email_proveedor .= $glue . $contacto_adicional;
                    $glue = ',';
                }
            }
        }

        $email_contactos_internos = $result_email[0]['pep_contactos_internos'];
        $cc = '';
        if (!empty($email_contactos_internos)) {
            $contactos_internos = json_decode($email_contactos_internos);
            $cc = implode(',', array_filter($contactos_internos));
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
            , 'usuario'   => implode(',', array_filter(array($email_usuario_tecnico, $email_usuario_comercial,$email_usuario_responsable)))
            ,'cc' => $cc
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
                    AND ate_contacto = con_id
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
            //datos del nodo:
            $sql_nodo = ("
                SELECT *
                ,(
                    SELECT tum_nombre
                    FROM sai_tipo_ultima_milla
                    WHERE tum_borrado IS NULL
                    AND nod_tipo_ultima_milla = tum_id
                )
                FROM sai_atencion
                ,sai_nodo
                ,sai_ubicacion
                ,sai_provincia
                ,sai_canton
                ,sai_parroquia
                ,sai_ciudad
                WHERE ate_borrado IS NULL
                AND nod_borrado IS NULL
                AND ubi_borrado IS NULL
                AND prv_borrado IS NULL
                AND can_borrado IS NULL
                AND par_borrado IS NULL
                AND ciu_borrado IS NULL
                AND ate_nodo = nod_id
                AND nod_ubicacion = ubi_id
                AND ubi_provincia = prv_id
                AND ubi_canton = can_id
                AND ubi_parroquia = par_id
                AND ubi_ciudad = ciu_id
                AND ate_id=$ate_id
            ");
            //echo "SQL NODO: $sql_nodo";
            $result_nodo = q($sql_nodo);
            //var_dump($result_nodo);
            //datos del concentrador:
            $result_concentrador = q("
                SELECT *
                ,(
                    SELECT tum_nombre
                    FROM sai_tipo_ultima_milla
                    WHERE tum_borrado IS NULL
                    AND nod_tipo_ultima_milla = tum_id
                )
                ,(
                    SELECT pep_no_diferencia_puntos
                    FROM sai_pertinencia_proveedor
                    WHERE pep_borrado IS NULL
                    AND pep_id = ate_pertinencia_proveedor
                )
                ,(
                    SELECT ate_codigo
                    FROM sai_atencion
                    WHERE ate_borrado IS NULL
                    AND ate_id = nod_atencion_referenciada
                ) AS ate_codigo_referenciada
                FROM sai_atencion
                ,sai_nodo
                ,sai_ubicacion
                ,sai_provincia
                ,sai_canton
                ,sai_parroquia
                ,sai_ciudad
                WHERE ate_borrado IS NULL
                AND nod_borrado IS NULL
                AND ubi_borrado IS NULL
                AND prv_borrado IS NULL
                AND can_borrado IS NULL
                AND par_borrado IS NULL
                AND ciu_borrado IS NULL
                AND ate_concentrador = nod_id
                AND nod_ubicacion = ubi_id
                AND ubi_provincia = prv_id
                AND ubi_canton = can_id
                AND ubi_parroquia = par_id
                AND ubi_ciudad = ciu_id
                AND ate_id=$ate_id
            ");
            //datos del extremo:
            $result_extremo = q("
                SELECT *
                ,(
                    SELECT tum_nombre
                    FROM sai_tipo_ultima_milla
                    WHERE tum_borrado IS NULL
                    AND nod_tipo_ultima_milla = tum_id
                )
                ,(
                    SELECT pep_no_diferencia_puntos
                    FROM sai_pertinencia_proveedor
                    WHERE pep_borrado IS NULL
                    AND pep_id = ate_pertinencia_proveedor
                )
                ,(
                    SELECT ate_codigo
                    FROM sai_atencion
                    WHERE ate_borrado IS NULL
                    AND ate_id = nod_atencion_referenciada
                ) AS ate_codigo_referenciada
                FROM sai_atencion
                ,sai_nodo
                ,sai_ubicacion
                ,sai_provincia
                ,sai_canton
                ,sai_parroquia
                ,sai_ciudad
                WHERE ate_borrado IS NULL
                AND nod_borrado IS NULL
                AND ubi_borrado IS NULL
                AND prv_borrado IS NULL
                AND can_borrado IS NULL
                AND par_borrado IS NULL
                AND ciu_borrado IS NULL
                AND ate_extremo = nod_id
                AND nod_ubicacion = ubi_id
                AND ubi_provincia = prv_id
                AND ubi_canton = can_id
                AND ubi_parroquia = par_id
                AND ubi_ciudad = ciu_id
                AND ate_id=$ate_id
            ");

            if ($result_metadata_atencion) {

                /*
                //primera pasada de campos con valores históricos, para tener valores po defecto:
                foreach ($campos as $campo) {
                    $campos_valores[$campo['cae_codigo']] = $campo['valor'];
                }
                //segunda pasada de campos con valores históricos, para tener la referencia:
                foreach ($campos as $campo) {
                    $campos_valores[$campo['cae_codigo'] . '_HISTORICO'] = $campo['valor'];
                }
                 */
                foreach($valores_vigentes as $valor_vigente){
                    $campos_valores[$valor_vigente['codigo']] = $valor_vigente['valor'];
                }
                foreach($valores_vigentes as $valor_vigente){
                    $campos_valores[$valor_vigente['codigo'] . '_HISTORICO'] = $valor_vigente['valor'];
                }

                //los campos no confirmados se hacen después para actualizar datos históricos:
                foreach ($campos_aun_no_confirmados as $campo) {
                    $campos_valores[$campo['cae_codigo']] = $campo['valor'];
                }

                //Agregando campos desde metadata de atencion:
                //echo "[[RESULT METADATA ATENCION]]";
                //var_dump($result_metadata_atencion);
                //$result_metadata_atencion = $result_metadata_atencion[0];
                foreach ($result_metadata_atencion[0] as $k => $v) {
                    $campos_valores[strtoupper($k)] = $v;
                }
                //var_dump($campos_valores);
                //Agregando contacto en sitio:
                //
                $ate_contacto_en_sitio = $campos_valores['ATE_CONTACTO_EN_SITIO']; 
                //echo "[[$ate_contacto_en_sitio]]";
                $result_contacto_en_sitio = q("SELECT * FROM sai_contacto WHERE con_borrado IS NULL AND con_id = $ate_contacto_en_sitio");
                if ($result_contacto_en_sitio) {
                    foreach ($result_contacto_en_sitio[0] as $k => $v) {
                        $campos_valores['CONTACTO_EN_SITIO_' . strtoupper($k)] = $v;
                    }
                }
                //var_dump($campos_valores);
                //CONTACTOS DEL CLIENTE:
                $result_contactos_cliente = q("
                    SELECT *
                    FROM sai_contacto
                    ,sai_tipo_contacto
                    WHERE con_borrado IS NULL
                    AND tco_borrado IS NULL
                    AND con_tipo_contacto = tco_id
                    AND con_cliente = (
                        SELECT ate_cliente
                        FROM sai_atencion
                        WHERE ate_borrado IS NULL
                        AND ate_id = $ate_id
                    )
                ");
                if ($result_contactos_cliente) {
                    foreach ($result_contactos_cliente as $result_contacto_cliente) {
                        $cli_contacto = 'CLI_CONTACTO_' . strtoupper($result_contacto_cliente['tco_codigo']) . '_';
                        foreach ($result_contacto_cliente as $k => $v) {
                            if (substr($k, 0, 4) == 'con_') {
                                $campos_valores[$cli_contacto . strtoupper(substr($k, 4))] = $v;
                            }
                        }
                    }
                }
                //var_dump($campos_valores);
                //CAMPOS DEL CONTRATO:

                $campos_valores['CLI_PERSONA_JURIDICA_REPRESENTANTE_LEGAL_NOMBRE'] = '';
                $campos_valores['CLI_PERSONA_JURIDICA_REPRESENTANTE_LEGAL_CARGO'] = '';

                if ($campos_valores['CLI_ES_PERSONA_JURIDICA'] == 1) {
                    $razon_social = $campos_valores['CLI_RAZON_SOCIAL'];
                    $nombre = $campos_valores['CLI_REPRESENTANTE_LEGAL_NOMBRE'];
                    $cedula = $campos_valores['CLI_REPRESENTANTE_LEGAL_CEDULA']; 
                    $email = $campos_valores['CLI_REPRESENTANTE_LEGAL_EMAIL'];
                    $domiciliado = $campos_valores['CLI_REPRESENTANTE_LEGAL_DOMICILIADO'];
                    $canton = $campos_valores['CLI_REPRESENTANTE_LEGAL_CANTON'];
                    $provincia = $campos_valores['CLI_REPRESENTANTE_LEGAL_PROVINCIA'];
                    $campos_valores['CLIENTE_CONTRATO'] = <<<EOT
$razon_social, representada por $nombre, con número de cédula/RUC $cedula, con email $email, domiciliado en $domiciliado cantón $canton, provincia $provincia
EOT;
                    $campos_valores['CLI_PERSONA_JURIDICA_REPRESENTANTE_LEGAL_NOMBRE'] = $campos_valores['CLI_REPRESENTANTE_LEGAL_NOMBRE'];
                    $campos_valores['CLI_PERSONA_JURIDICA_REPRESENTANTE_LEGAL_CARGO'] = $campos_valores['CLI_REPRESENTANTE_LEGAL_CARGO'];
                } else {
                    $razon_social = $campos_valores['CLI_RAZON_SOCIAL'];
                    //$campos_valores['CLIENTE_CONTRATO'] = "$razon_social, con número de cédula/RUC $ruc";
                    $nombre = $campos_valores['CLI_REPRESENTANTE_LEGAL_NOMBRE'];
                    $cedula = $campos_valores['CLI_RUC'];
                    $email = $campos_valores['CLI_REPRESENTANTE_LEGAL_EMAIL'];
                    $domiciliado = $campos_valores['CLI_REPRESENTANTE_LEGAL_DOMICILIADO'];
                    $canton = $campos_valores['CLI_REPRESENTANTE_LEGAL_CANTON'];
                    $provincia = $campos_valores['CLI_REPRESENTANTE_LEGAL_PROVINCIA'];
                    $campos_valores['CLIENTE_CONTRATO'] = <<<EOT
$razon_social, con número de cédula/RUC $cedula, con email $email, domiciliado en $domiciliado cantón $canton, provincia $provincia
EOT;


                    //para orden de servicio CNT, Autorizacion Central de Riesgo y Costo de Implementacion:
                    if (empty($campos_valores['CLI_REPRESENTANTE_LEGAL_CEDULA'])) {
                        $campos_valores['CLI_REPRESENTANTE_LEGAL_CEDULA'] = $campos_valores['CLI_RUC'];
                    }

                    //if ($campos_valores['CLI_REPRESENTANTE_LEGAL_NOMBRE'] == $campos_valores['CLI_RAZON_SOCIAL']) {
                    //    $campos_valores['CLI_REPRESENTANTE_LEGAL_NOMBRE'] = '';
                    //}
                }
                //CAMPOS DE LOS PUNTOS:
                if ($result_nodo) {
                    foreach($result_nodo[0] as $k => $v) {
                        $campos_valores['NODO_'.strtoupper($k)] = $v;
                    }
                }
                if ($result_concentrador) {
                    foreach($result_concentrador[0] as $k => $v) {
                        $campos_valores['CONCENTRADOR_' . strtoupper($k)] = $v;
                    }
                }

                //if (isset($campos_valores['CONCENTRADOR_PEP_NO_DIFERENCIA_PUNTOS']) && $campos_valores['CONCENTRADOR_PEP_NO_DIFERENCIA_PUNTOS'] == 1 && $campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA'] != $campos_valores['CONCENTRADOR_ATE_CODIGO']) {

                $campos_valores['CONCENTRADOR_LOGIN'] = ''; 
                if (!empty($campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA']) && $campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA'] != $campos_valores['CONCENTRADOR_ATE_CODIGO']) {
                    //$campos_valores['CONCENTRADOR_NOD_CODIGO'] = $campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA'];
                    //$campos_valores['CONCENTRADOR_UBI_DIRECCION'] = $campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA'];
                    if ($campos_valores['CONCENTRADOR_PEP_NO_DIFERENCIA_PUNTOS'] == 1) {
                        $campos_valores['CONCENTRADOR_NOD_CODIGO'] = $campos_valores['CONCENTRADOR_ATE_CODIGO_REFERENCIADA']; 
                    }
                    $campos_valores['CONCENTRADOR_LOGIN'] = $campos_valores['CONCENTRADOR_NOD_CODIGO'];

                    //$campos_valores['CONCENTRADOR_NOD_CODIGO'] = ''; 
                    //$campos_valores['CONCENTRADOR_UBI_DIRECCION'] = ''; 
                    //$campos_valores['CONCENTRADOR_UBI_SECTOR'] = '';
                    //$campos_valores['CONCENTRADOR_UBI_LONGITUD'] = '';
                    //$campos_valores['CONCENTRADOR_UBI_LATITUD'] = '';
                    //$campos_valores['CONCENTRADOR_CIU_NOMBRE'] = '';
                    //$campos_valores['CONCENTRADOR_CAN_NOMBRE'] = '';
                    //$campos_valores['CONCENTRADOR_PRV_NOMBRE'] = '';
                    //$campos_valores['CONCENTRADOR_PAR_NOMBRE'] = '';
                }

                if ($result_extremo) {
                    foreach($result_extremo[0] as $k => $v) {
                        $campos_valores['EXTREMO_' . strtoupper($k)] = $v;
                        $campos_valores['NODO_' . strtoupper($k)] = $v;
                    }
                }

                //if (isset($campos_valores['EXTREMO_PEP_NO_DIFERENCIA_PUNTOS']) && $campos_valores['EXTREMO_PEP_NO_DIFERENCIA_PUNTOS'] == 1 && $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'] != $campos_valores['EXTREMO_ATE_CODIGO']) {
                $campos_valores['EXTREMO_LOGIN'] = '';
                $campos_valores['NODO_LOGIN'] = '';
                if (!empty($campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA']) && $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'] != $campos_valores['EXTREMO_ATE_CODIGO']) {
                    //$campos_valores['EXTREMO_NOD_CODIGO'] = $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'];
                    //$campos_valores['EXTREMO_UBI_DIRECCION'] = $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'];
                    //
                    if ($campos_valores['EXTREMO_PEP_NO_DIFERENCIA_PUNTOS'] == 1) {
                        $campos_valores['EXTREMO_NOD_CODIGO'] = $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'];
                    }
                    $campos_valores['EXTREMO_LOGIN'] = $campos_valores['EXTREMO_NOD_CODIGO'];

                    //$campos_valores['EXTREMO_NOD_CODIGO'] = ''; 
                    //$campos_valores['EXTREMO_UBI_DIRECCION'] = ''; 
                    //$campos_valores['EXTREMO_UBI_SECTOR'] = '';
                    //$campos_valores['EXTREMO_UBI_LONGITUD'] = '';
                    //$campos_valores['EXTREMO_UBI_LATITUD'] = '';
                    //$campos_valores['EXTREMO_CIU_NOMBRE'] = '';
                    //$campos_valores['EXTREMO_CAN_NOMBRE'] = '';
                    //$campos_valores['EXTREMO_PRV_NOMBRE'] = '';
                    //$campos_valores['EXTREMO_PAR_NOMBRE'] = '';

                    //$campos_valores['NODO_NOD_CODIGO'] = $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'];
                    //$campos_valores['NODO_UBI_DIRECCION'] = $campos_valores['EXTREMO_ATE_CODIGO_REFERENCIADA'];
                    $campos_valores['NODO_LOGIN'] = $campos_valores['EXTREMO_NOD_CODIGO'];

                    $campos_valores['NODO_NOD_CODIGO'] = $campos_valores['EXTREMO_NOD_CODIGO']; 
                    //$campos_valores['NODO_UBI_DIRECCION'] = '';
                    //$campos_valores['NODO_UBI_SECTOR'] = '';
                    //$campos_valores['NODO_UBI_LONGITUD'] = '';
                    //$campos_valores['NODO_UBI_LATITUD'] = '';
                    //$campos_valores['NODO_CIU_NOMBRE'] = '';
                    //$campos_valores['NODO_CAN_NOMBRE'] = '';
                    //$campos_valores['NODO_PRV_NOMBRE'] = '';
                    //$campos_valores['NODO_PAR_NOMBRE'] = '';
                }



                //Agregando campos automaticos:
                $campos_valores['FECHA'] = p_formatear_fecha(null, true);
                $campos_valores['NOW'] = p_formatear_fecha();
                $campos_valores['IDENTIFICADOR'] = isset($campos_valores['IDENTIFICADOR']) ? $campos_valores['IDENTIFICADOR'] : $campos_valores['ATE_SECUENCIAL']; 
                $campos_valores['SERVICIO'] = strtoupper($campos_valores['SER_NOMBRE']);
                $campos_valores['EQUIS_DATOS'] = ($campos_valores['SERVICIO'] == 'DATOS') ? 'X' : '';
                $campos_valores['EQUIS_INTERNET'] = ($campos_valores['SERVICIO'] == 'INTERNET') ? 'X' : '';

                $campos_valores['ID'] = $campos_valores['ATE_CODIGO'];
                $campos_valores['LOGIN'] = $campos_valores['ATE_CODIGO'];
                $campos_valores['ID_SERVICIO'] = $campos_valores['ATE_CODIGO'];
                $campos_valores['LOGIN_SERVICIO'] = $campos_valores['ATE_CODIGO'];
                //$campos_valores['ID_ORDEN_SERVICIO'] = $campos_valores['ATE_CODIGO'];
                $campos_valores['ID_ORDEN_SERVICIO_LETRAS'] = is_numeric($campos_valores['ID_ORDEN_SERVICIO']) ? n2t($campos_valores['ID_ORDEN_SERVICIO']) : $campos_valores['ID_ORDEN_SERVICIO'];
                $campos_valores['ID_SERVICIO_LETRAS'] = is_numeric($campos_valores['ID_SERVICIO']) ? n2t($campos_valores['ID_SERVICIO']) : $campos_valores['ID_SERVICIO'];


                $campos_valores['IDENTIFICADOR_LETRAS'] = n2t($campos_valores['IDENTIFICADOR']);

                $iniciales = '';
                $nombre = $campos_valores['CON_NOMBRES'] . ' ' . $campos_valores['CON_APELLIDOS'];
                $nombre = explode(' ', $nombre);
                foreach ($nombre as $parte) {
                    $iniciales .= $parte[0];
                }
                $iniciales = strtoupper($iniciales);

                $campos_valores['INICIALES_CLIENTE'] = $iniciales;
                $campos_valores['ROUTER'] = 'Cisco';
                //$campos_valores['PROVEEDOR'] = $campos_valores['PRO_RAZON_SOCIAL'];
                $campos_valores['PROVEEDOR'] = $campos_valores['PRO_NOMBRE_COMERCIAL'];
                $campos_valores['CLIENTE'] = $campos_valores['CLI_RAZON_SOCIAL'];

                $campos_valores['PAA_SECUENCIAL'] = $paa_secuencial;
                $campos_valores['PAA_SECUENCIAL_LETRAS'] = n2t($paa_secuencial);









                ///////////////
                // Capacidades:
                //

                $campos_valores['CAPACIDAD'] = $campos_valores['CAPACIDAD_CONTRATADA'];
                $campos_valores['NUEVA_CAPACIDAD'] = $campos_valores['CAPACIDAD_SOLICITADA'];
                $campos_valores['CAPACIDAD_CONTRATADA_KBPS'] = $campos_valores['CAPACIDAD_CONTRATADA'] * 1024;
                $campos_valores['CAPACIDAD_FACTURADA_KBPS'] = $campos_valores['CAPACIDAD_FACTURADA'] * 1024;
                $campos_valores['CAPACIDAD_SOLICITADA_KBPS'] = $campos_valores['CAPACIDAD_SOLICITADA'] * 1024;

                //$campos_valores['FALTANTE_CAPACIDAD_CONTRATADA'] = ($campos_valores['CAPACIDAD_CONTRATADA'] < $campos_valores['CAPACIDAD_FACTURADA']) ? ($campos_valores['CAPACIDAD_FACTURADA'] - $campos_valores['CAPACIDAD_CONTRATADA']) : 0;
                $campos_valores['FALTANTE_CAPACIDAD_CONTRATADA'] = ($campos_valores['CAPACIDAD_CONTRATADA'] < $campos_valores['CAPACIDAD_SOLICITADA']) ? ($campos_valores['CAPACIDAD_SOLICITADA'] - $campos_valores['CAPACIDAD_CONTRATADA']) : 0;
                $campos_valores['FALTANTE_CAPACIDAD_FACTURADA'] = ($campos_valores['CAPACIDAD_FACTURADA'] < $campos_valores['CAPACIDAD_SOLICITADA']) ? ($campos_valores['CAPACIDAD_SOLICITADA'] - $campos_valores['CAPACIDAD_FACTURADA']) : 0;

                $campos_valores['SOBRANTE_CAPACIDAD_CONTRATADA'] = ($campos_valores['CAPACIDAD_CONTRATADA'] > $campos_valores['CAPACIDAD_SOLICITADA']) ? ($campos_valores['CAPACIDAD_CONTRATADA'] - $campos_valores['CAPACIDAD_SOLICITADA']) : 0;
                $campos_valores['SOBRANTE_CAPACIDAD_FACTURADA'] = ($campos_valores['CAPACIDAD_FACTURADA'] > $campos_valores['CAPACIDAD_SOLICITADA']) ? ($campos_valores['CAPACIDAD_FACTURADA'] - $campos_valores['CAPACIDAD_SOLICITADA']) : 0;

                $campos_valores['CAPACIDAD_ACTUAL'] = $campos_valores['CAPACIDAD_FACTURADA_HISTORICO'];
                $campos_valores['VELOCIDAD_MINIMA_EFECTIVA'] = $campos_valores['CAPACIDAD_CONTRATADA'];


                //$iva = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='iva'")[0]['cat_texto']; 
                $iva = c('iva');

                ///////////
                // Precios (cliente):
                //
                $campos_valores['PRECIO_CAPACIDAD_CONTRATADA'] = $campos_valores['CAPACIDAD_CONTRATADA'] * $campos_valores['PRECIO_MB'];
                $campos_valores['PRECIO_CAPACIDAD_FACTURADA']  = $campos_valores['CAPACIDAD_FACTURADA']  * $campos_valores['PRECIO_MB'];
                $campos_valores['PRECIO_CAPACIDAD_SOLICITADA'] = $campos_valores['CAPACIDAD_SOLICITADA'] * $campos_valores['PRECIO_MB'];

                $campos_valores['PRECIO_CAPACIDAD'] = $campos_valores['PRECIO_CAPACIDAD_CONTRATADA'];
                $campos_valores['PRECIO_MENSUAL'] = $campos_valores['PRECIO_CAPACIDAD'];
                $campos_valores['PRECIO_BW'] = $campos_valores['PRECIO_CAPACIDAD'];
                $campos_valores['PRECIO_BW_SOLICITADA'] = $campos_valores['PRECIO_CAPACIDAD_SOLICITADA'];
                $campos_valores['PRECIO_ACTUAL'] = $campos_valores['PRECIO_CAPACIDAD'];

                //reemplaza los costos de instalacion de los nodos (nodo_nod_costo_) por los de la atención (nodo_costo_):
                if (isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) || isset($campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'] = isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) ? $campos_valores['NODO_COSTO_INSTALACION_CLIENTE'] : $campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'] ;
                }
                if (isset($campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['CONCENTRADOR_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'];
                }
                $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'];

                $campos_valores['IVA_MENSUAL_PRECIO_CAPACIDAD_FACTURADA'] = round($campos_valores['PRECIO_CAPACIDAD_FACTURADA'] * $iva, 2);

                $campos_valores['TOTAL_MENSUAL_PRECIO_CAPACIDAD_FACTURADA'] = $campos_valores['IVA_MENSUAL_PRECIO_CAPACIDAD_FACTURADA'] + $campos_valores['PRECIO_CAPACIDAD_FACTURADA'];



                $campos_valores['PRECIO_INSTALACION'] = isset($campos_valores['PRECIO_INSTALACION']) ? $campos_valores['PRECIO_INSTALACION'] : ($campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'] + $campos_valores['CONCENTRADOR_NOD_COSTO_INSTALACION_CLIENTE']);
                //$campos_valores['SUBTOTAL_SERVICIO'] = $campos_valores['PRECIO_CAPACIDAD'] + $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'];
                $campos_valores['SUBTOTAL_SERVICIO'] = $campos_valores['PRECIO_CAPACIDAD_FACTURADA'] + $campos_valores['PRECIO_INSTALACION'];
                $campos_valores['IVA_SERVICIO'] = round($campos_valores['SUBTOTAL_SERVICIO'] * $iva, 2);
                $campos_valores['TOTAL_SERVICIO'] = $campos_valores['SUBTOTAL_SERVICIO'] + $campos_valores['IVA_SERVICIO'];

                $campos_valores['IVA_INSTALACION'] = round($campos_valores['PRECIO_INSTALACION'] * $iva, 2);
                $campos_valores['TOTAL_INSTALACION'] = $campos_valores['PRECIO_INSTALACION'] + $campos_valores['IVA_INSTALACION'];

                $campos_valores['IVA_MENSUAL'] = round($campos_valores['PRECIO_CAPACIDAD'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL'] = $campos_valores['PRECIO_CAPACIDAD'] + $campos_valores['IVA_MENSUAL'];

                $campos_valores['IVA_MENSUAL_SOLICITADO'] = round($campos_valores['PRECIO_CAPACIDAD_SOLICITADA'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL_SOLICITADO'] = $campos_valores['PRECIO_CAPACIDAD_SOLICITADA'] + $campos_valores['IVA_MENSUAL_SOLICITADO'];

                
                $campos_valores['PRECIO_TOTAL'] = (isset($campos_valores['CAPACIDAD_FACTURADA'])?$campos_valores['CAPACIDAD_FACTURADA'] : 0) * (isset($campos_valores['PRECIO_MB'])?$campos_valores['PRECIO_MB'] : 0);


                ///////////
                // Costos (proveedor):
                //
                $campos_valores['COSTO_CAPACIDAD_CONTRATADA'] = $campos_valores['CAPACIDAD_CONTRATADA'] * $campos_valores['COSTO_MB'];
                $campos_valores['COSTO_CAPACIDAD_FACTURADA']  = $campos_valores['CAPACIDAD_FACTURADA']  * $campos_valores['COSTO_MB'];
                $campos_valores['COSTO_CAPACIDAD_SOLICITADA'] = $campos_valores['CAPACIDAD_SOLICITADA'] * $campos_valores['COSTO_MB'];

                $campos_valores['COSTO_CAPACIDAD'] = $campos_valores['COSTO_CAPACIDAD_CONTRATADA'];
                $campos_valores['COSTO_MENSUAL'] = $campos_valores['COSTO_CAPACIDAD'];
                $campos_valores['COSTO_BW'] = $campos_valores['COSTO_CAPACIDAD'];
                $campos_valores['COSTO_BW_SOLICITADA'] = $campos_valores['COSTO_CAPACIDAD_SOLICITADA'];
                $campos_valores['COSTO_ACTUAL'] = $campos_valores['COSTO_CAPACIDAD'];

                //reemplaza los costos de instalacion de los nodos (nodo_nod_costo_) por los de la atención (nodo_costo_):
                if (isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) || isset($campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'] = isset($campos_valores['NODO_COSTO_INSTALACION_CLIENTE']) ? $campos_valores['NODO_COSTO_INSTALACION_CLIENTE'] : $campos_valores['EXTREMO_COSTO_INSTALACION_CLIENTE'] ;
                }
                if (isset($campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'])) {
                    $campos_valores['CONCENTRADOR_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['CONCENTRADOR_COSTO_INSTALACION_CLIENTE'];
                }
                $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'] = $campos_valores['EXTREMO_NOD_COSTO_INSTALACION_CLIENTE'];

                $campos_valores['COSTO_INSTALACION'] = isset($campos_valores['COSTO_INSTALACION']) ? $campos_valores['COSTO_INSTALACION'] : ($campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'] + $campos_valores['CONCENTRADOR_NOD_COSTO_INSTALACION_CLIENTE']);
                //$campos_valores['SUBTOTAL_SERVICIO'] = $campos_valores['COSTO_CAPACIDAD'] + $campos_valores['NODO_NOD_COSTO_INSTALACION_CLIENTE'];
                $campos_valores['SUBTOTAL_SERVICIO_COSTO'] = $campos_valores['COSTO_CAPACIDAD_CONTRATADA'] + $campos_valores['COSTO_INSTALACION'];
                $campos_valores['IVA_SERVICIO_COSTO'] = round($campos_valores['SUBTOTAL_SERVICIO_COSTO'] * $iva, 2);
                $campos_valores['TOTAL_SERVICIO_COSTO'] = $campos_valores['SUBTOTAL_SERVICIO_COSTO'] + $campos_valores['IVA_SERVICIO_COSTO'];

                $campos_valores['IVA_INSTALACION_COSTO'] = round($campos_valores['COSTO_INSTALACION'] * $iva, 2);
                $campos_valores['TOTAL_INSTALACION_COSTO'] = $campos_valores['COSTO_INSTALACION'] + $campos_valores['IVA_INSTALACION_COSTO'];

                $campos_valores['IVA_MENSUAL_COSTO'] = round($campos_valores['COSTO_CAPACIDAD'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL_COSTO'] = $campos_valores['COSTO_CAPACIDAD'] + $campos_valores['IVA_MENSUAL_COSTO'];

                $campos_valores['IVA_MENSUAL_SOLICITADO_COSTO'] = round($campos_valores['COSTO_CAPACIDAD_SOLICITADA'] * $iva, 2);
                $campos_valores['IVA_MENSUAL_FACTURADO_COSTO'] = round($campos_valores['COSTO_CAPACIDAD_FACTURADA'] * $iva, 2);
                $campos_valores['TOTAL_MENSUAL_SOLICITADO_COSTO'] = $campos_valores['COSTO_CAPACIDAD_SOLICITADA'] + $campos_valores['IVA_MENSUAL_SOLICITADO_COSTO'];
                $campos_valores['TOTAL_MENSUAL_FACTURADO_COSTO'] = $campos_valores['COSTO_CAPACIDAD_FACTURADA'] + $campos_valores['IVA_MENSUAL_FACTURADO_COSTO'];

                
                $campos_valores['COSTO_TOTAL'] = (isset($campos_valores['CAPACIDAD_CONTRATADA'])?$campos_valores['CAPACIDAD_CONTRATADA'] : 0) * (isset($campos_valores['COSTO_MB'])?$campos_valores['COSTO_MB'] : 0);

                //var_dump($campos_valores);
                //foreach($campos_valores as $k => $v){
                //    echo "$k|";
                //}

                foreach ($result_contenido as $rc) {
                    //$tea_id = $rc['tea_id'];

                    $pla_asunto = $rc['pla_asunto'];
                    
                    //$pla_asunto = ($pla_asunto == 'null') ? 'Notificación' : $pla_asunto;
                    $pla_asunto = ($pla_asunto == 'null') ? '' : $pla_asunto;
                    
                    $pla_adjunto_nombre = $rc['pla_adjunto_nombre'];

                    $pla_cuerpo = $rc['pla_cuerpo'];
                    //$pla_cuerpo = ($pla_cuerpo == 'null') ? 'Favor revisar.' : $pla_cuerpo;
                    $pla_cuerpo = ($pla_cuerpo == 'null') ? '' : $pla_cuerpo;

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

                    $search = array();
                    $replace = array();
                    foreach($campos_valores as $c => $v) {
                        $search[] = '${' . $c . '}';
                        $replace[] = $v;
                    }

                    //pla_cuerpo
                    //$pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
                    $pla_cuerpo = p_reemplazar_campos_valores($pla_cuerpo);

                    //$pla_asunto = str_replace($search, $replace, $pla_asunto);
                    $pla_asunto = p_reemplazar_campos_valores($pla_asunto);

                    //$pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
                    $pla_adjunto_nombre = p_reemplazar_campos_valores($pla_adjunto_nombre);

                    //$pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
                    $pla_adjunto_texto = p_reemplazar_campos_valores($pla_adjunto_texto);

                    $respuesta['plantillas'][$pla_id]['campos'] = $campos;

                    $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
                    $pla_adjunto_nombre = limpiar_nombre_archivo($pla_adjunto_nombre);

                    //$pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;
                    $pla_asunto = (empty($pla_asunto)) ? '' : $pla_asunto;
                    //$pla_cuerpo = (empty($pla_cuerpo)) ? 'Favor revisar atención '.$campos_valores['IDENTIFICADOR'] : $pla_cuerpo;
                    $pla_cuerpo = (empty($pla_cuerpo)) ? '' : $pla_cuerpo;


                    $respuesta['plantillas'][$pla_id]['textos'] = array($pla_cuerpo, $pla_asunto, $pla_adjunto_nombre, $pla_adjunto_texto);


                    $respuesta['plantillas'][$pla_id]['adjuntos_generados'] = array(); 
                    $xls_generado = false;

                    $ate_dirname = md5($ate_id . 'ate_SAIT');
                    $paa_dirname = md5($paa_id . 'paa_SAIT');

                    if (!file_exists('archivos/')) {
                        mkdir('archivos', 0777);
                    }

                    if (!file_exists('archivos/' . $ate_dirname . '/')) {
                        mkdir('archivos/' . $ate_dirname, 0777);
                    }


                    if (!file_exists('archivos/' . $ate_dirname . '/' . $paa_dirname . '/')) {
                        mkdir('archivos/' . $ate_dirname . '/' . $paa_dirname, 0777);
                    }

                    $dirname = 'archivos/' . $ate_dirname . '/' . $paa_dirname . '/';

                    if ($adjuntos_plantilla) {
                        foreach ($adjuntos_plantilla as $adjunto_plantilla) {
                            try {
                                //$adjunto_plantilla = $adjunto_plantilla[0];
                                
                                //$nombre = $pla_adjunto_nombre;
                                //$nombre = $nombre . '-' . random_int(100000, 999999);
                                
                                $nombre = $adjunto_plantilla['arc_nombre'];
                                $nombre = p_reemplazar_campos_valores($nombre);
                                $nombre = (empty($nombre)) ? 'adjunto' : $nombre;
                                $nombre = limpiar_nombre_archivo($nombre);
                                //$nombre = pathinfo($nombre, PATHINFO_FILENAME) . '-' . random_int(100000, 999999);
                                $nombre = pathinfo($nombre, PATHINFO_FILENAME);

                                $ext = strtolower(pathinfo($adjunto_plantilla['arc_nombre'], PATHINFO_EXTENSION));
                                $ruta_plantilla = 'uploads/' . $adjunto_plantilla['arc_nombre'];
                                if (file_exists($ruta_plantilla)) {
                                    if ($ext == 'xls' || $ext == 'xlsx' || $ext == 'ods') {
                                        //////////////
                                        //Excel

                                        //echo "sacando Excel";

                                        //\PhpOffice\PhpSpreadsheet\Settings::setOutputEscapingEnabled(true);
                                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta_plantilla);

                                        $worksheet = $spreadsheet->getActiveSheet();

                                        $filas = $worksheet->toArray();

                                        //var_dump($filas);
                                        foreach($filas as $x => $fila){
                                            foreach($fila as $y => $celda){
                                                if (!empty($celda)) {
                                                    //echo "[$x, $y: $celda]";
                                                    $nuevo_valor = p_reemplazar_campos_valores($celda);
                                                    //$nuevo_valor = htmlspecialchars($nuevo_valor);

                                                    /*
                                                    if (preg_match_all('/\$\{([a-zA-Z0-9_]+)\}/', $celda, $matches)){
                                                        //var_dump($matches);
                                                        foreach ($matches[0] as $k => $match) {
                                                            $campo_codigo = $matches[1][$k];
                                                            $valor = $campos_valores[$campo_codigo];
                                                            $nuevo_valor = str_replace($match, $valor, $nuevo_valor);
                                                            //echo "[$campo_codigo]";
                                                        }
                                                        //echo " --[[$nuevo_valor]]--";

                                                        //$nuevo_valor = (isset($campos_valores[$celda])) ? $campos_valores[$celda] : 'Dato no definido';
                                                    }
                                                     */
                                                    //$nuevo_valor = str_replace('&', ' and ', $nuevo_valor);
                                                    $worksheet->setCellValueByColumnAndRow($y+1, $x+1, $nuevo_valor);
                                                }
                                            }
                                        }

                                        //$worksheet->getCell('A1')->setValue('John');
                                        //$worksheet->getCell('A2')->setValue('Smith');

                                        $nombre = $nombre . '.xlsx';
                                        //echo " [[NOMBRE: $nombre]]";
                                        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                                        $writer->setPreCalculateFormulas(true); 
                                        $writer->save($dirname . $nombre);
                                        $xls_generado = true;

                                        //} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'odt') { //no funciona con .doc, sale este error:  
                                        //                        ZipArchive::getFromName(): Invalid or uninitialized Zip object
                                    } else if ($ext == 'docx' || $ext == 'odt') {
                                        //echo "[EXT:$ext]";
                                        ////////////
                                        // Word
                                        //$doc = \PhpOffice\PhpWord\IOFactory::load($ruta_plantilla);
                                        //OBTIENE CAMPOS:
                                        /*
                                        $phpWordReader = \PhpOffice\PhpWord\IOFactory::createReader('Word2007');
                                        if($phpWordReader->canRead($ruta_plantilla)) {
                                            $phpWord = $phpWordReader->load($ruta_plantilla);
                                        }
                                         */

                                        //PLANTILLA WORD
                                        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
                                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($ruta_plantilla);

                                        foreach ($campos_valores as $campo => $valor) {
                                            //echo "[$campo: $valor]";
                                            $templateProcessor->setValue($campo, $valor);
                                        }

                                        $nombre = $nombre .'.docx';
                                        //echo " -[$nombre]- ";

                                        // $writer = \PhpOffice\PhpWord\IOFactory::createWriter($doc, 'Word2007');
                                        // $writer->save($pla_adjunto_nombre);
                                        $templateProcessor->saveAs($dirname . $nombre);
                                        $xls_generado = true;
                                    } else {
                                        //cualquier otro tipo de archivo se pasa como está, sin ninguna modificación
                                        $nombre = $nombre . '.' . $ext;
                                        $result_copy = copy($ruta_plantilla, $dirname.$nombre);
                                        if ($result_copy) {
                                            l('no se pudo copiar el archivo ' . $ruta_plantilla);
                                        }
                                    }
                                    $respuesta['plantillas'][$pla_id]['adjuntos_generados'][] = $dirname . $nombre;
                                } else {
                                    l('No existe el archivo plantilla: ' . $ruta_plantilla);
                                }
                            } catch(Exception $e) {
                                //echo '<div>ERROR EN LOS ARCHIVOS: ' . $e->getMessage() . '</div>';
                                l($e->getMessage());
                            }
                        }
                    }
                    $respuesta['plantillas'][$pla_id]['xls_generado'] = $xls_generado;

                    try {

                        //////////////
                        //PDF
                        if (!empty($pla_adjunto_texto) && $pla_adjunto_texto != 'null') {
                            //if (file_exists('adjunto.html')) {
                            //    unlink('adjunto.html');
                            //}
                            $nombre = $pla_adjunto_nombre;
                            //$nombre = $nombre . '-' . random_int(100000, 999999);
                            $nombre = $nombre . '.pdf';

                            if (file_exists($nombre)) {
                                unlink($nombre);
                            }

                            $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
                            $msg = ($pla_adjunto_texto);
                            //file_put_contents( 'adjunto.html', $msg);
                            //$msg = file_get_contents('adjunto.html');
                            //$msg = utf8_decode($msg);
                            $snappy->generateFromHtml($msg, $dirname . $nombre, array('encoding' => 'utf-8'));
                            $respuesta['plantillas'][$pla_id]['adjuntos_generados'][] = $dirname . $nombre;
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
