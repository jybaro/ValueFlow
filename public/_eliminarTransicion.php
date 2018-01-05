<?php

$result = array();
if (isset($args) && !empty($args) && isset($args[0])) {
    $tea_id = $args[0];
    $result = q("
        UPDATE sai_transicion_estado_atencion
        SET tea_borrado=now()
        WHERE tea_borrado IS NULL
        AND tea_id=$tea_id
        RETURNING *
    ");
}
echo json_encode($result);
