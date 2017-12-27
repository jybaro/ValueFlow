<?php

//header('Content-Type: application/json');


if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

$rol_usuario_actual = $_SESSION['rol'];
$result = array();

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);






    if (isset($dataset->campo) && !empty($dataset->campo)) {
        $campo = $dataset->campo;
        $result_campo = q("SELECT * FROM sai_campo_extra WHERE cae_id=$campo");
        if ($result_campo) {

            if (isset($dataset->borrar) && !empty($dataset->borrar)) {
                //borrar
                $result = q("UPDATE sai_campo_extra SET cae_transicion_estado_atencion = NULL WHERE cae_id=$campo RETURNING *");
            } else {
                if (isset($dataset->transicion) && !empty($dataset->transicion )) {
                    //guardar
                    $transicion = $dataset->transicion;
                    $result = q("UPDATE sai_campo_extra SET cae_transicion_estado_atencion=$transicion  WHERE cae_id=$campo RETURNING *");
                } else {
                    $result = array(array('ERROR' => 'No se envió la transición de estado para asignar al campo'));
                }
            }
        } else {
            $result = array(array('ERROR' => 'El campo no esta registrado en el sistema'));
        }
    } else {
        $result = array(array('ERROR' => 'No se ha enviado un campo'));
    }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}
$respuestas = array();
if ($result) {
    foreach($result as $r) {
        $respuesta = array();
        foreach($r as $k => $v) {
            $respuesta[str_replace('cae_', '', $k)] = $v;
        }
        $respuestas[] = $respuesta;
    }
}
echo json_encode($respuestas);


