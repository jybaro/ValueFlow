<?php

$nod_id = $args[0];

$result = array();

if (!empty($nod_id)) {

    $result = q("
        SELECT *
        ,(
            SELECT concat(usu_nombres, ' ', usu_apellidos)
            FROM sai_usuario
            WHERE usu_borrado IS NULL
            AND usu_id = nod_creado_por
        ) AS usuario
        ,to_char(nod_creado, 'YYYY-MM-DD') AS fecha_creacion
        ,to_char(nod_fecha_termino, 'YYYY-MM-DD') AS fecha_termino
        ,(
            SELECT tum_nombre
            FROM sai_tipo_ultima_milla
            WHERE tum_borrado IS NULL
            AND nod_tipo_ultima_milla = tum_id 
        ) AS tum_nombre
        FROM sai_nodo

        LEFT OUTER JOIN sai_ubicacion
          ON ubi_borrado IS NULL
          AND nod_ubicacion = ubi_id

        LEFT OUTER JOIN sai_provincia
          ON prv_borrado IS NULL
          AND ubi_provincia = prv_id

        LEFT OUTER JOIN sai_canton
          ON can_borrado IS NULL
          AND ubi_canton = can_id

        LEFT OUTER JOIN sai_parroquia
          ON par_borrado IS NULL
          AND ubi_parroquia = par_id

        LEFT OUTER JOIN sai_ciudad
          ON ciu_borrado IS NULL
          AND ubi_ciudad = ciu_id

        LEFT OUTER JOIN sai_atencion
          ON ate_borrado IS NULL
          AND nod_atencion_referenciada = ate_id

        WHERE nod_borrado IS NULL
        AND nod_id = $nod_id
    ");
}

echo json_encode($result);
