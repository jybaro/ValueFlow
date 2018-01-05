<?php

$imprimir_json = (!isset($tea_id));

$tea_id = (isset($tea_id)) ? $tea_id : $args[0];
$ate_id = (isset($ate_id)) ? $ate_id : (isset($args[1])?$args[1]:0);
/*
$sql = ("
    SELECT *
    ,(
        SELECT vae_texto 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra = cae_id 
        AND paa_id=vae_paso_atencion
        AND paa_atencion=$ate_id
    ) AS valor
    FROM sai_campo_extra 
    WHERE cae_transicion_estado_atencion=$tea_id
    ORDER BY cae_orden
    ");
 */
$sql = ("
    SELECT *
    ,(
        SELECT vae_texto 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra = cae_id 
        AND paa_id=vae_paso_atencion
        AND paa_atencion=$ate_id
    ) AS valor
    FROM sai_campo_extra 
    WHERE cae_transicion_estado_atencion IN (
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_estado_atencion_actual = (SELECT tea_estado_atencion_actual FROM sai_transicion_estado_atencion WHERE tea_id=$tea_id)
        AND tea_estado_atencion_siguiente = (SELECT tea_estado_atencion_siguiente FROM sai_transicion_estado_atencion WHERE tea_id=$tea_id)
        AND tea_pertinencia_proveedor = (SELECT tea_pertinencia_proveedor FROM sai_transicion_estado_atencion WHERE tea_id=$tea_id)
    )
    ORDER BY cae_orden
    ");
//echo "[$sql]";
$campos = q($sql);

if ($imprimir_json) {
    echo json_encode($campos);
}
