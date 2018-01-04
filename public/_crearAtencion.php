<?php


//var_dump($_POST);
$cli_id = $_POST['cliente'];
$cue_id = $_POST['cuenta'];
$pro_id = $_POST['proveedor'];
$ser_id = $_POST['servicio'];
$peu_id = $_POST['pertinencia_usuario'];
$usu_id = $_POST['usuario_comercial'];
$esa_id = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE '%factibilidad nueva%')";
$pep_id = "(SELECT pep_id FROM sai_pertinencia_proveedor WHERE pep_proveedor=$pro_id AND pep_servicio=$ser_id)";
//$ser_id = "(SELECT pep_servicio FROM sai_pertinencia_proveedor WHERE pep_id=$pep_id)";
$result = q("
    INSERT INTO sai_atencion(
        ate_cliente
        ,ate_cuenta
        ,ate_pertinencia_proveedor
        ,ate_pertinencia_usuario
        ,ate_usuario_comercial
        ,ate_estado_atencion
        ,ate_servicio
    ) VALUES (
        $cli_id
        ,$cue_id
        ,$pep_id
        ,$peu_id
        ,$usu_id
        ,$esa_id
        ,$ser_id
    ) RETURNING *
");
echo json_encode($result);
