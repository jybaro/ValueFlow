<?php

$resultado = array();
$json = true;
if (isset($ate_id)) {
    $json = false;
} else if (isset($args[0]) && !empty($args[0])) {
    $ate_id = intval($args[0]);
} else {
    $ate_id = null;
}

if (!empty($ate_id)) {

    $result = q("
        SELECT *
        , concat(
            vae_texto
            ,vae_numero
            ,to_char(vae_fecha, 'yyyy-MM-DD hh:mm')
        ) AS valor
        , (
            SELECT nod_codigo
            FROM 
             sai_nodo
            , sai_ubicacion
            WHERE 
            nod_borrado IS NULL
            AND ubi_borrado IS NULL
            AND nod_id = vae_nodo
            AND ubi_id = nod_ubicacion
        ) AS nodo
        , (
            SELECT ciu_nombre 
            FROM 
             sai_ciudad
            WHERE 
            ciu_borrado IS NULL
            AND ciu_id = vae_ciudad
        ) AS ciudad
        ,to_char(vae_creado, 'yyyy-MM-DD hh:mm') AS fecha
        FROM sai_campo_extra
        ,sai_paso_atencion
        ,sai_valor_extra
        ,sai_usuario
        ,sai_transicion_estado_atencion
        ,sai_estado_atencion
        WHERE cae_borrado IS NULL
        AND vae_borrado IS NULL
        AND paa_borrado IS NULL
        AND usu_borrado IS NULL
        AND tea_borrado IS NULL
        AND esa_borrado IS NULL
        AND vae_campo_extra = cae_id
        AND vae_paso_atencion = paa_id
        AND paa_creado_por = usu_id
        AND paa_transicion_estado_atencion = tea_id
        AND tea_estado_atencion_actual = esa_id
        AND paa_atencion = {$ate_id}
        AND NOT paa_confirmado IS NULL
        ORDER BY vae_creado DESC, cae_orden
    ");

    //var_dump($result);

    $resultado = $result;
}

if ($json) {
    echo json_encode($resultado);
}
