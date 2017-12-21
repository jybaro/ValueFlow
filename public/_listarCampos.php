<?php

$respuestas = array();
$error = array();
$id = $args[0];
$id = explode('_', $id);
$ser_id = $id[0];
$pro_id = $id[1];
$des_id = $id[2];
$query = $args[1];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    $result = q("
        SELECT *
        FROM sai_campo_extra
        WHERE cae_borrado IS NULL
        AND cae_transicion_estado_atencion IS NULL
        AND (cae_texto ILIKE '%$query%' OR cae_codigo ILIKE '%$query%')
        ORDER BY cae_texto
    ");

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['cae_id'], 'name' => ($r['cae_texto'] . ' (' . $r['cae_codigo'] . ')'));
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
