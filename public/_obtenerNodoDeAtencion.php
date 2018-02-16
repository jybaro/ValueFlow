<?php

$cae_id = $args[0];
$ate_id = $args[1];

$result = array();

if (!empty($cae_id) && !empty($ate_id)) {

    $validacion = q("SELECT cae_validacion FROM sai_campo_extra WHERE cae_id=$cae_id")[0]['cae_validacion'];

    $campo_nodo = 'nodo';
    if ($validacion == 'extremo' || $validacion == 'concentrador') {
        $campo_nodo = $validacion;
    }

    $result = q("
        SELECT *
        FROM sai_atencion
        ,sai_nodo
        WHERE ate_borrado IS NULL
        AND nod_borrado IS NULL
        AND ate_$campo_nodo = nod_id
        AND ate_id = $ate_id
    ");
}

echo json_encode($result);
