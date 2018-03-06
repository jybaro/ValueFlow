<?php

$result = array();

//var_dump($_POST);
foreach($_POST as $k => $v){
    $v = pg_escape_string($v);
    $v = (empty($v) ? 'null' : $v);
    //$k = substr($k, 4);
    $$k = $v;
}

$cae_id = $_POST['nod_cae_id'];

//$nod_responsable_ultima_milla = "'$nod_responsable_ultima_milla'";
$nod_responsable_ultima_milla = p_formatear_valor_sql($nod_responsable_ultima_milla, 'text');

//$nod_fecha_termino = "to_timestamp('$nod_fecha_termino', 'YYYY-MM-DD hh24:mi:ss')";
$nod_fecha_termino = p_formatear_valor_sql($nod_fecha_termino, 'timestamp'); 
$nod_nodo = p_formatear_valor_sql($nod_nodo, 'text');

$sql = ("
    UPDATE sai_nodo
    SET
    nod_tipo_ultima_milla = $nod_tipo_ultima_milla
    ,nod_responsable_ultima_milla = $nod_responsable_ultima_milla
    ,nod_distancia = $nod_distancia
    ,nod_fecha_termino = $nod_fecha_termino
    ,nod_nodo = $nod_nodo
    WHERE
    nod_id = $nod_id
    RETURNING *
");


//echo $sql;

$result = q($sql);

echo json_encode($result);
