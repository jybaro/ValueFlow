<?php


$transicion = array();
if (!empty($args) && isset($args[0]) && isset($args[1]) && isset($args[2]) && isset($args[3])) {
    $desde = $args[0];
    $hacia = $args[1];
    $ser_id = $args[2];
    $pro_id = $args[3];

    $des_id = (isset($args[4])) ? $args[4] : null;
    $filtro_destinatario = (empty($des_id)) ? '' : "AND tea_destinatario=$des_id";

        //,CASE WHEN tea_pertinencia_usuario IS NULL THEN null ELSE (SELECT peu_usuario FROM sai_pertinencia_usuario where peu_id=tea_pertinencia_usuario) END AS usu_id
    $sql = ("
        SELECT * 
        ,(SELECT peu_usuario FROM sai_pertinencia_usuario WHERE peu_id=tea_pertinencia_usuario) AS usu_id
        ,(SELECT pep_proveedor FROM sai_pertinencia_proveedor WHERE pep_id=tea_pertinencia_proveedor) AS pro_id
        ,(SELECT pep_servicio FROM sai_pertinencia_proveedor WHERE pep_id=tea_pertinencia_proveedor) AS ser_id
        FROM sai_transicion_estado_atencion 
        ,sai_plantilla
        WHERE 
        tea_borrado IS NULL
        AND pla_transicion_estado_atencion = tea_id
        AND tea_estado_atencion_padre=$desde
        AND tea_estado_atencion_hijo=$hacia
        $filtro_destinatario
        AND tea_pertinencia_proveedor=CASE WHEN $pro_id=0 THEN 0 ELSE (
            SELECT
            pep_id
            FROM sai_pertinencia_proveedor
            WHERE pep_servicio = $ser_id
            AND pep_proveedor=$pro_id
        ) END
        ");
    //echo $sql;
    $result = q($sql);
    if ($result) {
        foreach ($result as $r) {
            $r['archivos'] = q("
                SELECT * 
                FROM sai_adjunto_plantilla 
                ,sai_archivo
                WHERE adp_archivo = arc_id
                AND adp_plantilla={$r[pla_id]}
                ");
            $r['campos'] = q("
                SELECT *
                FROM sai_campo_extra
                WHERE cae_transicion_estado_atencion={$r[tea_id]}
                ");

            $transicion[] = $r;
        }
    }
}

echo json_encode($transicion);
