<?php

//var_dump($_POST);

$ate_id = $_POST['ate_id'];

$paa_id = "(
    SELECT paa_id 
    FROM sai_paso_atencion 
    WHERE paa_borrado IS NULL 
    AND paa_paso_anterior IS NULL
    AND paa_confirmado IS NULL
    AND paa_atencion=$ate_id
    )";
$result = q($paa_id);
if (!$result) {
    //no hay paso para la atencion
    //Trata de obtener una transicion para asociarla al paso que se va a crear:
    $result_tea_id = q("
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_estado_atencion_actual = (
            SELECT ate_estado_atencion 
            FROM sai_atencion 
            WHERE ate_id = $ate_id
        )
        AND tea_pertinencia_proveedor = (
            SELECT ate_pertinencia_proveedor 
            FROM sai_atencion
            WHERE ate_id = $ate_id
        )
    ");
    $tea_id = 'null';
    if ($result_tea_id) {
        $tea = array();
        foreach ($result_tea_id as $r) {
            if (!isset($tea[$r[tea_estado_atencion_siguiente]])) {
                $tea[$r[tea_estado_atencion_siguiente]] = array();
            }
            $tea[$r[tea_estado_atencion_siguiente]][$r[tea_destinatario]] = $r;
        }
        if (count($tea) == 1) {
            //si solo existe un estado siguiente posible para el estado de la atención: 
            foreach ($tea as $tea_estado_atencion_siguiente => $siguiente) {
                foreach ($siguiente as $tea_destinatario => $destinatario) {
                    //coje el tea del último destinatario:
                    $tea_id = $destinatario[tea_id];
                }
            }
        }
    }
    //crea el paso:
    $paa_id = q("
        INSERT INTO sai_paso_atencion (
            paa_transicion_estado_atencion
            , paa_atencion
            , paa_creado_por
        ) VALUES (
            $tea_id
            , $ate_id
            , {$_SESSION['usu_id']}
        ) RETURNING *
    ")[0][paa_id];

} else if (count($result) > 1) {
    /*
    q("
        UPDATE sai_paso_atencion 
        SET paa_borrado = now() 
        WHERE paa_atencion = $ate_id 
        AND paa_id <> (
            SELECT MAX(paa_id) 
            FROM sai_paso_atencion 
            WHERE paa_atencion=$ate_id
        )
    ");
     */
    q("
        UPDATE sai_paso_atencion 
        SET paa_paso_anterior = now() 
        WHERE paa_atencion = $ate_id 
        AND paa_id <> (
            SELECT MAX(paa_id) 
            FROM sai_paso_atencion 
            WHERE paa_atencion=$ate_id
        )
    ");
    $paa_id = "(
        SELECT paa_id 
        FROM sai_paso_atencion 
        WHERE paa_borrado IS NULL 
        AND paa_paso_anterior IS NULL
        AND paa_atencion = $ate_id
    )";
}
//echo $paa_id;
$respuesta = array();
foreach ($_POST as $k => $v){
    //$v = pg_escape_string($v);

    if ($k != 'ate_id') {
        //$cae_id = "(SELECT cae_id FROM sai_campo_extra WHERE cae_codigo='$k')";
        $cae_id = str_replace('campo_extra_', '', $k);
        q("
            UPDATE sai_valor_extra 
            SET vae_borrado = now() 
            WHERE vae_borrado IS NULL 
            AND vae_campo_extra = $cae_id 
            AND vae_paso_atencion = $paa_id
        ");

        $vae_texto = 'null';
        $vae_numero = 'null';
        $vae_fecha = 'null';
        $vae_nodo = 'null';
        $vae_ciudad = 'null';
        $vae_conexion = 'null';

        if ($v === '0' || (!empty($v) && $v != 'null')) {
            $result_cae = q("
                SELECT *
                FROM sai_campo_extra
                ,sai_tipo_dato
                WHERE cae_borrado IS NULL
                AND tid_borrado IS NULL
                AND cae_tipo_dato = tid_id
                AND cae_id = $cae_id
            ");

            if ($result_cae) {

                $cae = $result_cae[0];
                switch ($cae[tid_codigo]) {
                case 'texto': default:
                    $vae_texto = p_formatear_valor_sql($v, 'text');
                    break;
                case 'numero':
                    $vae_numero = p_formatear_valor_sql($v, 'number');
                    break;
                case 'fecha':
                    $vae_fecha = p_formatear_valor_sql($v, 'timestamp');
                    break;
                case 'ciudad':
                    $vae_ciudad = p_formatear_valor_sql($v, 'ciudad');
                    break;
                case 'nodo': case 'nodo_completo':
                    $vae_nodo = p_formatear_valor_sql($v, 'nodo');
                    $campo_nodo = 'nodo';
                    if ($cae[cae_validacion] == 'concentrador' || $cae[cae_validacion] == 'extremo') {
                        $campo_nodo = $cae[cae_validacion];
                    }
                    //verifica si es nuevo nodo, o si es referencia a uno existente y se lo debe duplicar:
                    $result_nodo = q("
                        SELECT *
                        FROM sai_nodo

                        LEFT OUTER JOIN sai_atencion
                            ON ate_borrado IS NULL
                            AND nod_atencion = ate_id

                        LEFT OUTER JOIN sai_estado_atencion
                            ON esa_borrado IS NULL
                            AND esa_id = ate_estado_atencion
                            AND (
                                esa_nombre ILIKE '%servicio activo%'
                                OR esa_nombre ILIKE '%servicio suspendido%'
                                OR esa_nombre ILIKE '%incremento%'
                                OR esa_nombre ILIKE '%decremento%'
                                OR esa_nombre ILIKE '%suspensión%'
                            )

                        WHERE nod_borrado IS NULL
                        AND nod_id = $vae_nodo
                    ");
                    if ($result_nodo) {
                        $nodo = $result_nodo[0];
                        if ($nodo['nod_atencion'] != $ate_id) {
                            //si la atencion del nodo no es la actual atención, se lo debe duplicar:

                            //$atencion_referenciada = 'null';
                            $atencion_referenciada = $ate_id;

                            $costo_instalacion_proveedor = 'nod_costo_instalacion_proveedor';
                            $costo_instalacion_cliente = 'nod_costo_instalacion_cliente';

                            if (!empty($nodo['esa_nombre'])) {
                                //Si es servicio activo, se guarda la referencia a la atencion:
                                $atencion_referenciada = $nodo['nod_atencion'];
                                //$atencion_referenciada = $ate_id;

                                //Si es servicio activo, no hay costos de instalacion:
                                $costo_instalacion_proveedor = 0;
                                $costo_instalacion_cliente = 0;
                            }

                            $sql = ("
                                INSERT INTO sai_nodo(
                                    nod_codigo
                                    ,nod_descripcion
                                    ,nod_ubicacion
                                    ,nod_creado_por
                                    ,nod_atencion
                                    ,nod_costo_instalacion_proveedor
                                    ,nod_costo_instalacion_cliente
                                    ,nod_tipo_ultima_milla
                                    ,nod_responsable_ultima_milla
                                    ,nod_distancia
                                    ,nod_fecha_termino
                                    ,nod_nodo 
                                    ,nod_duplicado_desde
                                    ,nod_atencion_referenciada
                                ) SELECT
                                    nod_codigo
                                    ,nod_descripcion
                                    ,nod_ubicacion
                                    ,nod_creado_por
                                    ,$ate_id
                                    ,$costo_instalacion_proveedor
                                    ,$costo_instalacion_cliente
                                    ,nod_tipo_ultima_milla
                                    ,nod_responsable_ultima_milla
                                    ,nod_distancia
                                    ,nod_fecha_termino
                                    ,nod_nodo 
                                    ,{$nodo[nod_id]}
                                    ,$atencion_referenciada
                                FROM sai_nodo
                                WHERE nod_borrado IS NULL
                                AND nod_id = {$nodo[nod_id]} 
                                RETURNING *
                            ");
                            echo $sql;
                            $result_nuevo_nodo = q($sql);

                            if ($result_nuevo_nodo) {
                                $nuevo_nodo = $result_nuevo_nodo[0];
                                $vae_nodo = $nuevo_nodo['nod_id'];
                            }
                        }
                    }
                    


                    //actualiza la atencion con el nodo

                    $sql = ("
                        UPDATE sai_atencion
                        SET 
                        ate_$campo_nodo = $vae_nodo
                        WHERE ate_borrado IS NULL
                        AND ate_id = $ate_id
                    ");
                    //echo $sql;
                    q($sql);
                    break;
                case 'conexion':
                    $vae_conexion = p_formatear_valor_sql($v, 'nodo');
                    q("
                        UPDATE sai_atencion
                        SET ate_conexion = $vae_conexion
                        WHERE ate_borrado IS NULL
                        AND ate_id = $ate_id
                    ");
                    break;
                case 'conexion_completar':
                    $vae_conexion = p_formatear_valor_sql($v, 'nodo');
                    break;
                }
            }
        }
        
        $return = q("
            INSERT INTO sai_valor_extra (
                vae_campo_extra
                , vae_paso_atencion
                , vae_texto
                , vae_numero
                , vae_fecha
                , vae_nodo
                , vae_conexion
                , vae_ciudad
                , vae_creado_por
            ) VALUES (
                $cae_id
                , $paa_id
                , $vae_texto
                , $vae_numero
                , $vae_fecha
                , $vae_nodo
                , $vae_conexion
                , $vae_ciudad
                , {$_SESSION['usu_id']}
            ) RETURNING *
        ");
        $respuesta[] = $return;
    }
}

echo json_encode($respuesta);
