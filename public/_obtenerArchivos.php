<?php

$result_archivo = array();
if (isset($args) && !empty($args) && isset($args[0]) &&  !empty($args[0])) {
    $tea_id = $args[0]; 
    if (isset($args[1]) &&  !empty($args[1])) {
        $pla_id = $args[1];
        $sql = ("
            SELECT *
            FROM sai_adjunto_plantilla
            ,sai_archivo
            ,sai_plantilla
            WHERE adp_borrado IS NULL
            AND arc_borrado IS NULL
            AND pla_borrado IS NULL
            AND pla_id = adp_plantilla
            AND adp_archivo = arc_id
            AND adp_plantilla=$pla_id
        ");
    } else {
        $sql = ("
            SELECT *
            FROM 
            sai_adjunto_plantilla
            ,sai_archivo
            ,sai_plantilla
            WHERE 
            adp_borrado IS NULL
            AND arc_borrado IS NULL
            AND pla_borrado IS NULL
            AND pla_id = adp_plantilla
            AND adp_archivo = arc_id
            AND adp_plantilla IN (
                SELECT MAX(pla_id)
                FROM sai_plantilla
                WHERE pla_borrado IS NULL
                AND pla_transicion_estado_atencion=$tea_id
            )
        ");
    }
    $result_archivo = q($sql);

}

echo json_encode($result_archivo);
