<?php

//var_dump($_POST);

$ate_id = $_POST['ate_id'];

$paa_id = "(
    SELECT paa_id 
    FROM sai_paso_atencion 
    WHERE paa_borrado IS NULL 
    AND paa_paso_anterior IS NULL
    AND paa_atencion=$ate_id
    )";
$result = q($paa_id);
if (!$result) {
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
        ) VALUES (
            $tea_id
            , $ate_id
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
    $v = pg_escape_string($v);

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

        if (!empty($v) && $v != 'null') {
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
                    $vae_texto = "'$v'";
                    break;
                case 'numero':
                    $vae_numero = "$v";
                    break;
                case 'fecha':
                    $vae_fecha = "to_timestamp('$v', 'YYYY-MM-DD hh24:mi:ss')";
                    break;
                case 'ciudad':
                    $vae_ciudad = "$v";
                    break;
                case 'nodo': case 'nodo_completo':
                    $vae_nodo = "$v";
                    $campo_nodo = 'nodo';
                    if ($cae[cae_validacion] == 'concentrador' || $cae[cae_validacion] == 'extremo') {
                        $campo_nodo = $cae[cae_validacion];
                    }

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
                    $vae_conexion = "$v";
                    q("
                        UPDATE sai_atencion
                        SET ate_conexion = $vae_conexion
                        WHERE ate_borrado IS NULL
                        AND ate_id = $ate_id
                    ");
                    break;
                case 'conexion_completar':
                    $vae_conexion = "$v";
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
            ) VALUES (
                $cae_id
                , $paa_id
                , $vae_texto
                , $vae_numero
                , $vae_fecha
                , $vae_nodo
                , $vae_conexion
                , $vae_ciudad
            ) RETURNING *
        ");
        $respuesta[] = $return;
    }
}

echo json_encode($respuesta);
