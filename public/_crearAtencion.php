<?php


//var_dump($_POST);
//return;
$cli_id = $_POST['cliente'];
$cue_id = $_POST['cuenta'];
$pro_id_lista = $_POST['proveedor'];
$ser_id = $_POST['servicio'];
$con_id = $_POST['contacto'];
$usuario_tecnico = $_POST['usuario_tecnico'];
$usuario_comercial = $_POST['usuario_comercial'];
$esa_id = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE '%factibilidad nueva%')";


$respuesta = array();
foreach ($pro_id_lista as $pro_id) {

    $pep_id = "(SELECT pep_id FROM sai_pertinencia_proveedor WHERE pep_proveedor=$pro_id AND pep_servicio=$ser_id)";
    //$ser_id = "(SELECT pep_servicio FROM sai_pertinencia_proveedor WHERE pep_id=$pep_id)";
    $result = q("
        INSERT INTO sai_atencion(
            ate_cliente
            ,ate_cuenta
            ,ate_pertinencia_proveedor
            ,ate_usuario_tecnico
            ,ate_usuario_comercial
            ,ate_estado_atencion
            ,ate_servicio
            ,ate_contacto
        ) VALUES (
            $cli_id
            ,$cue_id
            ,$pep_id
            ,$usuario_tecnico
            ,$usuario_comercial
            ,$esa_id
            ,$ser_id
            ,$con_id
        ) RETURNING *
    ");
    $respuesta[$pro_id] = $result;
}
echo json_encode($respuesta);
