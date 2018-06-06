<?php

$result = array();
if (isset($args) && !empty($args) && isset($args[0])) {
    $tea_id = $args[0];
    $result = q("
        UPDATE sai_transicion_estado_atencion
        SET tea_borrado = now()
        WHERE tea_borrado IS NULL
        AND tea_id = $tea_id
        RETURNING *
    ");
    if ($result) {
        q("
            UPDATE sai_plantilla
            SET pla_borrado = now()
            WHERE pla_borrado IS NULL
            AND pla_transicion_estado_atencion = $tea_id
        ");

        q("
            UPDATE sai_campo_extra
            SET cae_transicion_estado_atencion = null
            WHERE cae_borrado IS NULL
            AND cae_transicion_estado_atencion = $tea_id
        ");
    }
}
echo json_encode($result);
