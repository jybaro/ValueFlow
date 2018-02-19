<?php

$ate_id = $args[0];
$ate_codigo = $args[1];

$result = q("
    SELECT *
    FROM sai_atencion
    WHERE ate_borrado IS NULL
    AND ate_codigo = '$ate_codigo' 
    AND ate_id <> $ate_id
");

if (!$result) {
    q("
        UPDATE sai_atencion
        SET ate_codigo = '$ate_codigo'
        WHERE ate_borrado IS NULL
        AND ate_id = $ate_id
    ");
}

echo json_encode($result);
