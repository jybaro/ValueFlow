<?php

$result = array();

//var_dump($_POST);
foreach($_POST as $k => $v){
    $v = (empty($v) ? 'null' : $v);
    //$k = substr($k, 4);
    $$k = pg_escape_string($v);
}

$cae_id = $_POST['nod_cae_id'];
$nod_responsable_ultima_milla = "'$nod_responsable_ultima_milla'";
$nod_fecha_termino = "to_timestamp('$nod_fecha_termino', 'YYYY-MM-DD hh24:mi:ss')";

$sql = ("
    UPDATE sai_nodo
    SET
    nod_costo_instalacion_proveedor = $nod_costo_instalacion_proveedor
    ,nod_costo_instalacion_cliente = $nod_costo_instalacion_cliente
    ,nod_tipo_ultima_milla = $nod_tipo_ultima_milla
    ,nod_responsable_ultima_milla = $nod_responsable_ultima_milla
    ,nod_distancia = $nod_distancia
    ,nod_fecha_termino = $nod_fecha_termino
    WHERE
    nod_id = $nod_id
    RETURNING *
");


//echo $sql;

$result = q($sql);

echo json_encode($result);
