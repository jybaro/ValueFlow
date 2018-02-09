<?php

$txt = array('texto', 'codigo', 'validacion');

foreach($_POST as $k => $v) {
    $v = empty($v) ? 'NULL' : (in_array($k, $txt) ? "'$v'" : $v);
    $$k = $v;
}

$sql = null;
$result = array();

if ($accion == 'duplicar') {
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
        function p_tree($campo, $cae_padre = null){
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
                    ,cae_padre
                ) SELECT 
                    cae_texto
                    ,cae_codigo
                    ,cae_tipo_dato
                    ,cae_validacion
                    ,cae_orden
                    ,$cae_padre
                FROM sai_campo_extra
                WHERE cae_id = {$campo[cae_id]} 
                RETURNING *
            ");

            $cae_id = $result[0][cae_id];
            foreach ($campo[hijos] as $hijo) {
                p_tree($hijo, $cae_id);
            }
            return $result;
        }
        $result = p_tree($campos[$id]);
    }
} else if ($accion == 'borrar') {
    $count_hijos = q("
        SELECT COUNT(*)
        FROM sai_campo_extra
        WHERE cae_padre = $id
    ")[0]['count'];

    if ($count_hijos == 0) {
        $sql = ("
            UPDATE sai_campo_extra
            SET cae_borrado = now()
            WHERE cae_borrado IS NULL
            AND cae_id = $id
            RETURNING *
        ");
    /*
        $count_valores = q("
            SELECT COUNT(*)
            FROM sai_valor_extra
            WHERE vae_campo_extra = $id
        ")[0]['count'];

        if ($count_valores == 0) {
            $sql = ("
                DELETE FROM sai_campo_extra
                WHERE cae_id = $id
                RETURNING *
            ");
        } else {
            $result = array('ERROR' => 'No se puede borrar porque el campo tiene ' . $count_valores . ' valores registrados');
        }
     */
    } else {
        $result = array('ERROR' => 'No se puede borrar porque el campo tiene ' . $count_hijos . ' hijos');
    }
} else if ($id != 'NULL') {
    $sql = ("
        UPDATE sai_campo_extra
        SET cae_texto = $texto
        ,cae_codigo = $codigo
        ,cae_tipo_dato = $tipo_dato
        ,cae_validacion = $validacion
        ,cae_orden = $orden
        ,cae_padre = $padre
        WHERE cae_id = $id
        RETURNING *
    ");
} else {
    $sql = ("
        INSERT INTO sai_campo_extra (
            cae_texto
            ,cae_codigo
            ,cae_tipo_dato
            ,cae_validacion
            ,cae_orden
            ,cae_padre
        ) VALUES (
            $texto
            ,$codigo
            ,$tipo_dato
            ,$validacion
            ,$orden
            ,$padre
        ) RETURNING *
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
