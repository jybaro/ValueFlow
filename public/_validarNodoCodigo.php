<?php

$ate_id = $args[0];
$cae_id = $args[1];
$nod_codigo = $args[2];

$result = array();

$result_campo = q("
    SELECT *
    FROM sai_campo_extra
    WHERE cae_borrado IS NULL
    AND cae_id = $cae_id
");

if ($result_campo) {

    $cae = $result_campo[0];
    $campo_nodo = 'nodo';
    if ($cae[cae_validacion] == 'concentrador' || $cae[cae_validacion] == 'extremo') {
        $campo_nodo = $cae[cae_validacion];
    }

    $result = q("
        SELECT *
        FROM sai_nodo
        WHERE nod_borrado IS NULL
        AND nod_codigo ILIKE '%$nod_codigo%'
        AND nod_id <> (
            SELECT ate_$campo_nodo
            FROM sai_atencion
            WHERE ate_borrado IS NULL
            AND ate_id = $ate_id
        )
    ");

    if (!$result) {
        q("
            UPDATE sai_nodo
            SET nod_codigo = '$nod_codigo'
            WHERE nod_borrado IS NULL
            AND nod_id = (
                SELECT ate_$campo_nodo
                FROM sai_atencion
                WHERE ate_borrado IS NULL
                AND ate_id = $ate_id
            )
        ");
    }
}

echo json_encode($result);
