<?php
$result = array();

$nod_id = $args[0];
$ate_id = $args[1];
$atencion_referenciada = $args[2];

$result_nodo = q("
    SELECT * 
    FROM sai_nodo
    WHERE nod_borrado IS NULL
    AND nod_id = $nod_id
");
if ($result_nodo) {
    $n = $result_nodo[0];

    $codigo = $n['nod_codigo'];
    $codigo = p_formatear_valor_sql($codigo);

    $descripcion = $n['nod_descripcion'];
    $descripcion = p_formatear_valor_sql($descripcion);

    $ubi_id = $n['nod_ubicacion'];

    //$atencion = is_numeric($ate_id)?intval($ate_id):'null';
    $atencion = p_formatear_valor_sql($ate_id, 'bigint');

    $atencion_referenciada = p_formatear_valor_sql($atencion_referenciada, 'bigint');



    $costo_instalacion_proveedor = $n['nod_costo_instalacion_proveedor'];
    $costo_instalacion_proveedor = p_formatear_valor_sql($costo_instalacion_proveedor, 'number');

    $costo_instalacion_cliente = $n['nod_costo_instalacion_cliente'];
    $costo_instalacion_cliente = p_formatear_valor_sql($costo_instalacion_cliente, 'number');

    $tipo_ultima_milla = $n['nod_tipo_ultima_milla'];
    $tipo_ultima_milla = p_formatear_valor_sql($tipo_ultima_milla, 'bigint');

    $responsable_ultima_milla = $n['nod_responsable_ultima_milla'];
    $responsable_ultima_milla = p_formatear_valor_sql($responsable_ultima_milla);

    $distancia = $n['nod_distancia'];
    $distancia = p_formatear_valor_sql($distancia, 'number');

    $fecha_termino = $n['nod_fecha_termino'];
    $fecha_termino = p_formatear_valor_sql($fecha_termino, 'timestamp');

    $nodo = $n['nod_nodo'];
    $nodo = p_formatear_valor_sql($nodo);

    $sql = ("
        INSERT INTO sai_nodo (
            nod_codigo
            ,nod_descripcion
            ,nod_ubicacion
            ,nod_creado_por
            ,nod_atencion
            ,nod_costo_instalacion_proveedor
            ,nod_costo_instalacion_cliente
            ,nod_tipo_ultima_milla
            ,nod_responsable_ultima_milla
            ,nod_distancia
            ,nod_fecha_termino
            ,nod_nodo 
            ,nod_duplicado_desde
            ,nod_atencion_referenciada
        ) VALUES (
            $codigo
            ,$descripcion
            ,$ubi_id
            ,{$_SESSION['usu_id']}
            ,$atencion
            ,$costo_instalacion_proveedor
            ,$costo_instalacion_cliente
            ,$tipo_ultima_milla
            ,$responsable_ultima_milla
            ,$distancia
            ,$fecha_termino
            ,$nodo
            ,$nod_id
            ,$atencion_referenciada
            
        ) RETURNING *
    ");
    $result_nod = q($sql);

    $result = $result_nod;
}



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
