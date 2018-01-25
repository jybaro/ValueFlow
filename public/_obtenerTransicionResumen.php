<?php


$transicion = array();
if (!empty($args) && isset($args[0]) && isset($args[1])) {
    $desde = $args[0];
    $hacia = $args[1];

    $sql = ("
        SELECT tea_id 
        ,tea_destinatario
        ,tea_usuario AS usu_id
        ,(SELECT pep_proveedor FROM sai_pertinencia_proveedor WHERE pep_id=tea_pertinencia_proveedor) AS pro_id
        ,(SELECT pep_servicio FROM sai_pertinencia_proveedor WHERE pep_id=tea_pertinencia_proveedor) AS ser_id
        ,(SELECT des_nombre FROM sai_destinatario WHERE des_id=tea_destinatario) AS destinatario
        FROM sai_transicion_estado_atencion 
        WHERE 
        tea_borrado IS NULL
        AND tea_estado_atencion_actual=$desde
        AND tea_estado_atencion_siguiente=$hacia
        ");
    //echo $sql;
    $result = q($sql);
    if ($result) {
        foreach ($result as $r) {
            $transicion[] = $r;
        }
    }
}

echo json_encode($transicion);
