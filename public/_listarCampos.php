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
        ,(
            SELECT cae_texto
            FROM sai_campo_extra AS padre
            WHERE padre.cae_id = cae.cae_padre
        ) AS padre_texto
        FROM sai_campo_extra AS cae
        WHERE cae.cae_borrado IS NULL
        AND cae.cae_transicion_estado_atencion IS NULL
        AND (cae.cae_texto ILIKE '%$query%' OR cae.cae_codigo ILIKE '%$query%')
        ORDER BY cae.cae_texto
    ");

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['cae_id'], 'name' => ($r['cae_codigo'] . ' (' . $r['cae_texto'] . ')'));
            if (!empty($r['padre_texto'])) {
                $respuesta['name'] = $r['padre_texto'] . ' >> '. $respuesta['name'];
            }
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
