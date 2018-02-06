<?php

$imprimir_json = (!isset($tea_id));

$tea_id = (isset($tea_id)) ? $tea_id : $args[0];
$ate_id = (isset($ate_id)) ? $ate_id : (isset($args[1])?$args[1]:0);

$traer_campos_asociados = (isset($traer_campos_asociados)) ? $traer_campos_asociados : (isset($args[2])?$args[2]:0);
$extender_campos_anteriores = (isset($extender_campos_anteriores)) ? $extender_campos_anteriores : (isset($args[3])?$args[3]:0);

$cae_transicion_estado_atencion = $tea_id;

if ($extender_campos_anteriores == 1) {
    //traer todos los campos y valores de la atencion, incluidos pasos anteriores, sin considerar la transicion:
    $cae_transicion_estado_atencion = "
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        ,sai_paso_atencion
        WHERE tea_borrado IS NULL
        AND paa_transicion_estado_atencion = tea_id
        AND paa_atencion = $ate_id
    ";
    //AND paa_borrado IS NULL
} else if ($traer_campos_asociados == 1) {
    //trae los campos de las transiciones de los otros destinatarios
    $cae_transicion_estado_atencion = "
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
    ";
}

$sql = "
    SELECT *
    ,(
        SELECT vae_texto 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra = cae_id 
        AND paa_id=vae_paso_atencion
        AND paa_paso_anterior IS NULL
        AND paa_atencion = $ate_id
    ) AS valor
    , (
        SELECT des_nombre
        FROM sai_destinatario
        ,sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_destinatario = des_id
        AND tea_id = cae_transicion_estado_atencion
    ) AS destinatario
    ,(
        SELECT tid_codigo
        FROM sai_tipo_dato
        WHERE tid_id = cae_tipo_dato
    ) AS tipo_dato
    FROM sai_campo_extra 
    WHERE cae_borrado IS NULL
    AND cae_transicion_estado_atencion IN (
        $cae_transicion_estado_atencion
    )
    ORDER BY cae_orden
";
//echo "[$sql]";
$campos = q($sql);

//var_dump($campos);
if ($imprimir_json) {
    echo json_encode($campos);
}
