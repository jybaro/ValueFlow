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

    //SELECT concat(ate_secuencial, '. ', COALESCE(ate_codigo, '(sin ID)'))
    //trae los pasos 
    $result = q("
        SELECT *
        , concat(
            vae_texto
            ,vae_numero
            ,to_char(vae_fecha, 'YYYY-MM-DD HH24:MI')
        ) AS valor
        , (
            SELECT 
            CASE 
                WHEN nod_no_diferencia_puntos = 1 AND nod_atencion <> nod_atencion_referenciada
                THEN trim(concat('Servicio activo ', ate_secuencial, ' ', COALESCE(ate_codigo, '')))

                WHEN nod_no_diferencia_puntos = 0 AND nod_atencion <> nod_atencion_referenciada
                THEN concat(trim(concat('Servicio activo ', ate_secuencial, ' ', COALESCE(ate_codigo, ''))), ', punto ', nod_codigo)

                ELSE concat('Punto ', nod_codigo)
            END
            FROM 
             sai_nodo
            , sai_ubicacion
            , sai_atencion
            WHERE 
                nod_borrado IS NULL
                AND ubi_borrado IS NULL
                AND ate_borrado IS NULL
                AND nod_id = vae_nodo
                AND ubi_id = nod_ubicacion
                AND nod_atencion_referenciada = ate_id
        ) AS nodo
        , (
            SELECT ciu_nombre 
            FROM 
             sai_ciudad
            WHERE 
            ciu_borrado IS NULL
            AND ciu_id = vae_ciudad
        ) AS ciudad
        ,(
            SELECT concat(usu_nombres, ' ', usu_apellidos)
            FROM sai_usuario
            WHERE usu_borrado IS NULL
            AND usu_id = paa_creado_por
        ) as usuario
        ,to_char(paa_confirmado, 'YYYY-MM-DD HH24:MI:SS') AS fecha
        FROM sai_paso_atencion

        LEFT OUTER JOIN sai_transicion_estado_atencion
            ON tea_borrado IS NULL
            AND tea_id = paa_transicion_estado_atencion

        LEFT OUTER JOIN sai_estado_atencion
            ON esa_borrado IS NULL
            AND esa_id = tea_estado_atencion_actual

        LEFT OUTER JOIN sai_valor_extra
            ON vae_borrado IS NULL
            AND vae_paso_atencion = paa_id

        LEFT OUTER JOIN sai_campo_extra
            ON cae_borrado IS NULL
            AND cae_id = vae_campo_extra

        WHERE paa_borrado IS NULL
        AND NOT paa_confirmado IS NULL
        AND paa_atencion = {$ate_id}
        ORDER BY paa_creado DESC, cae_orden
    ");

    if ($result) {
        $count_null = 0;
        //var_dump($result);
        //quita los valores vacios repetidos:
        $quiebre = null;
        foreach($result as $k => $r) {
            if ($quiebre != $r['esa_nombre']) {
                $count_null = 0;
            }
            $quiebre = $r['esa_nombre'];

            if (empty($r['cae_texto'])) {
                $count_null++;
                if ($count_null > 1 || (isset($result[$k+1]) && $result[$k+1]['esa_nombre'] == $r['esa_nombre'])) {
                    unset($result[$k]);
                }
            }
        }
    }

    //var_dump($result);

    if ($result) {
        $resultado = array_values($result);
    }
}

if ($json) {
    echo json_encode($resultado);
}
