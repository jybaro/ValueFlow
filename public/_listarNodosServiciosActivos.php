<?php

$respuestas = array();
$error = array();
$ate_id = $args[0];
$cae_id = $args[1];
$query = $args[2];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    $cae_validacion = q("
        SELECT cae_validacion
        FROM sai_campo_extra
        WHERE cae_borrado IS NULL
        AND cae_id = $cae_id
    ")[0]['cae_validacion'];
    $cae_validacion = empty($cae_validacion) ? 'punto' : $cae_validacion;

    $no_diferencia_puntos = q("
        SELECT pep_no_diferencia_puntos
        FROM sai_atencion
        ,sai_pertinencia_proveedor
        WHERE ate_borrado IS NULL
        AND pep_borrado IS NULL
        AND ate_pertinencia_proveedor = pep_id
        AND ate_id = $ate_id
    ")[0]['pep_no_diferencia_puntos'];

    /*
    $result = q("
        SELECT *
        FROM sai_nodo
        ,sai_ubicacion
        WHERE nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND nod_ubicacion = ubi_id
        AND (ubi_direccion ILIKE '%$query%' OR nod_codigo ILIKE '%$query%'  OR nod_descripcion ILIKE '%$query%')
        ORDER BY nod_codigo
    ");
     */

    $int_query = is_numeric($query) ? intval($query) : -1;

    $sql = ("
        SELECT *
        FROM sai_nodo
        ,sai_ubicacion
        ,sai_atencion
        ,sai_estado_atencion
        ,sai_pertinencia_proveedor
        WHERE nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND ate_borrado IS NULL
        AND esa_borrado IS NULL
        AND pep_borrado IS NULL
        AND nod_ubicacion = ubi_id
        AND ate_estado_atencion = esa_id
        AND (
            esa_play = 1
            OR esa_pause = 1
        )
        AND ate_pertinencia_proveedor = pep_id
        AND pep_proveedor = (
            SELECT pep_proveedor
            FROM sai_pertinencia_proveedor
            ,sai_atencion
            WHERE pep_borrado IS NULL
            AND ate_borrado IS NULL
            AND ate_pertinencia_proveedor = pep_id
            AND ate_id = $ate_id 
        )
        AND nod_atencion = ate_id
        AND (
            ate_secuencial = $int_query
            OR ate_codigo ILIKE '%{$query}%'
            OR ubi_direccion ILIKE '%$query%'
            OR nod_codigo ILIKE '%$query%'
            OR nod_descripcion ILIKE '%$query%'
        )
        ORDER BY ate_codigo
    ");
    /*
        AND (
            esa_nombre ILIKE '%servicio activo%'
            OR esa_nombre ILIKE '%servicio suspendido%'
            OR esa_nombre ILIKE '%incremento%'
            OR esa_nombre ILIKE '%decremento%'
            OR esa_nombre ILIKE '%suspensión%'
        )
     * */

    //echo $sql;
    $result = q($sql);
    /*
        AND (
            ate_nodo = nod_id
            OR ate_extremo = nod_id
        )
     * */
    if ($result) {
        foreach($result as $r){
            $tipo = ($r['ate_concentrador'] == $r['nod_id']) ? 'concentrador' : (($r['ate_extremo'] == $r['nod_id']) ? 'extremo' : 'punto');
            //echo "[[{$r['nod_id']}]]";

            //$respuesta = array('id' => $r['nod_id'], 'name' => ($r['nod_codigo'] . ': ' . $r['nod_descripcion'] . ' (' . $r['ubi_direccion'] . ')'));
            if ($no_diferencia_puntos) {
                //echo "[[NO {$tipo} {$cae_validacion}]]";
                $respuesta = array('id' => $r['nod_id'], 'name' => 'Servicio activo '. trim($r['ate_secuencial'] . ' ' .$r['ate_codigo'] ));
                /*
                $encuentra = false;
                foreach($respuestas as $respuesta_no_diferencia_puntos) {
                    if ($respuesta_no_diferencia_puntos['name'] == $respuesta['name']) {
                        $encuentra = true;
                    }
                }
                if (!$encuentra) {
                    $respuestas[] = $respuesta; 
                }
                 */
                //if ($tipo == 'concentrador' || $tipo == 'punto') {
                //if ($tipo == $cae_validacion) {
                if ($tipo == $cae_validacion || $tipo == 'punto') {
                    //Si el campo es de concentrador, ingresa el concentrador, caso contrario si la validacion del campo es extremo, ingresa el extremo:
                    $respuestas[] = $respuesta; 
                }
            } else {
                //echo "[[SI]]";
                $respuesta = array('id' => $r['nod_id'], 'name' => 'Servicio activo '. trim($r['ate_secuencial'] . ' ' .$r['ate_codigo']) . ', ' . $tipo . ' ' . $r['nod_codigo'] . ', ' .$r['nod_descripcion'] . ' (' . $r['ubi_direccion'] . ')');
                $respuestas[] = $respuesta; 
            }

        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
