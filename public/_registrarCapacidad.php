<?php

$resultado = array();

if (isset($args[0]) && !empty($args[0]) && isset($args[1]) && !empty($args[1]) && isset($args[2]) && !empty($args[2])) {
    $tipo_capacidad = strtolower($args[1]);

    if (strpos($tipo_capacidad, 'capacidad') !== false && is_int($ate_id) && is_int($capacidad)) {
        $ate_id = intval($args[0]);
        $capacidad = intval($args[2]);

        $result = q("
            UPDATE sai_atencion
            SET ate_{$tipo_capacidad} = $capacidad
            WHERE ate_borrado IS NULL
            AND ate_id = $ate_id
            RETURNING *
        ");
        $resultado = $result;
    } else {
        $resultado = array('ERROR' => 'No se mandaron los tipos correctos de datos, abortando.');
    }
} else {
    $resultado = array('ERROR' => 'No se mandaron los parametros correctos, abortando.');
}

echo json_encode($resultado);
