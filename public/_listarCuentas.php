<?php

$cli_id = $args[0];

$result = q("
    SELECT *
    FROM sai_cuenta
    WHERE cue_borrado IS NULL
    AND cue_cliente = $cli_id
");

echo json_encode($result);
