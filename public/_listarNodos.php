<?php

$respuestas = array();
$error = array();
$query = $args[0];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    $result = q("
        SELECT *
        FROM sai_nodo
        ,sai_ubicacion
        WHERE nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND nod_ubicacion = ubi_id
        AND (ubi_direccion ILIKE '%$query%' OR nod_codigo ILIKE '%$query%'  OR nod_descripcion ILIKE '%$query%')
        ORDER BY nod_codigo
    ");

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['nod_id'], 'name' => ($r['nod_codigo'] . ': ' . $r['nod_descripcion'] . ' (' . $r['ubi_direccion'] . ')'));
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
