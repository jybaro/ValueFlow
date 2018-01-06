<?php

$result = array();
if (isset($args) && !empty($args) && isset($args[0]) && !empty($args[0])) {
    $adp_id = $args[0];
    $result = q("
        UPDATE sai_adjunto_plantilla
        SET adp_borrado=now()
        WHERE adp_borrado IS NULL
        AND adp_id=$adp_id
    ");
}
echo json_encode($result);
