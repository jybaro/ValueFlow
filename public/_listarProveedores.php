<?php

$ser_id = $args[0];

$result = q("
    SELECT *
    FROM sai_pertinencia_proveedor
    ,sai_proveedor
    WHERE pep_borrado IS NULL
    AND pro_borrado IS NULL
    AND pep_proveedor = pro_id
    AND pep_servicio = $ser_id
");

echo json_encode($result);
