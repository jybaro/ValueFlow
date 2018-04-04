<?php

$txt = array('texto', 'codigo', 'validacion', 'valor_por_defecto', 'menor_que', 'mayor_que');
//$no_nulo = array('valor_por_defecto');
$no_nulo = array();

foreach($_POST as $k => $v) {
    $v = pg_escape_string($v);
    $v = empty($v) ? (in_array($k, $no_nulo) ? (in_array($k, $txt) ? "''" : '0') : 'NULL') : (in_array($k, $txt) ? "'$v'" : $v);
    $$k = $v;
}


$sql = null;
$result = array();
$result_campos = q("
    SELECT *
    FROM sai_campo_extra
    WHERE cae_borrado IS NULL
");
$campos = array();
if ($result_campos) {
    foreach ($result_campos as $rc) {
        $campos[$rc['cae_id']] = $rc;
        $campos[$rc['cae_id']]['padre'] = null;
        $campos[$rc['cae_id']]['hijos'] = array();
    }
    foreach ($campos as $cae_id => $c) {
        $cae_padre = $c['cae_padre'];
        $campos[$cae_id]['padre'] = & $campos[$cae_padre];
        $campos[$cae_padre]['hijos'][$cae_id] = & $campos[$cae_id];
    }
}

if ($accion == 'duplicar') {
        function p_tree_duplicar($campo, $cae_padre = null){
            //echo "[p_tree: {$campo[cae_id]} - {$campo[cae_padre]} - $cae_padre]";
            if (empty($cae_padre)) {
                $cae_padre = $campo[cae_padre];
            }
            $cae_padre = (empty($cae_padre) ? 'NULL' : $cae_padre);
            $result = q("
                INSERT INTO sai_campo_extra (
                    cae_texto
                    ,cae_codigo
                    ,cae_tipo_dato
                    ,cae_validacion
                    ,cae_orden
                    ,cae_valor_por_defecto
                    ,cae_menor_que
                    ,cae_mayor_que
                    ,cae_padre
                ) SELECT 
                    cae_texto
                    ,cae_codigo
                    ,cae_tipo_dato
                    ,cae_validacion
                    ,cae_orden
                    ,cae_valor_por_defecto
                    ,cae_menor_que
                    ,cae_mayor_que
                    ,$cae_padre
                FROM sai_campo_extra
                WHERE cae_id = {$campo[cae_id]} 
                RETURNING *
            ");

            $cae_id = $result[0][cae_id];
            foreach ($campo[hijos] as $hijo) {
                p_tree_duplicar($hijo, $cae_id);
            }
            return $result;
        }
        $result = p_tree_duplicar($campos[$id]);
} else if ($accion == 'borrar') {
    function p_tree_borrar($campo){

        $result = q("
            UPDATE sai_campo_extra
            SET cae_borrado = now()
            WHERE cae_borrado IS NULL
            AND cae_id = {$campo[cae_id]} 
            RETURNING *
        ");
        foreach ($campo[hijos] as $hijo) {
            p_tree_borrar($hijo);
        }
        return $result;
    }
    $result = p_tree_borrar($campos[$id]);
} else if ($id != 'NULL') {
    $sql = ("
        UPDATE sai_campo_extra
        SET cae_texto = $texto
        ,cae_codigo = $codigo
        ,cae_tipo_dato = $tipo_dato
        ,cae_validacion = $validacion
        ,cae_orden = $orden
        ,cae_valor_por_defecto = $valor_por_defecto
        ,cae_menor_que = $menor_que
        ,cae_mayor_que = $mayor_que
        ,cae_padre = $padre
        FROM sai_tipo_dato  
        WHERE cae_id = $id
        AND tid_id = $tipo_dato
        RETURNING *
    ");
} else {
    $sql = ("
        WITH cae AS (
        INSERT INTO sai_campo_extra (
            cae_texto
            ,cae_codigo
            ,cae_tipo_dato
            ,cae_validacion
            ,cae_orden
            ,cae_valor_por_defecto
            ,cae_menor_que
            ,cae_mayor_que
            ,cae_padre
        ) VALUES (
            $texto
            ,$codigo
            ,$tipo_dato
            ,$validacion
            ,$orden
            ,$valor_por_defecto
            ,$menor_que
            ,$mayor_que
            ,$padre
        ) RETURNING *
        )
        SELECT *
        FROM cae
        ,sai_tipo_dato
        WHERE cae.cae_tipo_dato = tid_id
    ");
}
//echo $sql;
if (!empty($sql)) {
    $result = q($sql);
} 

if (empty($result)) {
    $result = array('ERROR' => 'No fue posible realizar la acción');
}
echo json_encode($result);
