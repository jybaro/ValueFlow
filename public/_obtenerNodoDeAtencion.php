<?php

$ate_id = $args[0];

$result = q("
    SELECT *
    FROM sai_atencion
    ,sai_nodo
    WHERE ate_borrado IS NULL
    AND nod_borrado IS NULL
    AND ate_nodo = nod_id
    AND ate_id = $ate_id
");

echo json_encode($result);
