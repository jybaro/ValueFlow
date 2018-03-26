<?php

/*
$cli_id = $args[0];

$result = q("
    SELECT *
    FROM sai_cuenta
    WHERE cue_borrado IS NULL
    AND cue_cliente = $cli_id
");

echo json_encode($result);
 */

$query = '';
$filtro_cliente = '';
$filtro_cuenta = '';

if (isset($args[0]) && !empty($args[0])) {
    $filtro_cuenta = "AND cue_id <> {$args[0]}";
}

if (isset($args[1]) && !empty($args[1])) {
    $filtro_cliente = 'AND cli_id = ' . $args[1];
}

if (isset($args[2]) && !empty($args[2])) {
    $query = $args[2];
}


$resultado = array();

$sql = ("
    SELECT
    cue_id AS id
    ,concat(
        'Cuenta '
        , CASE WHEN count_hijos > 0 
            THEN 'padre' 
            ELSE  (
                CASE WHEN cue_padre IS NULL 
                    THEN 'independiente' 
                    ELSE 'hijo' 
                END
            )
        END
        , ' '
        , cli_razon_social
    ) AS text

    FROM (    
        SELECT *
        ,(
            SELECT count(*)
            FROM sai_cuenta AS hijos
            WHERE hijos.cue_borrado IS NULL
            AND hijos.cue_padre = padre.cue_id
        ) AS count_hijos
        FROM sai_cuenta AS padre
        ,sai_cliente
        WHERE cue_borrado IS NULL
        AND cli_borrado IS NULL
        AND cue_cliente = cli_id
        AND (
            cli_razon_social ILIKE '%{$query}%'
            OR cli_nombre_comercial ILIKE '%{$query}%'
            OR cli_ruc ILIKE '%{$query}%'
            OR cue_codigo ILIKE '%{$query}%'
        )
        $filtro_cliente
        $filtro_cuenta
    ) AS t
");

//echo "[[$sql]]";
$result = q($sql);

if ($result) {
    $resultado = $result;
}
echo json_encode(array('results' => $resultado));
