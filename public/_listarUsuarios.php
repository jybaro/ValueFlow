<?php

$respuestas = array();
$error = array();
$rol_codigo = $args[0];
$query = $args[1];

if (!empty($rol_codigo)) {

    $result = q("
        SELECT * 
        FROM sai_usuario, 
        sai_rol 
        WHERE usu_borrado IS NULL 
        AND rol_borrado IS NULL
        AND usu_rol = rol_id 
        AND rol_codigo = '$rol_codigo'
        AND (
            usu_apellidos ILIKE '%$query%' 
            OR usu_nombres ILIKE '%$query%'
        ) 
        ORDER BY usu_nombres, usu_apellidos
    ");

    if ($result) {
        foreach($result as $r){
            $respuesta = array(
                'id' => $r['usu_id']
                , 'text' => $r['usu_nombres'] . ' ' . $r['usu_apellidos']
            );
            $respuestas[] = $respuesta; 
        }
    }
}
echo json_encode(array('results' => $respuestas));
