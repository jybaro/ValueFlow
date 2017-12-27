<?php

$respuestas = array();
$error = array();
$query = $args[0];

$extension_minima = 3;

if (strlen($query) >= $extension_minima) {

    $result = q("SELECT * FROM esamyn.esa_usuario, esamyn.esa_rol WHERE usu_borrado IS NULL AND usu_rol = rol_id AND (usu_apellidos ILIKE '%$query%' OR usu_cedula LIKE '%$query%') ORDER BY usu_apellidos");

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['usu_id'], 'name' => ($r['usu_apellidos'] . ' ' . $r['usu_nombres'] . ' (' . $r['usu_cedula'] . ') - ' . $r['rol_nombre']));
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
