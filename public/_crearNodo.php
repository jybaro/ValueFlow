<?php
$result = array();

//echo "[111]";
//var_dump($_POST);
//echo "[222]";
foreach($_POST as $k => $v){
    $v = pg_escape_string($v);
    $v = (empty($v) ? 'null' : $v); 
    $k = substr($k, 4);
    $$k = $v;
}

$cae_id = $_POST['nod_cae_id'];

$result_ubi = q("
    INSERT INTO sai_ubicacion (
        ubi_provincia
        ,ubi_canton
        ,ubi_parroquia
        ,ubi_ciudad
        ,ubi_latitud
        ,ubi_longitud
        ,ubi_direccion
        ,ubi_sector
    ) VALUES (
        $provincia
        ,$canton
        ,$parroquia
        ,$ciudad
        ,'$latitud'
        ,'$longitud'
        ,'$direccion'
        ,'$sector'
    ) RETURNING *;
");

if ($result_ubi) {
    $ubi_id = $result_ubi[0]['ubi_id'];
    $result_nod = q("
        INSERT INTO sai_nodo (
            nod_codigo
            ,nod_descripcion
            ,nod_ubicacion
            ,nod_creado_por
            ,nod_atencion
        ) VALUES (
            '$codigo'
            ,'$descripcion'
            ,$ubi_id
            ,{$_SESSION['usu_id']}
            ,$atencion
        ) RETURNING *
    ");
    $result = $result_nod;
}

echo json_encode($result);
