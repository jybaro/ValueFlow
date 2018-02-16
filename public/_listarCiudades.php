<?php

$respuestas = array();
$error = array();
$query = $args[0];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    $sql = ("
        SELECT *
        FROM sai_ciudad
        WHERE ciu_borrado IS NULL
        AND ciu_nombre ILIKE '%$query%' 
        ORDER BY ciu_nombre
        ");
    //echo $sql;
    $result = q($sql);

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['ciu_id'], 'name' => $r['ciu_nombre']);
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
